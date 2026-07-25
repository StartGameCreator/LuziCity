<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssImportedArticle extends Model
{
    protected $fillable = [
        'rss_feed_id', 'source_name', 'category', 'title', 'original_url', 'guid',
        'excerpt', 'image_url', 'published_at', 'imported_at', 'is_visible',
        'source_hash', 'source_domain', 'collection_status', 'collected_at',
        'title_hash', 'topic_group_id', 'is_topic_primary', 'similarity_score',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime', 'imported_at' => 'datetime',
            'is_visible' => 'boolean', 'collected_at' => 'datetime',
            'is_topic_primary' => 'boolean', 'similarity_score' => 'float',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_topic_primary', true);
    }

    public function relatedSources()
    {
        return static::query()->where('topic_group_id', $this->topic_group_id)->whereKeyNot($this->getKey());
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(RssFeed::class, 'rss_feed_id');
    }
}
