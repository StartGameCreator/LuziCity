<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Setting;
use App\Services\PaywallService;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function show(NewsArticle $news, PaywallService $paywall): View
    {
        abort_unless($news->status === 'published', 404);
        abort_unless($news->sponsoredIsVisible(), 404);
        if ($news->is_sponsored) {
            $news->increment('sponsored_views_count');
        }
        $paywallState = $paywall->evaluate($news, auth()->user());

        $shareImage = $news->cover_image_path
            ?: ($news->carousel_image_path ?: data_get(Setting::siteIdentity(), 'share_image'));

        return view('news.show', [
            'article' => $news->load(['category', 'author', 'sponsor', 'site', 'originSite']),
            'showAds' => ! auth()->user()?->hasAdFreeAccess() && $news->allow_ads,
            'paywall' => $paywallState,
            'meta' => [
                'title' => $news->title,
                'description' => $news->excerpt ?: str($news->body)->stripTags()->limit(180)->toString(),
                'image' => $shareImage,
                'type' => 'article',
                'url' => route('news.show', $news->slug),
                'analytics_news_id' => $news->id,
            ],
        ]);
    }
}
