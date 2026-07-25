<?php

namespace App\Services\Search;

use App\Models\NewsArticle;
use App\Models\RealEstateListing;
use App\Models\VehicleListing;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UnifiedSearchService
{
    public function search(string $term, string $type = 'all', int $limit = 8): array
    {
        $term = $this->normalize($term);

        if ($term === '') {
            return ['news' => collect(), 'properties' => collect(), 'vehicles' => collect()];
        }

        return [
            'news' => in_array($type, ['all', 'news'], true) ? $this->news($term, $limit) : collect(),
            'properties' => in_array($type, ['all', 'properties'], true) ? $this->properties($term, $limit) : collect(),
            'vehicles' => in_array($type, ['all', 'vehicles'], true) ? $this->vehicles($term, $limit) : collect(),
        ];
    }

    public function suggestions(string $term, int $limit = 8): Collection
    {
        $results = $this->search($term, 'all', 4);

        return collect()
            ->merge($results['news']->map(fn (NewsArticle $item) => [
                'type' => 'Notícia', 'title' => $item->title, 'url' => route('news.show', $item),
            ]))
            ->merge($results['properties']->map(fn (RealEstateListing $item) => [
                'type' => 'Imóvel', 'title' => $item->title, 'url' => route('real-estate.show', $item),
            ]))
            ->merge($results['vehicles']->map(fn (VehicleListing $item) => [
                'type' => 'Veículo', 'title' => $item->title, 'url' => route('vehicles.show', $item),
            ]))
            ->unique('url')
            ->take($limit)
            ->values();
    }

    private function news(string $term, int $limit): Collection
    {
        $like = "%{$term}%";

        return NewsArticle::query()
            ->published()
            ->with('category:id,name,slug')
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('body', 'like', $like);
            })
            ->orderByRaw('CASE WHEN LOWER(title) = ? THEN 0 WHEN LOWER(title) LIKE ? THEN 1 ELSE 2 END', [$term, "{$term}%"])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    private function properties(string $term, int $limit): Collection
    {
        $like = "%{$term}%";

        return RealEstateListing::query()
            ->published()
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('neighborhood', 'like', $like)
                    ->orWhere('property_type', 'like', $like);
            })
            ->orderByRaw('CASE WHEN LOWER(title) = ? THEN 0 WHEN LOWER(title) LIKE ? THEN 1 ELSE 2 END', [$term, "{$term}%"])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    private function vehicles(string $term, int $limit): Collection
    {
        $like = "%{$term}%";

        return VehicleListing::query()
            ->published()
            ->where(function ($query) use ($like): void {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('city', 'like', $like);
            })
            ->orderByRaw('CASE WHEN LOWER(title) = ? THEN 0 WHEN LOWER(title) LIKE ? THEN 1 ELSE 2 END', [$term, "{$term}%"])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    private function normalize(string $term): string
    {
        return Str::of($term)->squish()->lower()->limit(100, '')->toString();
    }
}
