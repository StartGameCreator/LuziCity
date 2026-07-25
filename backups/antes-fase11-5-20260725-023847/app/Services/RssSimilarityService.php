<?php

namespace App\Services;

use App\Models\RssImportedArticle;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RssSimilarityService
{
    public function enrich(array $data): array
    {
        $normalized = $this->normalize($data['title'] ?? '');
        $data['title_hash'] = hash('sha256', $normalized);
        $data['topic_group_id'] = null;
        $data['is_topic_primary'] = true;
        $data['similarity_score'] = null;

        $match = RssImportedArticle::query()
            ->where('title_hash', $data['title_hash'])
            ->orWhere(function ($query) use ($data) {
                $query->where('category', $data['category'] ?? null)
                    ->where('published_at', '>=', now()->subDays(7));
            })
            ->latest('published_at')
            ->limit(100)
            ->get()
            ->map(fn ($article) => [$article, $this->similarity($data['title'] ?? '', $article->title)])
            ->filter(fn ($pair) => $pair[1] >= 0.60)
            ->sortByDesc(fn ($pair) => $pair[1])
            ->first();

        if ($match) {
            [$article, $score] = $match;
            $group = $article->topic_group_id ?: (string) Str::uuid();
            if (! $article->topic_group_id) $article->update(['topic_group_id' => $group, 'is_topic_primary' => true]);
            $data['topic_group_id'] = $group;
            $data['is_topic_primary'] = false;
            $data['similarity_score'] = $score;
        }

        return $data;
    }

    public function similarity(string $left, string $right): float
    {
        $a = collect(explode(' ', $this->normalize($left)))->filter()->unique();
        $b = collect(explode(' ', $this->normalize($right)))->filter()->unique();
        if ($a->isEmpty() || $b->isEmpty()) return 0.0;
        return round($a->intersect($b)->count() / $a->merge($b)->unique()->count(), 4);
    }

    private function normalize(string $text): string
    {
        $text = Str::ascii(mb_strtolower(strip_tags($text)));
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?: '';
        $stop = ['a','o','as','os','de','da','do','das','dos','e','em','na','no','nas','nos','para','por','com','um','uma'];
        return collect(preg_split('/\s+/', trim($text)) ?: [])
            ->reject(fn ($word) => in_array($word, $stop, true) || strlen($word) < 3)
            ->unique()->sort()->implode(' ');
    }
}
