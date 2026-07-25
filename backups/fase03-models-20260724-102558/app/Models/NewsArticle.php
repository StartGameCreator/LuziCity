<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NewsArticle extends Model
{
    protected $fillable = [
        'category_id',
        'author_id',
        'published_by',
        'title',
        'slug',
        'excerpt',
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
