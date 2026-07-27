<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionBenefit extends Model
{
    protected $fillable = ['name', 'type', 'description', 'code', 'destination_url', 'starts_at', 'ends_at', 'usage_limit', 'redeemed_count', 'is_active'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean', 'usage_limit' => 'integer', 'redeemed_count' => 'integer'];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscription_benefit_plan');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(SubscriptionBenefitRedemption::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('redeemed_count', '<', 'usage_limit'));
    }
}
