<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssImportedArticle extends Model
{
    protected $fillable = [
        'rss_feed_id',
        'source_name',
        'category',
        'title',
        'original_url',
        'guid',
        'excerpt',
        'image_url',
        'published_at',
        'imported_at',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'imported_at' => 'datetime',
            'is_visible' => 'boolean',
        ];
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(RssFeed::class, 'rss_feed_id');
    }
}
