<?php

namespace Tests\Unit\Cache;

use App\Services\Cache\PublicContentCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicContentCacheUnitTest extends TestCase
{
    public function test_versioned_keys_change_after_flush(): void
    {
        Cache::forget('luzicity:public-content-cache-version');
        $before = PublicContentCache::key('news');
        $version = PublicContentCache::flush();

        $this->assertSame(2, $version);
        $this->assertNotSame($before, PublicContentCache::key('news'));
        $this->assertStringContainsString(':v2:', PublicContentCache::key('news'));
    }
}
