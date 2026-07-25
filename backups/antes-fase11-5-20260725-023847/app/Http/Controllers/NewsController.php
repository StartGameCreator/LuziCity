<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Setting;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function show(NewsArticle $news): View
    {
        abort_unless($news->status === 'published', 404);

        $shareImage = $news->cover_image_path
            ?: ($news->carousel_image_path ?: data_get(Setting::siteIdentity(), 'share_image'));

        return view('news.show', [
            'article' => $news->load(['category', 'author']),
            'showAds' => ! auth()->user()?->hasAdFreeAccess() && $news->allow_ads,
            'meta' => [
                'title' => $news->title,
                'description' => $news->excerpt ?: str($news->body)->stripTags()->limit(180)->toString(),
                'image' => $shareImage,
                'type' => 'article',
                'url' => route('news.show', $news->slug),
            ],
        ]);
    }
}
