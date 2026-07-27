<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use App\Models\AiExecution;
use App\Models\NewsArticle;
use App\Models\Site;
use App\Models\SystemAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GlobalAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_sees_global_network_cost_health_and_audit(): void
    {
        Role::findOrCreate('Admin');
        Role::findOrCreate('Super Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($admin)->get(route('admin.global.index'))->assertForbidden();
        $this->actingAs($superAdmin)->post(route('admin.sites.store'), [
            'name' => 'Portal Global', 'slug' => 'portal-global', 'city' => 'Brasília', 'state' => 'DF',
            'domains' => 'global.test', 'theme_primary' => '#112233', 'theme_secondary' => '#334455',
            'is_active' => '1',
        ])->assertRedirect();
        $site = Site::where('slug', 'portal-global')->firstOrFail();
        app()->instance('currentSite', $site);
        NewsArticle::create([
            'author_id' => $superAdmin->id, 'title' => 'Notícia da rede', 'slug' => 'noticia-da-rede',
            'body' => 'Conteúdo', 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        AdCampaign::create(['name' => 'Campanha global', 'placement' => 'home_top']);
        AiExecution::create([
            'user_id' => $superAdmin->id, 'feature' => 'global-test', 'status' => 'success',
            'input_hash' => hash('sha256', 'global'), 'estimated_cost_micros' => 250000,
        ]);

        $this->assertDatabaseHas('system_audit_logs', ['user_id' => $superAdmin->id, 'event' => 'admin.admin.sites.store']);
        $this->actingAs($superAdmin)->get(route('admin.global.index'))->assertOk()
            ->assertSee('Administração global')->assertSee('Portal Global')
            ->assertSee('Notícia da rede')->assertSee('Campanha global')
            ->assertSee('Custos globais')->assertSee('Saúde')
            ->assertSee('Auditoria administrativa');
    }

    public function test_audit_log_does_not_store_form_payload_or_secrets(): void
    {
        Role::findOrCreate('Super Admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');
        $this->actingAs($superAdmin)->post(route('admin.sites.store'), [
            'name' => 'Auditado', 'slug' => 'auditado', 'domains' => 'auditado.test',
            'theme_primary' => '#0067c0', 'theme_secondary' => '#004e8c',
            'settings' => 'secret=nao-deve-ser-copiado', 'is_active' => '1',
        ])->assertRedirect();

        $audit = SystemAuditLog::latest('created_at')->firstOrFail();
        $this->assertArrayNotHasKey('settings', $audit->new_values);
        $this->assertStringNotContainsString('nao-deve-ser-copiado', json_encode($audit->new_values));
        $this->assertNotNull($audit->request_id);
    }
}
