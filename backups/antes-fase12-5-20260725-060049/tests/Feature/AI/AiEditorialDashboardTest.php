<?php

namespace Tests\Feature\AI;

use App\Models\AiExecution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiEditorialDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_empty_dashboard(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get('/admin/ia')
            ->assertOk()
            ->assertSee('Central Editorial IA')
            ->assertSee('Nenhuma execução no período');
    }

    public function test_journalist_only_sees_own_execution(): void
    {
        $journalist = User::factory()->create();
        $other = User::factory()->create();
        Role::findOrCreate('Jornalista');
        $journalist->assignRole('Jornalista');
        AiExecution::query()->create(['user_id' => $journalist->id, 'feature' => 'own-feature', 'status' => 'completed']);
        AiExecution::query()->create(['user_id' => $other->id, 'feature' => 'hidden-feature', 'status' => 'completed']);

        $this->actingAs($journalist)->get('/admin/ia')
            ->assertOk()
            ->assertSee('own-feature')
            ->assertDontSee('hidden-feature');
    }
}
