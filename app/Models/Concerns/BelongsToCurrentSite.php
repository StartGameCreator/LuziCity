<?php

namespace App\Models\Concerns;

use App\Models\NewsArticle;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait BelongsToCurrentSite
{
    public static function bootBelongsToCurrentSite(): void
    {
        static::addGlobalScope('current_site', function (Builder $query): void {
            if ($site = Site::current()) {
                $siteColumn = $query->getModel()->qualifyColumn('site_id');
                if ($query->getModel() instanceof NewsArticle
                    && ! request()->is('admin', 'admin/*') && Schema::hasTable('news_distributions')) {
                    $query->where(function (Builder $visibility) use ($site, $siteColumn): void {
                        $visibility->where($siteColumn, $site->id)->orWhereExists(function ($references) use ($site): void {
                            $references->selectRaw('1')->from('news_distributions')
                                ->whereColumn('news_distributions.source_article_id', 'news_articles.id')
                                ->where('news_distributions.target_site_id', $site->id)
                                ->where('news_distributions.mode', 'reference');
                        });
                    });
                } else {
                    $query->where($siteColumn, $site->id);
                }
            }
        });
        static::creating(function ($model): void {
            if (! $model->site_id && Schema::hasTable('sites')) {
                $model->site_id = Site::current()?->id ?? Site::withoutGlobalScopes()->where('is_default', true)->value('id');
            }
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeForAllSites(Builder $query): Builder
    {
        return $query->withoutGlobalScope('current_site');
    }
}
