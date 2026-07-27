<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionBenefitRedemption extends Model
{
    protected $fillable = ['user_id', 'redeemed_at', 'status'];

    protected function casts(): array
    {
        return ['redeemed_at' => 'datetime'];
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(SubscriptionBenefit::class, 'subscription_benefit_id');
    }
}
