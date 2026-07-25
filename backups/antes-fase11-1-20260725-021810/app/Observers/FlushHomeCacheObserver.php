<?php

namespace App\Observers;

use App\Services\Cache\HomeCache;

class FlushHomeCacheObserver
{
    public function saved(object $model): void
    {
        HomeCache::flush();
    }

    public function deleted(object $model): void
    {
        HomeCache::flush();
    }

    public function restored(object $model): void
    {
        HomeCache::flush();
    }
}
