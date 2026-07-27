<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaywallCategoryRule extends Model
{
    protected $fillable = ['category_id', 'minimum_plan_id', 'is_enabled', 'preview_characters'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'preview_characters' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function minimumPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'minimum_plan_id');
    }
}
