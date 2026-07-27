<?php

namespace Tests\Feature\Performance;

use App\Models\Category;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\Cache\PublicContentCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicContentCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_response_is_cached_and_has_shared_cache_headers(): void
    {
        $category = Category::create([
            'name' => 'Local',
            'slug' => 'cache-local',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        NewsArticle::create([
            'category_id' => $category->id,
            'author_id' => User::factory()->create()->id,
            'title' => 'Noticia em cache',
            'slug' => 'noticia-em-cache',
            'body' => 'Conteudo',
            'status' => NewsArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $first = $this->getJson('/api/v1/news?per_page=10');
        $first->assertOk()
            ->assertHeader('Cache-Control', 'max-age=60, public, s-maxage=60, stale-while-revalidate=30')
            ->assertJsonPath('data.0.title', 'Noticia em cache');

        $etag = $first->headers->get('ETag');
        $this->assertNotNull($etag);

        $this->withHeader('If-None-Match', $etag)
            ->getJson('/api/v1/news?per_page=10')
            ->assertStatus(304);
    }

    public function test_editorial_change_invalidates_versioned_public_cache(): void
    {
        Cache::forget('luzicity:public-content-cache-version');
        $before = PublicContentCache::key('news');

        Category::create([
            'name' => 'Cultura Cache',
            'slug' => 'cultura-cache',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertNotSame($before, PublicContentCache::key('news'));
    }

    public function test_home_uses_private_conditional_cache_for_guest_html(): void
    {
        $first = $this->get('/');
        $first->assertOk()
            ->assertHeader('Cache-Control', 'max-age=60, must-revalidate, private');

        $etag = $first->headers->get('ETag');
        $this->assertNotNull($etag);
    }
}
