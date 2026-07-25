<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\MediaBanner;
use App\Models\NewsArticle;
use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use App\Models\Setting;
use App\Observers\FlushHomeCacheObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            NewsArticle::class,
            MediaBanner::class,
            RssFeed::class,
            RssImportedArticle::class,
            Category::class,
            Setting::class,
        ] as $model) {
            $model::observe(FlushHomeCacheObserver::class);
        }
    }
}
