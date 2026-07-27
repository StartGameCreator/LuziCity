<?php

namespace App\Observers;

use App\Services\Cache\HomeCache;
use App\Services\Cache\PublicContentCache;

class FlushHomeCacheObserver
{
    public function saved(object $model): void
    {
        HomeCache::flush();
        PublicContentCache::flush();
    }

    public function deleted(object $model): void
    {
        HomeCache::flush();
        PublicContentCache::flush();
    }

    public function restored(object $model): void
    {
        HomeCache::flush();
        PublicContentCache::flush();
    }
}
