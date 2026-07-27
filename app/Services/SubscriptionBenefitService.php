<?php

namespace App\Services;

use App\Models\SubscriptionBenefit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionBenefitService
{
    public function eligibleFor(User $user)
    {
        $subscription = $user->subscription;
        if (! $subscription?->isActive() || ! $subscription->subscription_plan_id) {
            return SubscriptionBenefit::query()->whereRaw('1 = 0')->get();
        }

        return SubscriptionBenefit::available()->whereHas('plans', fn ($q) => $q->whereKey($subscription->subscription_plan_id))
            ->with(['redemptions' => fn ($q) => $q->where('user_id', $user->id)])->orderBy('type')->orderBy('name')->get();
    }

    public function redeem(SubscriptionBenefit $benefit, User $user): void
    {
        DB::transaction(function () use ($benefit, $user): void {
            $locked = SubscriptionBenefit::available()->lockForUpdate()->find($benefit->id);
            if (! $locked || ! $user->subscription?->isActive() || ! $locked->plans()->whereKey($user->subscription->subscription_plan_id)->exists()) {
                throw ValidationException::withMessages(['benefit' => 'Benefício indisponível para seu plano.']);
            }
            $redemption = $locked->redemptions()->firstOrCreate(['user_id' => $user->id], ['redeemed_at' => now(), 'status' => 'redeemed']);
            if ($redemption->wasRecentlyCreated) {
                $locked->increment('redeemed_count');
            }
        });
    }
}
