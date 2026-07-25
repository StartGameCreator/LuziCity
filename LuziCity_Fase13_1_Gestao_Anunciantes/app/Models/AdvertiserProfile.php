<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertiserProfile extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'legal_name', 'trade_name', 'document_number',
        'state_registration', 'municipal_registration', 'segment', 'company_size',
        'commercial_status', 'responsible_user_id', 'contact_phone', 'whatsapp',
        'email', 'website', 'social_links', 'notes', 'contracted_revenue',
        'expected_revenue', 'contract_starts_at', 'contract_ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'contracted_revenue' => 'decimal:2',
            'expected_revenue' => 'decimal:2',
            'contract_starts_at' => 'date',
            'contract_ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), fn (Builder $q) => $q->where(function (Builder $inner) use ($term): void {
            $inner->where('company_name', 'like', "%{$term}%")
                ->orWhere('legal_name', 'like', "%{$term}%")
                ->orWhere('trade_name', 'like', "%{$term}%")
                ->orWhere('document_number', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        }));
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function responsible(): BelongsTo { return $this->belongsTo(User::class, 'responsible_user_id'); }
    public function contacts(): HasMany { return $this->hasMany(AdvertiserContact::class); }
    public function addresses(): HasMany { return $this->hasMany(AdvertiserAddress::class); }
    public function documents(): HasMany { return $this->hasMany(AdvertiserDocument::class); }
    public function histories(): HasMany { return $this->hasMany(AdvertiserHistory::class)->latest('occurred_at'); }
}
