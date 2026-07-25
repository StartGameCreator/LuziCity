<?php

namespace Tests\Feature;

use App\Jobs\CollectRssFeedJob;
use App\Models\RssCollectionRun;
use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RssCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_collector_stores_only_short_excerpt_and_deduplicates_origin(): void
    {
        Http::fake(['https://1.1.1.1/feed' => Http::response('<?xml version="1.0"?><rss><channel><item><title>Noticia</title><link>https://example.com/a#fragmento</link><description>'.str_repeat('texto ', 150).'</description></item></channel></rss>', 200)]);
        $feed = RssFeed::create(['name' => 'Fonte', 'url' => 'https://1.1.1.1/feed', 'category' => 'Geral', 'is_active' => true, 'frequency_minutes' => 60]);

        CollectRssFeedJob::dispatchSync($feed->id, 12);
        CollectRssFeedJob::dispatchSync($feed->id, 12);

        $this->assertSame(1, RssImportedArticle::count());
        $article = RssImportedArticle::first();
        $this->assertSame('https://example.com/a', $article->original_url);
        $this->assertSame('pending_review', $article->collection_status);
        $this->assertLessThanOrEqual(363, mb_strlen($article->excerpt));
        $this->assertSame(2, RssCollectionRun::count());
    }

    public function test_collector_upgrades_legacy_article_without_creating_duplicate(): void
    {
        Http::fake(['https://1.1.1.1/feed' => Http::response('<?xml version="1.0"?><rss><channel><item><title>Atualizada</title><link>https://example.com/antiga</link><description>Resumo</description></item></channel></rss>', 200)]);
        $feed = RssFeed::create(['name' => 'Fonte', 'url' => 'https://1.1.1.1/feed', 'category' => 'Geral', 'is_active' => true, 'frequency_minutes' => 60]);
        RssImportedArticle::create(['rss_feed_id' => $feed->id, 'title' => 'Antiga', 'original_url' => 'https://example.com/antiga', 'is_visible' => true]);

        CollectRssFeedJob::dispatchSync($feed->id, 12);

        $this->assertSame(1, RssImportedArticle::count());
        $this->assertNotNull(RssImportedArticle::first()->source_hash);
    }
}
