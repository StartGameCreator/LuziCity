<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'billing_cycle',
        'price',
        'auto_renew',
        'starts_at',
        'ends_at',
        'assigned_by',
        'notes',
        'cancelled_at', 'cancelled_by', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'decimal:2',
            'auto_renew' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(fn (Builder $query) => $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class)->latest();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
