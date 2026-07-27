<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_plans_are_publicly_available(): void
    {
        $this->get(route('subscription-plans.index'))->assertOk()
            ->assertSee('Gratuito')->assertSee('Premium')->assertSee('VIP')->assertSee('Empresarial');
        $this->assertCount(4, SubscriptionPlan::all());
    }

    public function test_admin_assigns_plan_and_ad_free_access_follows_plan(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $user = User::factory()->create();
        $premium = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'roles' => [], 'subscription_status' => 'active', 'subscription_plan_id' => $premium->id,
            'billing_cycle' => 'monthly', 'subscription_ends_at' => now()->addMonth()->format('Y-m-d H:i:s'),
        ])->assertRedirect();
        $this->assertTrue($user->fresh()->hasAdFreeAccess());

        $free = SubscriptionPlan::where('slug', 'gratuito')->firstOrFail();
        Subscription::where('user_id', $user->id)->update(['subscription_plan_id' => $free->id]);
        $this->assertFalse($user->fresh()->hasAdFreeAccess());
    }
}
