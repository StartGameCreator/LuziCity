<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\NewsDistribution;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsDistributionService
{
    public function distribute(NewsArticle $source, Site $target, string $mode, User $user): NewsDistribution
    {
        if ($source->site_id === $target->id) {
            throw ValidationException::withMessages(['target_site_id' => 'Escolha outro site.']);
        }
        if (NewsDistribution::where('source_article_id', $source->id)->where('target_site_id', $target->id)->exists()) {
            throw ValidationException::withMessages(['target_site_id' => 'Esta notícia já foi distribuída para o site escolhido.']);
        }

        return DB::transaction(function () use ($source, $target, $mode, $user): NewsDistribution {
            $copy = $mode === 'copy' ? $this->copy($source, $target) : null;

            return NewsDistribution::create([
                'source_article_id' => $source->id, 'source_site_id' => $source->site_id,
                'target_site_id' => $target->id, 'mode' => $mode,
                'target_article_id' => $copy?->id, 'distributed_by' => $user->id,
            ]);
        });
    }

    private function copy(NewsArticle $source, Site $target): NewsArticle
    {
        $copy = $source->replicate([
            'slug', 'status', 'workflow_status', 'published_at', 'published_by',
            'approved_by', 'approved_at', 'sponsor_advertiser_id', 'sponsor_campaign_id',
            'sponsor_approved_by', 'sponsor_approved_at', 'sponsored_views_count',
        ]);
        $copy->forceFill([
            'site_id' => $target->id, 'origin_article_id' => $source->id, 'origin_site_id' => $source->site_id,
            'slug' => $this->slug($source->slug, $target), 'status' => 'draft', 'workflow_status' => 'draft',
            'published_at' => null, 'published_by' => null, 'approved_by' => null, 'approved_at' => null,
            'is_sponsored' => false, 'sponsored_views_count' => 0,
        ])->save();
        $copy->tags()->sync($source->tags()->pluck('tags.id'));

        return $copy;
    }

    private function slug(string $sourceSlug, Site $target): string
    {
        $base = Str::limit($sourceSlug.'-'.$target->slug, 210, '');
        $slug = $base;
        $suffix = 2;
        while (NewsArticle::forAllSites()->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 205, '').'-'.$suffix++;
        }

        return $slug;
    }
}
