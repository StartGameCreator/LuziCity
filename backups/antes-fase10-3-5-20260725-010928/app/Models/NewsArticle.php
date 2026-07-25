<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NewsArticle extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    protected $fillable = [
        'category_id',
        'author_id',
        'published_by',
        'title',
        'subtitle',
        'slug',
        'excerpt',
        'seo_title',
        'seo_description',
        'ai_metadata',
        'ai_execution_id',
        'body',
        'status',
        'is_premium',
        'allow_ads',
        'show_in_carousel',
        'carousel_type',
        'carousel_embed_code',
        'carousel_image_path',
        'carousel_sort_order',
        'cover_image_path',
        'cover_image_alt',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'allow_ads' => 'boolean',
            'show_in_carousel' => 'boolean',
            'carousel_sort_order' => 'integer',
            'published_at' => 'datetime',
            'ai_metadata' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeForCarousel(Builder $query, string $type): Builder
    {
        return $query
            ->published()
            ->where('show_in_carousel', true)
            ->where('carousel_type', $type);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
