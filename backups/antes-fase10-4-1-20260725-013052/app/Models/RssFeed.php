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
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
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

    public function importedArticles(): HasMany
    {
        return $this->hasMany(RssImportedArticle::class);
    }
}
