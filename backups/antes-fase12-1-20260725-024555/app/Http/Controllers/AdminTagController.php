<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminTagController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.tags.index', [
            'tags' => Tag::query()
                ->withCount('articles')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        Tag::query()->create($this->validatedData($request));

        return back()->with('status', 'Tag salva.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $this->authorizeAdmin();

        $tag->update($this->validatedData($request, $tag));

        return back()->with('status', 'Tag atualizada.');
    }

    private function validatedData(Request $request, ?Tag $tag = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $tag?->id);

        return $data;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'tag';
        $slug = $base;
        $count = 2;

        while (Tag::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
