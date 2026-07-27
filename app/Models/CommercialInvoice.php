<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialInvoice extends Model
{
    protected $fillable = [
        'advertiser_profile_id', 'commercial_proposal_id', 'created_by', 'number',
        'description', 'status', 'amount', 'paid_amount', 'issued_at', 'due_at',
        'paid_at', 'is_recurring', 'recurrence', 'next_renewal_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', 'paid_amount' => 'decimal:2',
            'issued_at' => 'date', 'due_at' => 'date', 'paid_at' => 'datetime',
            'is_recurring' => 'boolean', 'next_renewal_at' => 'date',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserProfile::class, 'advertiser_profile_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CommercialProposal::class, 'commercial_proposal_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CommercialPayment::class);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_at', '<', today())->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
