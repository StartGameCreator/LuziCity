<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\EventResource;
use App\Http\Resources\Api\V1\NewsResource;
use App\Http\Resources\Api\V1\PodcastResource;
use App\Http\Resources\Api\V1\VideoResource;
use App\Models\Category;
use App\Models\EditorialCalendarEvent;
use App\Models\NewsArticle;
use App\Models\PodcastEpisode;
use App\Models\Video;
use App\Services\Cache\PublicContentCache;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class PublicContentController extends Controller
{
    public function news(Request $request): AnonymousResourceCollection
    {
        return NewsResource::collection($this->remember($request, 'news', fn () => NewsArticle::published()->with(['category', 'author', 'site', 'originSite'])
            ->latest('published_at')->paginate($this->perPage($request))
        ));
    }

    public function categories(Request $request): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->remember($request, 'categories', fn () => Category::query()->where('is_active', true)
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderBy('sort_order')->orderBy('name')->paginate($this->perPage($request))
        ));
    }

    public function videos(Request $request): AnonymousResourceCollection
    {
        return VideoResource::collection($this->remember($request, 'videos', fn () => Video::query()->where('is_published', true)->whereNotNull('published_at')
            ->where('published_at', '<=', now())->with(['category', 'series'])
            ->latest('published_at')->paginate($this->perPage($request))
        ));
    }

    public function podcasts(Request $request): AnonymousResourceCollection
    {
        return PodcastResource::collection($this->remember($request, 'podcasts', fn () => PodcastEpisode::query()->where('is_published', true)->whereNotNull('published_at')
            ->where('published_at', '<=', now())->whereHas('series', fn ($query) => $query->where('is_published', true))
            ->with('series')->latest('published_at')->paginate($this->perPage($request))
        ));
    }

    public function events(Request $request): AnonymousResourceCollection
    {
        return EventResource::collection($this->remember($request, 'events', fn () => EditorialCalendarEvent::query()->where('status', 'active')
            ->where('event_type', 'local_event')->where('is_ai_suggestion', false)
            ->orderBy('starts_at')->paginate($this->perPage($request))
        ));
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 20)));
    }

    private function remember(Request $request, string $resource, callable $query): mixed
    {
        $parameters = collect($request->query())->sortKeys()->all();
        $key = PublicContentCache::key($resource.':'.hash('sha256', serialize($parameters)));

        return Cache::remember(
            $key,
            now()->addSeconds((int) config('luzicity.public_cache_ttl_seconds', 60)),
            $query,
        );
    }
}
