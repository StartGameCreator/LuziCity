<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MediaBanner;
use App\Models\NewsArticle;
use App\Models\RadioRequest;
use App\Models\RealEstateListing;
use App\Models\RssFeed;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Models\VehicleListing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class AdminSystemHealthController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);

        $checks = collect([
            $this->databaseCheck(),
            $this->cacheCheck(),
            $this->storageCheck(),
            $this->rssCheck(),
            $this->socialLoginCheck(),
            $this->radioCheck(),
            $this->trackingPixelCheck(),
            $this->googleAdsCheck(),
            $this->contentCheck(),
            $this->rolesCheck(),
        ]);

        return view('admin.system-health.index', [
            'checks' => $checks,
            'summary' => [
                'ok' => $checks->where('status', 'ok')->count(),
                'warning' => $checks->where('status', 'warning')->count(),
                'error' => $checks->where('status', 'error')->count(),
            ],
            'stats' => $this->stats(),
        ]);
    }

    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();
            $tables = ['users', 'settings', 'rss_feeds', 'news_articles', 'categories', 'tags'];
            $missing = collect($tables)->reject(fn (string $table) => Schema::hasTable($table))->values();

            return $this->check(
                $missing->isEmpty() ? 'ok' : 'error',
                'Banco de dados',
                $missing->isEmpty() ? 'Conectado e com tabelas principais disponiveis.' : 'Faltam tabelas: '.$missing->implode(', '),
                'SQLite ativo: '.database_path('database.sqlite')
            );
        } catch (Throwable $exception) {
            return $this->check('error', 'Banco de dados', 'Nao foi possivel conectar ao banco.', $exception->getMessage());
        }
    }

    private function cacheCheck(): array
    {
        try {
            $key = 'system-health:test';
            Cache::put($key, 'ok', now()->addMinutes(2));

            return $this->check(
                Cache::get($key) === 'ok' ? 'ok' : 'warning',
                'Cache',
                Cache::get($key) === 'ok' ? 'Cache gravando e lendo normalmente.' : 'Cache respondeu, mas nao confirmou leitura.',
                'Driver atual: '.config('cache.default')
            );
        } catch (Throwable $exception) {
            return $this->check('error', 'Cache', 'Falha ao gravar cache.', $exception->getMessage());
        }
    }

    private function storageCheck(): array
    {
        $paths = [
            storage_path('framework/cache'),
            storage_path('framework/views'),
            storage_path('logs'),
            public_path(),
        ];

        $blocked = collect($paths)->reject(fn (string $path) => is_dir($path) && is_writable($path))->values();

        return $this->check(
            $blocked->isEmpty() ? 'ok' : 'warning',
            'Arquivos e permissoes',
            $blocked->isEmpty() ? 'Pastas principais gravaveis.' : 'Algumas pastas precisam de permissao de escrita.',
            $blocked->isEmpty() ? 'Tudo pronto para cache, views e logs.' : $blocked->implode(' | ')
        );
    }

    private function rssCheck(): array
    {
        $feeds = RssFeed::query()
            ->where('is_active', true)
            ->where('url', '<>', '#')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(5)
            ->get();

        if ($feeds->isEmpty()) {
            return $this->check('warning', 'RSS', 'Nenhum feed RSS real ativo cadastrado.', 'Cadastre fontes em Backend > RSS.');
        }

        $results = $feeds->map(function (RssFeed $feed) {
            try {
                $response = Http::timeout(6)
                    ->connectTimeout(2)
                    ->withoutVerifying()
                    ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 Luzicity RSS Reader'])
                    ->get($feed->url);

                if (! $response->successful()) {
                    return "{$feed->name}: HTTP {$response->status()}";
                }

                $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                $items = $xml ? $this->rssItemCount($xml) : 0;

                return $items > 0 ? "{$feed->name}: {$items} noticia(s)" : "{$feed->name}: sem itens";
            } catch (Throwable $exception) {
                return "{$feed->name}: falhou";
            }
        });

        $okCount = $results->filter(fn (string $result) => str_contains($result, 'noticia'))->count();

        return $this->check(
            $okCount > 0 ? ($okCount === $feeds->count() ? 'ok' : 'warning') : 'error',
            'RSS',
            "{$okCount} de {$feeds->count()} feed(s) responderam com noticias.",
            $results->implode(' | ')
        );
    }

    private function socialLoginCheck(): array
    {
        $providers = collect(Setting::socialLoginProviders());
        $enabled = $providers->filter(fn (array $provider) => ! empty($provider['enabled']));
        $configured = $enabled->filter(fn (array $provider) => filled($provider['client_id']) && filled($provider['client_secret']) && filled($provider['redirect']));

        return $this->check(
            $enabled->isEmpty() ? 'warning' : ($configured->count() === $enabled->count() ? 'ok' : 'warning'),
            'Login social',
            "{$configured->count()} de {$enabled->count()} provedor(es) ativos estao completos.",
            $enabled->isEmpty() ? 'Nenhum provedor ativo.' : $enabled->keys()->implode(', ')
        );
    }

    private function radioCheck(): array
    {
        $settings = Setting::radioSettings();
        $hasAudio = filled($settings['audio_stream_url'] ?? '');
        $hasVideo = filled($settings['tiktok_embed_code'] ?? '') || filled($settings['tiktok_url'] ?? '');
        $requests = RadioRequest::query()->count();

        return $this->check(
            $hasAudio || $hasVideo ? 'ok' : 'warning',
            'Radio Web',
            $hasAudio || $hasVideo ? 'Transmissao configurada.' : 'Audio/video da radio ainda nao configurado.',
            "{$requests} pedido(s)/mensagem(ns) registrados no chat e radio."
        );
    }

    private function trackingPixelCheck(): array
    {
        $pixels = Setting::trackingPixels();
        $active = collect($pixels)->filter(fn ($value) => filled($value))->count();

        return $this->check(
            $active > 0 ? 'ok' : 'warning',
            'Pixels',
            $active > 0 ? "{$active} pixel(is) configurado(s)." : 'Nenhum pixel configurado.',
            'Meta e TikTok podem ser preenchidos em Backend > Pixels.'
        );
    }

    private function googleAdsCheck(): array
    {
        $ads = Setting::googleAds();
        $slots = collect($ads['slots'] ?? [])->filter(fn ($value) => filled($value))->count();
        $client = filled($ads['client'] ?? '');

        return $this->check(
            $client && $slots > 0 ? 'ok' : 'warning',
            'Google Ads',
            $client ? "{$slots} slot(s) preenchido(s)." : 'Cliente do Google Ads ainda nao configurado.',
            'Assinantes e patrocinadores continuam com navegacao sem blocos Google Ads.'
        );
    }

    private function contentCheck(): array
    {
        $published = NewsArticle::query()->where('status', 'published')->count();
        $categories = Category::query()->count();
        $tags = Tag::query()->count();

        return $this->check(
            $categories > 0 ? 'ok' : 'warning',
            'Conteudo',
            "{$published} noticia(s) publicada(s), {$categories} editoria(s), {$tags} tag(s).",
            $published > 0 ? 'Conteudo real ja cadastrado.' : 'A home ainda pode usar noticias de exemplo enquanto nao houver publicacoes.'
        );
    }

    private function rolesCheck(): array
    {
        $expected = ['Super Admin', 'Admin', 'Usuario', 'Assinante', 'Jornalista', 'Colunista', 'Anunciante', 'Patrocinador'];
        $missing = collect($expected)->reject(fn (string $role) => Role::where('name', $role)->exists())->values();

        return $this->check(
            $missing->isEmpty() ? 'ok' : 'warning',
            'Usuarios e permissoes',
            $missing->isEmpty() ? 'Papeis principais disponiveis.' : 'Alguns papeis ainda nao existem.',
            $missing->isEmpty() ? User::query()->count().' usuario(s) cadastrado(s).' : 'Faltando: '.$missing->implode(', ')
        );
    }

    private function stats(): array
    {
        return [
            ['label' => 'Usuarios', 'value' => User::query()->count()],
            ['label' => 'Noticias', 'value' => NewsArticle::query()->count()],
            ['label' => 'RSS ativos', 'value' => RssFeed::query()->where('is_active', true)->where('url', '<>', '#')->count()],
            ['label' => 'Banners', 'value' => MediaBanner::query()->count()],
            ['label' => 'Veiculos', 'value' => VehicleListing::query()->count()],
            ['label' => 'Imoveis', 'value' => RealEstateListing::query()->count()],
        ];
    }

    private function rssItemCount(\SimpleXMLElement $xml): int
    {
        $items = 0;

        foreach (($xml->channel->item ?? []) as $item) {
            $items++;
        }

        if ($items === 0) {
            foreach (($xml->entry ?? []) as $item) {
                $items++;
            }
        }

        return $items;
    }

    private function check(string $status, string $title, string $message, string $detail): array
    {
        return compact('status', 'title', 'message', 'detail');
    }
}