<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdCampaign extends Model
{
    use BelongsToCurrentSite;

    protected $fillable = [
        'site_id',
        'advertiser_id',
        'advertiser_profile_id',
        'name',
        'campaign_type',
        'placement',
        'status',
        'billing_model',
        'budget',
        'daily_budget',
        'price_per_impression',
        'price_per_click',
        'impression_limit',
        'click_limit',
        'impressions_count',
        'clicks_count',
        'target_url',
        'image_path',
        'image_alt',
        'target_cities',
        'target_categories',
        'target_devices',
        'weekdays',
        'daily_start_time',
        'daily_end_time',
        'priority',
        'is_active',
        'starts_at',
        'ends_at',
        'approved_at',
        'approved_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'daily_budget' => 'decimal:2',
            'price_per_impression' => 'decimal:6',
            'price_per_click' => 'decimal:6',
            'impression_limit' => 'integer',
            'click_limit' => 'integer',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
            'target_cities' => 'array',
            'target_categories' => 'array',
            'target_devices' => 'array',
            'weekdays' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function advertiserProfile(): BelongsTo
    {
        return $this->belongsTo(AdvertiserProfile::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApprovedActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNotNull('approved_at')
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeDeliverable(Builder $query): Builder
    {
        return $query->approvedActive()
            ->where(fn (Builder $q) => $q->whereNull('impression_limit')
                ->orWhereColumn('impressions_count', '<', 'impression_limit'))
            ->where(fn (Builder $q) => $q->whereNull('click_limit')
                ->orWhereColumn('clicks_count', '<', 'click_limit'));
    }

    public function getCtrAttribute(): float
    {
        return $this->impressions_count > 0
            ? round(($this->clicks_count / $this->impressions_count) * 100, 2)
            : 0.0;
    }
}
