<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function update(User $user, array $data, ?User $actor = null): Subscription
    {
        return DB::transaction(function () use ($user, $data, $actor): Subscription {
            $current = $user->subscription;
            $fromStatus = $current?->status;
            $fromPlan = $current?->subscription_plan_id;
            $subscription = $user->subscription()->updateOrCreate(['user_id' => $user->id], $data);
            $event = $current ? (($fromPlan !== $subscription->subscription_plan_id) ? 'plan_changed' : 'status_changed') : 'created';
            $subscription->histories()->create([
                'user_id' => $user->id, 'performed_by' => $actor?->id, 'event' => $event,
                'from_status' => $fromStatus, 'to_status' => $subscription->status,
                'from_plan_id' => $fromPlan, 'to_plan_id' => $subscription->subscription_plan_id,
            ]);
            $user->setRelation('subscription', $subscription->load('plan'));

            return $subscription;
        });
    }

    public function cancel(Subscription $subscription, User $actor, string $reason): void
    {
        DB::transaction(function () use ($subscription, $actor, $reason): void {
            $from = $subscription->status;
            $subscription->update([
                'status' => 'cancelled', 'auto_renew' => false, 'cancelled_at' => now(),
                'cancelled_by' => $actor->id, 'cancellation_reason' => $reason,
            ]);
            $subscription->histories()->create([
                'user_id' => $subscription->user_id, 'performed_by' => $actor->id, 'event' => 'cancelled',
                'from_status' => $from, 'to_status' => 'cancelled',
                'from_plan_id' => $subscription->subscription_plan_id, 'to_plan_id' => $subscription->subscription_plan_id,
                'notes' => $reason,
            ]);
        });
    }
}
