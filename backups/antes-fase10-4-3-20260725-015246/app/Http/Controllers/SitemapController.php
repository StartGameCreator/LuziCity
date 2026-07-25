<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\RealEstateListing;
use App\Models\VehicleListing;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => now()->toAtomString(), 'changefreq' => 'hourly', 'priority' => '1.0'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => route('vehicles.index'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => route('real-estate.index'), 'changefreq' => 'daily', 'priority' => '0.8'],
        ]);

        NewsArticle::query()->published()->select(['slug','updated_at','published_at'])->orderByDesc('published_at')->chunk(500, function ($items) use ($urls) {
            foreach ($items as $item) $urls->push(['loc' => route('news.show', $item->slug), 'lastmod' => optional($item->updated_at ?? $item->published_at)->toAtomString(), 'changefreq' => 'daily', 'priority' => '0.9']);
        });
        VehicleListing::query()->published()->select(['id','updated_at','published_at'])->chunk(500, function ($items) use ($urls) {
            foreach ($items as $item) $urls->push(['loc' => route('vehicles.show', $item), 'lastmod' => optional($item->updated_at ?? $item->published_at)->toAtomString(), 'changefreq' => 'weekly', 'priority' => '0.7']);
        });
        RealEstateListing::query()->published()->select(['id','updated_at','published_at'])->chunk(500, function ($items) use ($urls) {
            foreach ($items as $item) $urls->push(['loc' => route('real-estate.show', $item), 'lastmod' => optional($item->updated_at ?? $item->published_at)->toAtomString(), 'changefreq' => 'weekly', 'priority' => '0.7']);
        });

        $xml = view('sitemap', compact('urls'))->render();
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=900']);
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nDisallow: /login\nSitemap: ".route('sitemap')."\n";
        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }
}
