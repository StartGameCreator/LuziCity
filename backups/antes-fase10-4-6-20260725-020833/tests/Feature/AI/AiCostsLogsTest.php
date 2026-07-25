<?php

namespace Tests\Feature\AI;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiCostsLogsTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_can_view_costs_and_logs(): void {
        $admin=User::factory()->create(); Role::findOrCreate('Admin'); $admin->assignRole('Admin');
        $this->actingAs($admin)->get('/admin/ia/custos')->assertOk();
        $this->actingAs($admin)->get('/admin/ia/logs')->assertOk();
    }
    public function test_journalist_cannot_view_global_costs(): void {
        $user=User::factory()->create(); Role::findOrCreate('Jornalista'); $user->assignRole('Jornalista');
        $this->actingAs($user)->get('/admin/ia/custos')->assertForbidden();
    }
}
