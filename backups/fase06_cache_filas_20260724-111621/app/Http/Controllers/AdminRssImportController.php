<?php

namespace App\Http\Controllers;

use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use App\Services\RssImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminRssImportController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.rss-imports.index', [
            'feeds' => RssFeed::query()
                ->where('is_active', true)
                ->where('url', '<>', '#')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->withCount('importedArticles')
                ->get(),
            'articles' => RssImportedArticle::query()
                ->with('feed')
                ->latest('published_at')
                ->latest('id')
                ->take(30)
                ->get(),
            'stats' => [
                'total' => RssImportedArticle::query()->count(),
                'visible' => RssImportedArticle::query()->visible()->count(),
                'feeds' => RssFeed::query()->usable()->count(),
            ],
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $limit = max(1, min(30, (int) $request->input('limit', 12)));
        $summary = app(RssImportService::class)->importAll($limit);

        return back()->with(
            'status',
            "Importacao concluida: {$summary['created']} nova(s), {$summary['updated']} atualizada(s), {$summary['failed']} falha(s). ".implode(' | ', $summary['messages'])
        );
    }

    public function update(Request $request, RssImportedArticle $article): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'clear_image' => ['nullable', 'boolean'],
        ]);

        $imageUrl = $data['image_url'] ?? $article->image_url;

        if ($request->boolean('clear_image')) {
            $imageUrl = null;
        }

        if ($request->hasFile('image_upload')) {
            $imageUrl = $this->storeUploadedImage($request);
        }

        $article->update([
            'is_visible' => $request->boolean('is_visible'),
            'image_url' => $imageUrl,
        ]);

        Cache::forget('home:rss-items');

        return back()->with('status', 'Noticia RSS atualizada.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }

    private function storeUploadedImage(Request $request): string
    {
        $file = $request->file('image_upload');
        $directory = public_path('uploads/rss-images');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return asset('uploads/rss-images/'.$filename);
    }
}
