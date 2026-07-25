<?php

namespace Tests\Feature\Performance;

use App\Services\Cache\HomeCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeCacheVersionTest extends TestCase
{
    public function test_flush_changes_home_cache_namespace(): void
    {
        Cache::forget('luzicity:home-cache-version');

        $before = HomeCache::key('articles');
        HomeCache::flush();
        $after = HomeCache::key('articles');

        $this->assertNotSame($before, $after);
    }
}
