<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_change_creates_history(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'roles' => [], 'subscription_status' => 'active', 'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly', 'subscription_ends_at' => now()->addMonth()->format('Y-m-d H:i:s'),
        ])->assertRedirect();
        $subscription = $user->fresh()->subscription;
        $this->assertSame('active', $subscription->status);
        $this->assertDatabaseHas('subscription_histories', [
            'subscription_id' => $subscription->id, 'event' => 'created', 'to_status' => 'active', 'to_plan_id' => $plan->id,
        ]);
    }

    public function test_subscriber_can_view_history_and_cancel_own_subscription(): void
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        app(SubscriptionService::class)->update($user, [
            'subscription_plan_id' => $plan->id, 'status' => 'active', 'billing_cycle' => 'monthly',
            'price' => $plan->monthly_price, 'auto_renew' => true, 'starts_at' => now(), 'ends_at' => now()->addMonth(),
        ], $user);
        $this->actingAs($user)->get(route('subscriber.show'))->assertOk()->assertSee('Premium');
        $this->actingAs($user)->post(route('subscriber.cancel'), ['reason' => 'Não utilizarei no próximo mês.'])->assertRedirect();
        $subscription = $user->fresh()->subscription;
        $this->assertSame('cancelled', $subscription->status);
        $this->assertFalse($subscription->auto_renew);
        $this->assertDatabaseHas('subscription_histories', ['subscription_id' => $subscription->id, 'event' => 'cancelled']);
    }
}
