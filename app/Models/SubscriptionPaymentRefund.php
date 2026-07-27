<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPaymentRefund extends Model
{
    protected $fillable = ['requested_by', 'provider_refund_id', 'amount', 'status', 'reason', 'provider_data'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'provider_data' => 'array'];
    }
}
