<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssFeed extends Model
{
    protected $fillable = [
        'name',
        'url',
        'category',
        'sort_order',
        'is_active',
        'frequency_minutes', 'next_collection_at', 'last_collected_at', 'last_success_at',
        'last_failure_at', 'last_failure_message', 'consecutive_failures',
        'deduplication_strategy', 'items_collected', 'duplicates_found',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'frequency_minutes' => 'integer', 'next_collection_at' => 'datetime',
            'last_collected_at' => 'datetime', 'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime', 'consecutive_failures' => 'integer',
            'items_collected' => 'integer', 'duplicates_found' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereNotNull('url')
            ->where('url', '<>', '')
            ->where('url', '<>', '#');
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->usable()->where(fn (Builder $q) => $q->whereNull('next_collection_at')->orWhere('next_collection_at', '<=', now()));
    }

    public function collectionRuns(): HasMany
    {
        return $this->hasMany(RssCollectionRun::class);
    }

    public function importedArticles(): HasMany
    {
        return $this->hasMany(RssImportedArticle::class);
    }
}
