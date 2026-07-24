<?php

namespace App\Http\Controllers;

use App\Models\RssFeed;
use App\Services\RssImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class AdminRssFeedController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $feeds = RssFeed::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.rss-feeds.index', [
            'feeds' => $feeds,
            'diagnostics' => $request->boolean('testar_rss')
                ? $feeds->mapWithKeys(fn (RssFeed $feed) => [$feed->id => $this->diagnoseFeed($feed)])
                : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        RssFeed::query()->create($this->validatedData($request));
        Cache::forget('home:rss-items');

        return back()->with('status', 'Feed RSS salvo.');
    }

    public function update(Request $request, RssFeed $rssFeed): RedirectResponse
    {
        $this->authorizeAdmin();

        $rssFeed->update($this->validatedData($request));
        Cache::forget('home:rss-items');

        return back()->with('status', 'Feed RSS atualizado.');
    }

    public function refresh(Request $request, RssImportService $importer): RedirectResponse
    {
        $this->authorizeAdmin();

        $limit = max(1, min(30, (int) $request->input('limit', 12)));
        $summary = $importer->importAll($limit);

        Cache::forget('home:rss-items');
        RssFeed::query()
            ->where('is_active', true)
            ->where('url', '<>', '#')
            ->update(['updated_at' => now()]);

        $message = "RSS atualizado: {$summary['created']} nova(s), {$summary['updated']} atualizada(s), {$summary['failed']} falha(s).";

        if ($summary['messages'] !== []) {
            $message .= ' '.implode(' | ', $summary['messages']);
        }

        return redirect()
            ->route('admin.rss-feeds.index', ['testar_rss' => 1])
            ->with('status', $message);
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'url' => ['required', 'string', 'max:2048'],
            'category' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }

    private function diagnoseFeed(RssFeed $feed): array
    {
        if (! $feed->is_active) {
            return [
                'ok' => false,
                'message' => 'Feed desativado no painel.',
            ];
        }

        if (! filled($feed->url) || $feed->url === '#') {
            return [
                'ok' => false,
                'message' => 'Informe um link RSS valido.',
            ];
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(2)
                ->withoutVerifying()
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 Luzicity RSS Reader'])
                ->get($feed->url);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'A fonte respondeu com erro HTTP '.$response->status().'.',
                ];
            }

            $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

            if (! $xml) {
                return [
                    'ok' => false,
                    'message' => 'O link respondeu, mas o conteudo nao parece ser RSS valido.',
                ];
            }

            $items = collect($this->rssXmlItems($xml));

            return [
                'ok' => $items->isNotEmpty(),
                'message' => $items->isNotEmpty()
                    ? $items->count().' noticia(s) encontradas nesse feed.'
                    : 'O RSS abriu, mas nao trouxe noticias.',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'O PHP nao conseguiu conectar nesse RSS: '.$exception->getMessage(),
            ];
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
}
