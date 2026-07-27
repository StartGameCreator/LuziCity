<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'monthly_price', 'yearly_price', 'benefits',
        'display_order', 'is_ad_free', 'is_active', 'is_featured',
        'can_access_premium', 'monthly_article_limit', 'preview_characters',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2', 'yearly_price' => 'decimal:2', 'benefits' => 'array',
            'is_ad_free' => 'boolean', 'is_active' => 'boolean', 'is_featured' => 'boolean',
            'can_access_premium' => 'boolean', 'monthly_article_limit' => 'integer', 'preview_characters' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function benefitsCatalog(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionBenefit::class, 'subscription_benefit_plan');
    }
}
