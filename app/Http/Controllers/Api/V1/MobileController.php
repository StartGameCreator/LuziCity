<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NewsResource;
use App\Models\NewsArticle;
use App\Models\NewsFavorite;
use App\Models\PushSubscription;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MobileController extends Controller
{
    public function feed(Request $request): AnonymousResourceCollection
    {
        return NewsResource::collection(
            NewsArticle::published()->with(['category', 'author', 'site', 'originSite'])
                ->latest('published_at')->paginate($this->perPage($request))
        );
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $term = str_replace(['%', '_'], ['\\%', '\\_'], trim($data['q']));

        return NewsResource::collection(
            NewsArticle::published()->with(['category', 'author', 'site', 'originSite'])
                ->where(fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"))
                ->latest('published_at')->paginate($this->perPage($request))
        );
    }

    public function favorites(Request $request): AnonymousResourceCollection
    {
        $ids = NewsFavorite::where('user_id', $request->user()->id)->where('site_id', Site::current()?->id)
            ->latest()->pluck('news_article_id');

        return NewsResource::collection(
            NewsArticle::published()->with(['category', 'author', 'site', 'originSite'])
                ->whereIn('id', $ids)->orderByDesc('published_at')->paginate($this->perPage($request))
        );
    }

    public function favorite(Request $request, NewsArticle $news): JsonResponse
    {
        abort_unless($news->status === NewsArticle::STATUS_PUBLISHED, 404);
        $favorite = NewsFavorite::firstOrCreate([
            'user_id' => $request->user()->id, 'news_article_id' => $news->id, 'site_id' => Site::current()->id,
        ]);

        return response()->json(['saved' => true, 'id' => $favorite->id], $favorite->wasRecentlyCreated ? 201 : 200);
    }

    public function unfavorite(Request $request, NewsArticle $news): JsonResponse
    {
        NewsFavorite::where('user_id', $request->user()->id)->where('site_id', Site::current()?->id)
            ->where('news_article_id', $news->id)->delete();

        return response()->json(['removed' => true]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['required', 'in:android,ios,web'],
        ]);
        $subscription = PushSubscription::updateOrCreate(['token' => $data['token']], [
            'user_id' => $request->user()->id, 'site_id' => Site::current()?->id,
            'device_name' => $data['device_name'] ?? null, 'platform' => $data['platform'], 'last_seen_at' => now(),
        ]);

        return response()->json(['saved' => true, 'id' => $subscription->id]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:4096']]);
        PushSubscription::where('token', $data['token'])->where('user_id', $request->user()->id)
            ->where('site_id', Site::current()?->id)->delete();

        return response()->json(['removed' => true]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['data' => [
            'id' => $request->user()->id, 'name' => $request->user()->name, 'email' => $request->user()->email,
            'site' => ['id' => Site::current()?->id, 'name' => Site::current()?->name],
            'favorites_count' => NewsFavorite::where('user_id', $request->user()->id)->where('site_id', Site::current()?->id)->count(),
        ]]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $request->user()->update($data);

        return $this->profile($request);
    }

    private function perPage(Request $request): int
    {
        return min(50, max(1, $request->integer('per_page', 20)));
    }
}
