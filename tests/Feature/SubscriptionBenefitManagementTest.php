<?php

namespace Tests\Feature;

use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionBenefitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_subscriber_sees_and_redeems_plan_benefit_once(): void
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        app(SubscriptionService::class)->update($user, [
            'subscription_plan_id' => $plan->id, 'status' => 'active', 'billing_cycle' => 'monthly',
            'price' => $plan->monthly_price, 'starts_at' => now(), 'ends_at' => now()->addMonth(),
        ], $user);
        $benefit = SubscriptionBenefit::create([
            'name' => 'Cupom parceiro', 'type' => 'coupon', 'code' => 'LUZI20', 'description' => '20% de desconto',
            'is_active' => true, 'usage_limit' => 10,
        ]);
        $benefit->plans()->attach($plan);
        $this->actingAs($user)->get(route('subscriber.benefits.index'))->assertOk()->assertSee('Cupom parceiro');
        $this->actingAs($user)->post(route('subscriber.benefits.redeem', $benefit))->assertRedirect();
        $this->actingAs($user)->post(route('subscriber.benefits.redeem', $benefit))->assertRedirect();
        $this->assertDatabaseCount('subscription_benefit_redemptions', 1);
        $this->assertSame(1, $benefit->fresh()->redeemed_count);
        $this->actingAs($user)->get(route('subscriber.benefits.index'))->assertSee('LUZI20');
    }

    public function test_benefit_from_another_plan_is_not_visible(): void
    {
        $user = User::factory()->create();
        $free = SubscriptionPlan::where('slug', 'gratuito')->firstOrFail();
        $vip = SubscriptionPlan::where('slug', 'vip')->firstOrFail();
        app(SubscriptionService::class)->update($user, [
            'subscription_plan_id' => $free->id, 'status' => 'active', 'billing_cycle' => 'monthly', 'price' => 0, 'starts_at' => now(),
        ], $user);
        $benefit = SubscriptionBenefit::create(['name' => 'Evento VIP', 'type' => 'event', 'is_active' => true]);
        $benefit->plans()->attach($vip);
        $this->actingAs($user)->get(route('subscriber.benefits.index'))->assertOk()->assertDontSee('Evento VIP');
    }
}
