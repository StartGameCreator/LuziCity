<?php

namespace App\Services;

use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use App\Services\Cache\HomeCache;
use App\Services\Security\PublicUrlGuard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class RssImportService
{
    public function __construct(private readonly PublicUrlGuard $urlGuard, private readonly RssSimilarityService $similarity) {}

    public function importAll(int $limit = 12): array
    {
        $summary = ['created' => 0, 'updated' => 0, 'failed' => 0, 'messages' => []];
        foreach (RssFeed::query()->usable()->orderBy('sort_order')->orderBy('name')->get() as $feed) {
            $result = $this->importFeed($feed, $limit);
            foreach (['created', 'updated', 'failed'] as $key) $summary[$key] += $result[$key];
            $summary['messages'][] = $result['message'];
        }
        HomeCache::flush();
        return $summary;
    }

    public function importFeed(RssFeed $feed, int $limit = 12): array
    {
        try {
            $url = $this->urlGuard->validate($feed->url);
            $response = Http::timeout(10)->connectTimeout(3)->withOptions(['allow_redirects' => false])
                ->withHeaders(['User-Agent' => 'Luzicity RSS Collector/1.0', 'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml'])
                ->get($url);

            if (! $response->successful()) return $this->feedResult($feed, 0, 0, 1, "HTTP {$response->status()}");
            if (strlen($response->body()) > 2 * 1024 * 1024) return $this->feedResult($feed, 0, 0, 1, 'RSS excede o limite de 2 MB');

            $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
            if (! $xml) return $this->feedResult($feed, 0, 0, 1, 'RSS invalido');

            $created = 0;
            $duplicates = 0;
            foreach (collect($this->rssXmlItems($xml))->take(max(1, min(30, $limit))) as $item) {
                $data = $this->normalizeItem($item, $feed);
                if (! filled($data['title']) || ! filled($data['original_url'])) continue;
                $data = $this->similarity->enrich($data);

                $article = RssImportedArticle::query()
                    ->where('source_hash', $data['source_hash'])
                    ->orWhere('original_url', $data['original_url'])
                    ->first();
                if ($article) {
                    $article->update($data);
                    $duplicates++;
                } else {
                    RssImportedArticle::create($data);
                    $created++;
                }
            }

            HomeCache::flush();
            return $this->feedResult($feed, $created, $duplicates, 0, ($created + $duplicates).' item(ns)');
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
        $originalUrl = $this->canonicalUrl($link ?: $guid);
        try { $publishedAt = filled($publishedRaw) ? Carbon::parse($publishedRaw) : now(); } catch (Throwable) { $publishedAt = now(); }

        return [
            'rss_feed_id' => $feed->id, 'source_name' => $feed->name, 'category' => $feed->category ?: 'RSS',
            'title' => Str::limit(trim(html_entity_decode(strip_tags((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 255, ''),
            'original_url' => $originalUrl, 'guid' => $guid ?: null,
            'excerpt' => Str::limit(trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 360),
            'image_url' => $this->imageFromItem($item, $media, $description, $content),
            'published_at' => $publishedAt, 'imported_at' => now(), 'is_visible' => true,
            'source_hash' => hash('sha256', mb_strtolower($originalUrl ?: $guid)),
            'source_domain' => parse_url($originalUrl, PHP_URL_HOST) ?: null,
            'collection_status' => 'pending_review', 'collected_at' => now(),
        ];
    }

    private function canonicalUrl(string $url): string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === '') return '';
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) return $url;
        return strtolower($parts['scheme']).'://'.strtolower($parts['host']).(isset($parts['port']) ? ':'.$parts['port'] : '').($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function imageFromItem(\SimpleXMLElement $item, ?\SimpleXMLElement $media, string $description, string $content): ?string
    {
        $image = (string) ($media?->content?->attributes()?->url ?? $media?->thumbnail?->attributes()?->url ?? $item->enclosure?->attributes()?->url ?? '');
        if (filled($image)) return $image;
        return preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description.' '.$content, $matches) ? html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
    }

    private function rssXmlItems(\SimpleXMLElement $xml): array
    {
        $items = [];
        foreach (($xml->channel->item ?? []) as $item) $items[] = $item;
        if ($items === []) foreach (($xml->entry ?? []) as $item) $items[] = $item;
        return $items;
    }

    private function feedResult(RssFeed $feed, int $created, int $updated, int $failed, string $message): array
    {
        $feed->update($failed ? [
            'last_collected_at' => now(), 'last_failure_at' => now(), 'last_failure_message' => Str::limit($message, 1000, ''),
            'consecutive_failures' => $feed->consecutive_failures + 1, 'next_collection_at' => now()->addMinutes($feed->frequency_minutes ?: 60),
        ] : [
            'last_collected_at' => now(), 'last_success_at' => now(), 'last_failure_message' => null, 'consecutive_failures' => 0,
            'items_collected' => $feed->items_collected + $created, 'duplicates_found' => $feed->duplicates_found + $updated,
            'next_collection_at' => now()->addMinutes($feed->frequency_minutes ?: 60),
        ]);
        return compact('created', 'updated', 'failed') + ['message' => "{$feed->name}: {$message}"];
    }
}
