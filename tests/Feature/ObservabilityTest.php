<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ObservabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ObservabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_requests_receive_correlation_id_and_generate_metrics(): void
    {
        config(['observability.sample_rate' => 100]);

        $response = $this->get('/health/ready');
        $response->assertOk()->assertHeader('X-Request-ID');

        $this->assertDatabaseHas('request_metrics', [
            'route' => 'health.ready',
            'status' => 200,
        ]);
    }

    public function test_readiness_endpoint_reports_dependencies_without_sensitive_details(): void
    {
        $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonStructure(['checks' => ['database', 'cache', 'queue', 'storage']])
            ->assertJsonMissing(['password']);
    }

    public function test_operational_alerts_and_admin_dashboard_expose_metrics(): void
    {
        DB::table('request_metrics')->insert([
            'request_id' => fake()->uuid(),
            'method' => 'GET',
            'route' => 'example',
            'path' => 'example',
            'status' => 500,
            'duration_ms' => 2000,
            'memory_bytes' => 1024,
            'is_api' => false,
            'occurred_at' => now(),
        ]);

        $alerts = app(ObservabilityService::class)->alerts();
        $this->assertNotEmpty($alerts);

        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        Http::fake();
        $this->actingAs($admin)->get('/admin/saude-do-sistema')
            ->assertOk()
            ->assertSee('Observabilidade')
            ->assertSee('Alerta:');
    }
}
