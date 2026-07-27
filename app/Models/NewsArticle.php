<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsArticle extends Model
{
    use BelongsToCurrentSite;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'category_id',
        'site_id',
        'origin_article_id',
        'origin_site_id',
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
        'is_sponsored', 'sponsor_advertiser_id', 'sponsor_campaign_id', 'sponsor_label',
        'sponsor_starts_at', 'sponsor_ends_at', 'sponsor_approved_by',
        'sponsor_approved_at', 'sponsored_views_count',
        'show_in_carousel',
        'carousel_type',
        'carousel_embed_code',
        'carousel_image_path',
        'carousel_sort_order',
        'cover_image_path',
        'cover_image_alt',
        'published_at', 'workflow_status', 'approved_by', 'approved_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'allow_ads' => 'boolean',
            'is_sponsored' => 'boolean',
            'sponsor_starts_at' => 'datetime',
            'sponsor_ends_at' => 'datetime',
            'sponsor_approved_at' => 'datetime',
            'sponsored_views_count' => 'integer',
            'show_in_carousel' => 'boolean',
            'carousel_sort_order' => 'integer',
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'ai_metadata' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function originArticle(): BelongsTo
    {
        return $this->belongsTo(self::class, 'origin_article_id');
    }

    public function originSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'origin_site_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(NewsDistribution::class, 'source_article_id');
    }

    public function attributionSite(): ?Site
    {
        if ($this->origin_site_id) {
            return $this->originSite;
        }

        return Site::current() && $this->site_id !== Site::current()->id ? $this->site : null;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(AdvertiserProfile::class, 'sponsor_advertiser_id');
    }

    public function sponsorCampaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'sponsor_campaign_id');
    }

    public function sponsoredIsVisible(): bool
    {
        if (! $this->is_sponsored) {
            return true;
        }

        return $this->sponsor_approved_at
            && (! $this->sponsor_starts_at || $this->sponsor_starts_at->lte(now()))
            && (! $this->sponsor_ends_at || $this->sponsor_ends_at->gte(now()));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $visibility): void {
                $visibility->where('is_sponsored', false)
                    ->orWhere(function (Builder $sponsored): void {
                        $sponsored->where('is_sponsored', true)
                            ->whereNotNull('sponsor_approved_at')
                            ->where(fn (Builder $period) => $period->whereNull('sponsor_starts_at')->orWhere('sponsor_starts_at', '<=', now()))
                            ->where(fn (Builder $period) => $period->whereNull('sponsor_ends_at')->orWhere('sponsor_ends_at', '>=', now()));
                    });
            });
    }

    public function scopeForCarousel(Builder $query, string $type): Builder
    {
        return $query
            ->published()
            ->where('show_in_carousel', true)
            ->where('carousel_type', $type);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(NewsArticleVersion::class)->orderByDesc('version');
    }

    public function editorialReviews(): HasMany
    {
        return $this->hasMany(NewsEditorialReview::class)->latest();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
