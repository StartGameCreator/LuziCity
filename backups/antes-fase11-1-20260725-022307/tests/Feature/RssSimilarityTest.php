<?php

namespace Tests\Feature;

use App\Jobs\CollectRssFeedJob;
use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RssSimilarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_similar_titles_are_grouped_with_primary_and_related_source(): void
    {
        Http::fake(['https://1.1.1.1/feed' => Http::response('<?xml version="1.0"?><rss><channel>
          <item><title>Prefeitura inaugura nova escola municipal em Luziania</title><link>https://a.test/1</link></item>
          <item><title>Nova escola municipal em Luziania e inaugurada pela prefeitura</title><link>https://b.test/2</link></item>
        </channel></rss>', 200)]);
        $feed = RssFeed::create(['name' => 'Fonte', 'url' => 'https://1.1.1.1/feed', 'category' => 'Cidade', 'is_active' => true, 'frequency_minutes' => 60]);

        CollectRssFeedJob::dispatchSync($feed->id, 12);

        $this->assertSame(2, RssImportedArticle::count());
        $this->assertSame(1, RssImportedArticle::primary()->count());
        $this->assertSame(1, RssImportedArticle::query()->where('is_topic_primary', false)->count());
        $this->assertSame(1, RssImportedArticle::primary()->first()->relatedSources()->count());
        $this->assertSame(0, RssImportedArticle::visible()->count(), 'Itens aguardam revisão humana antes de serem exibidos.');
    }
}
