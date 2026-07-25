<?php

namespace Tests\Feature\AI;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiCentralConsolidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reach_every_central_module(): void
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        foreach (['/admin/ia','/admin/noticias/ia','/admin/ia/prompts','/admin/ia/memoria','/admin/ia/provedores','/admin/ia/custos','/admin/ia/logs'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_route_names_and_method_uri_pairs_are_unique(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes());
        $names = $routes->pluck('action.as')->filter();
        $pairs = $routes->map(fn ($route) => implode('|', $route->methods()).' '.$route->uri());
        $this->assertSame($names->count(), $names->unique()->count(), 'Há nomes de rota duplicados.');
        $this->assertSame($pairs->count(), $pairs->unique()->count(), 'Há métodos/URIs duplicados.');
    }

    public function test_journalist_sees_editorial_actions_but_not_global_administration(): void
    {
        Role::findOrCreate('Jornalista');
        $user = User::factory()->create();
        $user->assignRole('Jornalista');
        $this->actingAs($user)->get('/admin/ia')->assertOk()->assertSee('Gerar notícia')->assertDontSee('Provedores');
        $this->actingAs($user)->get('/admin/ia/custos')->assertForbidden();
    }
}
