<?php

namespace Tests\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_pages_enforce_role_matrix(): void
    {
        foreach (['Admin', 'Jornalista'] as $role) {
            Role::findOrCreate($role);
        }
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $journalist = User::factory()->create();
        $journalist->assignRole('Jornalista');
        Http::fake();

        $this->get('/admin/saude-do-sistema')->assertRedirect('/login');
        $this->actingAs($journalist)->get('/admin/saude-do-sistema')->assertForbidden();
        $this->actingAs($journalist)->get('/admin/sistema/filas')->assertForbidden();
        $this->actingAs($admin)->get('/admin/saude-do-sistema')->assertOk();
        $this->actingAs($admin)->get('/admin/sistema/filas')->assertOk();
    }
}
