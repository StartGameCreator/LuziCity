<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialProposal extends Model
{
    protected $fillable = [
        'advertiser_profile_id', 'created_by', 'approved_by', 'number', 'title',
        'status', 'valid_until', 'discount', 'total', 'notes', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date', 'discount' => 'decimal:2',
            'total' => 'decimal:2', 'approved_at' => 'datetime',
        ];
    }

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvertiserProfile::class, 'advertiser_profile_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommercialProposalItem::class);
    }
}
