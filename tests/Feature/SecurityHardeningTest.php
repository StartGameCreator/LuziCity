<?php

namespace Tests\Feature;

use App\Models\TvBroadcast;
use App\Models\TvChannel;
use App\Models\User;
use App\Models\Setting;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_include_security_headers(): void
    {
        $this->get('/')->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertHeader('x-frame-options', 'SAMEORIGIN')
            ->assertHeader('referrer-policy', 'strict-origin-when-cross-origin')
            ->assertHeader('permissions-policy', 'camera=(), microphone=(self), geolocation=()')
            ->assertHeader('content-security-policy');
    }

    public function test_tv_embed_is_sanitized_again_at_render_time(): void
    {
        $channel = TvChannel::create(['name' => 'TV segura', 'slug' => 'tv-segura', 'is_active' => true]);
        TvBroadcast::create([
            'tv_channel_id' => $channel->id,
            'title' => 'Transmissão',
            'provider' => 'embed',
            'embed_code' => '<script>alert(1)</script><iframe src="https://www.youtube.com/embed/abc123XYZ" onload="alert(2)"></iframe>',
            'status' => 'live',
            'force_live' => true,
        ]);

        $this->get('/tv')->assertOk()
            ->assertSee('youtube.com/embed/abc123XYZ', false)
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('alert(2)', false)
            ->assertDontSee('onload=', false);
    }

    public function test_global_upload_guard_rejects_executable_extensions(): void
    {
        $file = UploadedFile::fake()->create('foto.php', 2, 'image/png');

        $this->post('/radio/pedidos', [
            'category' => 'geral',
            'message' => 'Teste de upload',
            'attachment' => $file,
        ])->assertSessionHasErrors('attachment');
    }

    public function test_editor_cannot_access_admin_only_template_management(): void
    {
        Role::findOrCreate('Jornalista');
        $journalist = User::factory()->create();
        $journalist->assignRole('Jornalista');

        $this->actingAs($journalist)->get('/admin/impresso/templates')->assertForbidden();
        $this->actingAs($journalist)->post('/admin/impresso/templates', [])->assertForbidden();
    }

    public function test_failed_admin_mutation_is_audited_without_request_secrets(): void
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)->post('/admin/impresso/templates', [
            'name' => '',
            'client_secret' => 'nao-deve-ser-registrado',
        ])->assertSessionHasErrors('name');

        $audit = \App\Models\SystemAuditLog::latest('id')->firstOrFail();
        $this->assertSame(302, $audit->new_values['response_status']);
        $this->assertStringNotContainsString('nao-deve-ser-registrado', json_encode($audit->toArray()));
    }

    public function test_production_example_disables_debug_and_encrypts_sessions(): void
    {
        $example = file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString('APP_DEBUG=false', $example);
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $example);
        $this->assertStringNotContainsString('OPENAI_API_KEY=sk-', $example);
    }

    public function test_csrf_exception_is_limited_to_the_signed_payment_webhook(): void
    {
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertStringContainsString("'pagamentos/webhook/mercado-pago'", $bootstrap);
        $this->assertStringNotContainsString("validateCsrfTokens(except: ['*']", $bootstrap);

        $this->post('/pagamentos/webhook/mercado-pago')->assertUnauthorized();
    }

    public function test_application_secrets_are_encrypted_at_rest(): void
    {
        $setting = Setting::create(['group' => 'ai', 'key' => 'openai_api_key', 'value' => 'sk-segredo-teste']);
        $this->assertSame('sk-segredo-teste', $setting->fresh()->value);
        $this->assertStringStartsWith('enc:', DB::table('settings')->where('id', $setting->id)->value('value'));
        $this->assertStringNotContainsString('sk-segredo-teste', DB::table('settings')->where('id', $setting->id)->value('value'));

        $user = User::factory()->create();
        $account = SocialAccount::create([
            'user_id' => $user->id, 'provider' => 'google', 'provider_user_id' => 'provider-123',
            'access_token' => 'access-secret', 'refresh_token' => 'refresh-secret',
        ]);
        $raw = DB::table('social_accounts')->where('id', $account->id)->first();
        $this->assertNotSame('access-secret', $raw->access_token);
        $this->assertNotSame('refresh-secret', $raw->refresh_token);
        $this->assertSame('access-secret', $account->fresh()->access_token);
    }
}
