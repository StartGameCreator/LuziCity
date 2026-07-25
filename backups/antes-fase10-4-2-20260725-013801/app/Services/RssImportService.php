<?php

namespace App\Services;

use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use Carbon\Carbon;
use App\Services\Cache\HomeCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class RssImportService
{
    public function importAll(int $limit = 12): array
    {
        $summary = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'messages' => [],
        ];

        $feeds = RssFeed::query()
            ->where('is_active', true)
            ->where('url', '<>', '#')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($feeds as $feed) {
            $result = $this->importFeed($feed, $limit);
            $summary['created'] += $result['created'];
            $summary['updated'] += $result['updated'];
            $summary['failed'] += $result['failed'];
            $summary['messages'][] = $result['message'];
        }

        HomeCache::flush();

        return $summary;
    }

    public function importFeed(RssFeed $feed, int $limit = 12): array
    {
        try {
            $response = Http::timeout(10)
                ->connectTimeout(3)
                ->withoutVerifying()
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 Luzicity RSS Importer'])
                ->get($feed->url);

            if (! $response->successful()) {
                return $this->feedResult($feed, 0, 0, 1, "HTTP {$response->status()}");
            }

            $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

            if (! $xml) {
                return $this->feedResult($feed, 0, 0, 1, 'RSS invalido');
            }

            $created = 0;
            $updated = 0;

            foreach (collect($this->rssXmlItems($xml))->take($limit) as $item) {
                $data = $this->normalizeItem($item, $feed);

                if (! filled($data['title']) || ! filled($data['original_url'])) {
                    continue;
                }

                $article = RssImportedArticle::query()->updateOrCreate(
                    ['original_url' => $data['original_url']],
                    $data
                );

                $article->wasRecentlyCreated ? $created++ : $updated++;
            }

            return $this->feedResult($feed, $created, $updated, 0, ($created + $updated).' item(ns)');
        } catch (Throwable $exception) {
            return $this->feedResult($feed, 0, 0, 1, 'falhou: '.$exception->getMessage());
        }
    }

    private function normalizeItem(\SimpleXMLElement $item, RssFeed $feed): array
    {
        $namespaces = $item->getNamespaces(true);
        $media = isset($namespaces['media']) ? $item->children($namespaces['media']) : null;
        $content = isset($namespaces['content']) ? (string) ($item->children($namespaces['content'])->encoded ?? '') : '';
        $description = (string) ($item->description ?? $item->summary ?? $item->content ?? $content);
        $publishedRaw = (string) ($item->pubDate ?? $item->published ?? $item->updated ?? '');
        $link = trim((string) ($item->link['href'] ?? $item->link ?? ''));
        $guid = trim((string) ($item->guid ?? $item->id ?? ''));

        try {
            $publishedAt = filled($publishedRaw) ? Carbon::parse($publishedRaw) : now();
        } catch (Throwable) {
            $publishedAt = now();
        }

        return [
            'rss_feed_id' => $feed->id,
            'source_name' => $feed->name,
            'category' => $feed->category ?: 'RSS',
            'title' => html_entity_decode(strip_tags((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'original_url' => $link ?: ($guid ?: Str::uuid()->toString()),
            'guid' => $guid ?: null,
            'excerpt' => Str::limit(trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 360),
            'image_url' => $this->imageFromItem($item, $media, $description, $content),
            'published_at' => $publishedAt,
            'imported_at' => now(),
            'is_visible' => true,
        ];
    }

    private function imageFromItem(\SimpleXMLElement $item, ?\SimpleXMLElement $media, string $description, string $content): ?string
    {
        $image = (string) ($media?->content?->attributes()?->url ?? $media?->thumbnail?->attributes()?->url ?? $item->enclosure?->attributes()?->url ?? '');

        if (filled($image)) {
            return $image;
        }

        $html = $description.' '.$content;

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private function rssXmlItems(\SimpleXMLElement $xml): array
    {
        $items = [];

        foreach (($xml->channel->item ?? []) as $item) {
            $items[] = $item;
        }

        if ($items === []) {
            foreach (($xml->entry ?? []) as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function feedResult(RssFeed $feed, int $created, int $updated, int $failed, string $message): array
    {
        $feed->update($failed > 0 ? [
            'last_collected_at' => now(), 'last_failure_at' => now(),
            'last_failure_message' => \Illuminate\Support\Str::limit($message, 1000, ''),
            'consecutive_failures' => $feed->consecutive_failures + 1,
            'next_collection_at' => now()->addMinutes($feed->frequency_minutes ?: 60),
        ] : [
            'last_collected_at' => now(), 'last_success_at' => now(), 'last_failure_message' => null,
            'consecutive_failures' => 0, 'items_collected' => $feed->items_collected + $created,
            'duplicates_found' => $feed->duplicates_found + $updated,
            'next_collection_at' => now()->addMinutes($feed->frequency_minutes ?: 60),
        ]);
        return compact('created', 'updated', 'failed') + [
            'message' => "{$feed->name}: {$message}",
        ];
    }
}
