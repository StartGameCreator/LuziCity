<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPayment extends Model
{
    protected $fillable = ['subscription_id', 'user_id', 'provider', 'external_reference', 'provider_payment_id', 'preference_id', 'status', 'amount', 'refunded_amount', 'currency', 'paid_at', 'provider_data'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'refunded_amount' => 'decimal:2', 'paid_at' => 'datetime', 'provider_data' => 'array'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(SubscriptionPaymentRefund::class);
    }
}
