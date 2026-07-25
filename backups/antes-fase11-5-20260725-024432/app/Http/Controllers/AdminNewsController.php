<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\NewsArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminNewsController extends Controller
{
    public function index(): View
    {
        $this->authorizeEditor();

        return view('admin.news.index', [
            'articles' => NewsArticle::query()
                ->with('category')
                ->latest('updated_at')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorizeEditor();

        return view('admin.news.form', [
            'article' => new NewsArticle(['status' => 'draft', 'allow_ads' => true]),
            'categories' => Category::query()->orderBy('name')->get(),
            'aiSettings' => \App\Models\Setting::aiSettings(),
            'aiPrefill' => [
                'title' => request('ai_title'),
                'subtitle' => request('ai_subtitle'),
                'excerpt' => request('ai_excerpt'),
                'body' => request('ai_body'),
                'slug' => request('ai_slug'),
                'seo_title' => request('ai_seo_title'),
                'seo_description' => request('ai_seo_description'),
                'category_id' => request('ai_category_id'),
                'tags' => json_decode((string) request('ai_tags', '[]'), true) ?: [],
                'ai_metadata' => json_decode((string) request('ai_metadata', '{}'), true) ?: [],
                'ai_execution_id' => request('ai_execution_id'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeEditor();

        $article = NewsArticle::query()->create($this->validatedData($request) + [
            'author_id' => $request->user()->id,
            'published_by' => null,
        ]);

        app(\App\Services\NewsEditorialWorkflowService::class)->snapshot($article, $request->user());
        return redirect()->route('admin.news.edit', $article)->with('status', 'Notícia salva como rascunho para revisão.');
    }

    public function edit(NewsArticle $news): View
    {
        $this->authorizeEditor();

        return view('admin.news.form', [
            'article' => $news,
            'categories' => Category::query()->orderBy('name')->get(),
            'aiSettings' => \App\Models\Setting::aiSettings(),
            'aiPrefill' => [],
        ]);
    }

    public function update(Request $request, NewsArticle $news): RedirectResponse
    {
        $this->authorizeEditor();

        $data = $this->validatedData($request);
        $data['published_by'] = $news->published_by;

        $news->update($data);
        app(\App\Services\NewsEditorialWorkflowService::class)->snapshot($news, $request->user());

        return back()->with('status', 'Notícia atualizada e nova versão registrada.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:240'],
            'slug' => ['nullable', 'string', 'max:220'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'ai_metadata' => ['nullable', 'array'],
            'ai_execution_id' => ['nullable', 'exists:ai_executions,id'],
            'body' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_premium' => ['nullable', 'boolean'],
            'allow_ads' => ['nullable', 'boolean'],
            'show_in_carousel' => ['nullable', 'boolean'],
            'carousel_type' => ['nullable', 'in:youtube,facebook_reel'],
            'carousel_embed_code' => ['nullable', 'string'],
            'carousel_sort_order' => ['nullable', 'integer', 'min:0'],
            'carousel_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'cover_image_alt' => ['nullable', 'string', 'max:180'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['status'] = 'draft';
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], $request->route('news')?->id);
        $data['is_premium'] = $request->boolean('is_premium');
        $data['allow_ads'] = $request->boolean('allow_ads', true);
        $data['show_in_carousel'] = $request->boolean('show_in_carousel');
        $data['carousel_type'] = $data['show_in_carousel'] ? ($data['carousel_type'] ?: 'youtube') : null;
        $data['carousel_sort_order'] = (int) ($data['carousel_sort_order'] ?? 0);

        if ($request->hasFile('carousel_image')) {
            $data['carousel_image_path'] = $this->storeCarouselImage($request);
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $this->storeNewsCoverImage($request);
        }

        unset($data['carousel_image']);
        unset($data['cover_image']);

        return $data;
    }

    private function storeCarouselImage(Request $request): string
    {
        $file = $request->file('carousel_image');
        $directory = public_path('uploads/news-carousel');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/news-carousel/'.$filename;
    }

    private function storeNewsCoverImage(Request $request): string
    {
        $file = $request->file('cover_image');
        $directory = public_path('uploads/news-covers');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/news-covers/'.$filename;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'noticia';
        $slug = $base;
        $count = 2;

        while (NewsArticle::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    private function authorizeEditor(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin', 'Jornalista', 'Colunista']), 403);
    }
}
