<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MediaBanner;
use App\Models\NewsArticle;
use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use App\Models\Setting;
use App\Services\Cache\HomeCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    private const DEFAULT_CONTENT_CACHE_TTL_MINUTES = 5;

    private ?Collection $rssFeedsMemo = null;

    private ?Collection $validRssFeedsMemo = null;

    public function __invoke(): View
    {
        $articles = collect();

        try {
            $articles = Cache::remember(
                HomeCache::key('articles'),
                now()->addMinutes($this->contentCacheTtlMinutes()),
                fn () => NewsArticle::query()
                    ->with(['category', 'author'])
                    ->published()
                    ->latest('published_at')
                    ->take(7)
                    ->get(),
            );
        } catch (Throwable) {
            $articles = collect();
        }

        if ($articles->isEmpty()) {
            $articles = collect($this->sampleArticles());
        }

        return view('home', [
            'leadArticle' => $articles->first(),
            'featuredArticles' => $articles->skip(1)->take(2),
            'latestArticles' => $articles->skip(3),
            'youtubeBanners' => $this->carouselBanners(MediaBanner::TYPE_YOUTUBE),
            'facebookReels' => $this->carouselBanners(MediaBanner::TYPE_FACEBOOK_REEL),
            'homeLiveBroadcast' => Setting::homeLiveBroadcast(),
            'visualBlocks' => Setting::visualBlocks(),
            'topicMenu' => $this->topicMenu(),
            'rssFeeds' => $this->rssFeeds(),
            'rssItems' => $this->rssItems(),
            'showAds' => ! auth()->user()?->hasAdFreeAccess(),
        ]);
    }

    private function carouselBanners(string $type)
    {
        $items = $this->newsCarouselItems($type)->concat($this->mediaBanners($type))->values();

        if ($items->isNotEmpty()) {
            return $items;
        }

        return collect([
            ['title' => null, 'embed_code' => null, 'image_url' => null],
        ]);
    }

    private function newsCarouselItems(string $type)
    {
        try {
            return Cache::remember(
                HomeCache::key("news-carousel:{$type}"),
                now()->addMinutes($this->contentCacheTtlMinutes()),
                fn () => NewsArticle::query()
                    ->forCarousel($type)
                    ->orderBy('carousel_sort_order')
                    ->latest('published_at')
                    ->take(8)
                    ->get()
                    ->map(fn (NewsArticle $article) => [
                        'title' => $article->title,
                        'embed_code' => $article->carousel_embed_code,
                        'image_url' => $this->carouselImageUrl($article->carousel_image_path),
                    ]),
            );
        } catch (Throwable) {
            return collect();
        }
    }

    private function mediaBanners(string $type)
    {
        try {
            return Cache::remember(
                HomeCache::key("media-banners:{$type}"),
                now()->addMinutes($this->contentCacheTtlMinutes()),
                fn () => MediaBanner::query()
                    ->ofType($type)
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get()
                    ->map(fn (MediaBanner $banner) => [
                        'title' => $banner->title,
                        'embed_code' => $banner->embed_code,
                        'image_url' => null,
                    ]),
            );
        } catch (Throwable) {
            return collect();
        }
    }

    private function carouselImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }

    private function rssFeeds(): Collection
    {
        if ($this->rssFeedsMemo !== null) {
            return $this->rssFeedsMemo;
        }

        try {
            $feeds = Cache::remember(
                HomeCache::key('rss-feeds'),
                now()->addMinutes($this->contentCacheTtlMinutes()),
                fn () => RssFeed::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(),
            );

            if ($feeds->isNotEmpty()) {
                return $this->rssFeedsMemo = $feeds;
            }
        } catch (Throwable) {
            //
        }

        return $this->rssFeedsMemo = collect([
            ['name' => 'Últimas Notícias', 'url' => '#', 'category' => 'Geral'],
            ['name' => 'Brasil', 'url' => '#', 'category' => 'Nacional'],
            ['name' => 'Mundo', 'url' => '#', 'category' => 'Internacional'],
            ['name' => 'Economia', 'url' => '#', 'category' => 'Economia'],
            ['name' => 'Esportes', 'url' => '#', 'category' => 'Esportes'],
            ['name' => 'Tecnologia', 'url' => '#', 'category' => 'Tecnologia'],
        ]);
    }

    private function rssItems()
    {
        $importedItems = $this->importedRssItems();

        if ($importedItems->isNotEmpty()) {
            return $importedItems;
        }

        $cacheKey = $this->rssCacheKey();
        $cachedItems = Cache::get($cacheKey);

        if ($cachedItems !== null && $cachedItems->isNotEmpty()) {
            return $cachedItems;
        }

        $feeds = $this->validRssFeeds()->take(6);

        $items = $feeds
            ->flatMap(fn ($feed) => $this->itemsFromFeed($feed)->take(4))
            ->sortByDesc(fn ($item) => $item['published_at']?->timestamp ?? 0)
            ->take(12)
            ->values();

        if ($items->isNotEmpty()) {
            Cache::put($cacheKey, $items, now()->addMinutes(12));
        }

        return $items;
    }

    private function importedRssItems()
    {
        try {
            return Cache::remember(
                HomeCache::key('imported-rss-items'),
                now()->addMinutes(2),
                fn () => RssImportedArticle::query()
                    ->visible()
                    ->latest('published_at')
                    ->latest('id')
                    ->take(12)
                    ->get()
                    ->map(fn (RssImportedArticle $article) => [
                        'source' => $article->source_name,
                        'category' => $article->category ?: 'RSS',
                        'title' => $article->title,
                        'excerpt' => $article->excerpt,
                        'url' => $article->original_url,
                        'image' => $article->image_url,
                        'published_at' => $article->published_at,
                        'published_label' => $article->published_at?->diffForHumans() ?? 'Importado do RSS',
                    ]),
            );
        } catch (Throwable) {
            return collect();
        }
    }

    private function rssCacheKey(): string
    {
        $signature = $this->validRssFeeds()
            ->map(fn ($feed) => implode('|', [
                data_get($feed, 'id'),
                data_get($feed, 'url'),
                data_get($feed, 'updated_at'),
            ]))
            ->implode('::');

        return HomeCache::key('rss-items:'.md5($signature));
    }

    private function validRssFeeds(): Collection
    {
        return $this->validRssFeedsMemo ??= $this->rssFeeds()
            ->filter(fn ($feed) => filled(data_get($feed, 'url')) && data_get($feed, 'url') !== '#')
            ->values();
    }

    private function itemsFromFeed($feed)
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(2)
                ->withoutVerifying()
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 Luzicity RSS Reader'])
                ->get(data_get($feed, 'url'));

            if (! $response->successful()) {
                return collect();
            }

            $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

            if (! $xml) {
                return collect();
            }

            return collect($this->rssXmlItems($xml))
                ->take(6)
                ->map(fn ($item) => $this->normalizeRssItem($item, $feed))
                ->filter(fn ($item) => filled($item['title']));
        } catch (Throwable) {
            return collect();
        }
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

    private function normalizeRssItem(\SimpleXMLElement $item, $feed): array
    {
        $namespaces = $item->getNamespaces(true);
        $media = isset($namespaces['media']) ? $item->children($namespaces['media']) : null;
        $link = (string) ($item->link['href'] ?? $item->link ?? '');
        $description = (string) ($item->description ?? $item->summary ?? $item->content ?? '');
        $publishedRaw = (string) ($item->pubDate ?? $item->published ?? $item->updated ?? '');

        try {
            $publishedAt = filled($publishedRaw) ? Carbon::parse($publishedRaw) : null;
        } catch (Throwable) {
            $publishedAt = null;
        }

        return [
            'source' => data_get($feed, 'name'),
            'category' => data_get($feed, 'category') ?: 'RSS',
            'title' => html_entity_decode(strip_tags((string) $item->title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'excerpt' => Str::limit(trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 260),
            'url' => $link,
            'image' => (string) ($media?->content?->attributes()?->url ?? $item->enclosure?->attributes()?->url ?? ''),
            'published_at' => $publishedAt,
            'published_label' => $publishedAt?->diffForHumans() ?? 'Atualizacao RSS',
        ];
    }

    private function topicMenu()
    {
        try {
            $categories = Cache::remember(
                'home:topic-menu:v1',
                now()->addMinutes($this->contentCacheTtlMinutes()),
                fn () => Category::query()
                    ->with(['children' => fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')])
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(),
            );

            if ($categories->isNotEmpty()) {
                return $categories;
            }
        } catch (Throwable) {
            //
        }

        return collect([
            ['name' => 'Turismo', 'slug' => 'turismo', 'children' => []],
            ['name' => 'Política', 'slug' => 'politica', 'children' => []],
            ['name' => 'Economia', 'slug' => 'economia', 'children' => []],
            ['name' => 'Cultura', 'slug' => 'cultura', 'children' => []],
            ['name' => 'Tecnologia', 'slug' => 'tecnologia', 'children' => []],
            ['name' => 'Esportes', 'slug' => 'esportes', 'children' => []],
            ['name' => 'Ciência e Tecnologia', 'slug' => 'ciencia-e-tecnologia', 'children' => []],
            ['name' => 'Música', 'slug' => 'musica', 'children' => []],
            ['name' => 'TV e Cinema', 'slug' => 'tv-e-cinema', 'children' => []],
            ['name' => 'Entretenimento', 'slug' => 'entretenimento', 'children' => []],
        ]);
    }

    private function sampleArticles(): array
    {
        return [
            [
                'section' => 'Turismo',
                'title' => 'Luzicity prepara cobertura local com foco em leitura rapida e confiavel',
                'excerpt' => 'A nova plataforma prioriza noticias organizadas, navegacao leve em celulares e acesso publico ao conteudo principal.',
                'author_name' => 'Redacao Luzicity',
                'published_label' => 'Hoje',
            ],
            [
                'section' => 'Tecnologia',
                'title' => 'Login social e perfis editoriais entram na primeira fase da plataforma',
                'excerpt' => 'Usuarios poderao acessar com provedores sociais e receber papeis como assinante, jornalista, colunista ou anunciante.',
                'author_name' => 'Equipe Digital',
                'published_label' => 'Atualizado agora',
            ],
            [
                'section' => 'Comunidade',
                'title' => 'Assinantes terao experiencia sem anuncios em toda a navegacao',
                'excerpt' => 'A administracao podera liberar acesso sem publicidade para usuarios assinantes diretamente pelo painel.',
                'author_name' => 'Redacao',
                'published_label' => 'Hoje',
            ],
            [
                'section' => 'Politica',
                'title' => 'Painel editorial organiza autores, colunistas e moderadores',
                'excerpt' => 'Fluxos internos ajudam a separar producao jornalistica, opiniao, moderacao e gestao administrativa.',
                'author_name' => 'Luzicity',
                'published_label' => 'Manha',
            ],
            [
                'section' => 'Economia',
                'title' => 'Area de anunciantes fica preparada para campanhas e relatorios',
                'excerpt' => 'O modulo comercial nasce integrado ao cadastro de usuarios e aos espacos de publicidade.',
                'author_name' => 'Luzicity Negocios',
                'published_label' => 'Ontem',
            ],
            [
                'section' => 'Cultura',
                'title' => 'Design mobile-first melhora leitura de colunas e materias longas',
                'excerpt' => 'Tipografia, contraste e espacamento foram definidos para conforto em telas pequenas.',
                'author_name' => 'Coluna Design',
                'published_label' => 'Ontem',
            ],
        ];
    }
    private function contentCacheTtlMinutes(): int
    {
        return max(1, (int) config('luzicity.home_cache_ttl_minutes', self::DEFAULT_CONTENT_CACHE_TTL_MINUTES));
    }

}
