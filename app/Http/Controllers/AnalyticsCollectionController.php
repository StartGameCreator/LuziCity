<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsPageview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyticsCollectionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless(
            $request->cookie('luzicity_analytics_consent') === 'accepted'
            || $request->header('x-analytics-consent') === 'accepted',
            403
        );
        $data = $request->validate([
            'event_uuid' => ['required', 'uuid'], 'event' => ['required', 'in:page_view,engagement,share'],
            'news_article_id' => ['nullable', 'integer', 'exists:news_articles,id'],
            'page_path' => ['required', 'string', 'max:2048', 'regex:/^\//'], 'page_title' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'url', 'max:2048'], 'source' => ['nullable', 'string', 'max:120'],
            'medium' => ['nullable', 'string', 'max:120'], 'campaign' => ['nullable', 'string', 'max:180'],
            'content' => ['nullable', 'string', 'max:180'], 'term' => ['nullable', 'string', 'max:180'],
            'reading_time_seconds' => ['nullable', 'integer', 'between:0,86400'],
            'max_scroll_percent' => ['nullable', 'integer', 'between:0,100'],
        ]);
        $attributes = ['event_uuid' => $data['event_uuid']];
        $values = [
            'session_hash' => hash_hmac('sha256', $request->session()->getId(), config('app.key')),
            'user_id' => $request->user()?->id, 'news_article_id' => $data['news_article_id'] ?? null,
            'page_path' => $data['page_path'], 'page_title' => $data['page_title'] ?? null,
            'referrer_host' => filled($data['referrer'] ?? null) ? Str::limit(parse_url($data['referrer'], PHP_URL_HOST) ?: '', 255) : null,
            'source' => $data['source'] ?? null, 'medium' => $data['medium'] ?? null, 'campaign' => $data['campaign'] ?? null,
            'content' => $data['content'] ?? null, 'term' => $data['term'] ?? null, 'device_type' => $this->device($request),
            'reading_time_seconds' => $data['reading_time_seconds'] ?? 0, 'max_scroll_percent' => $data['max_scroll_percent'] ?? 0,
            'viewed_at' => now(), 'last_activity_at' => now(),
        ];
        $view = AnalyticsPageview::firstOrCreate($attributes, $values);
        if (! $view->wasRecentlyCreated && $data['event'] === 'engagement') {
            $view->update([
                'reading_time_seconds' => max($view->reading_time_seconds, $data['reading_time_seconds'] ?? 0),
                'max_scroll_percent' => max($view->max_scroll_percent, $data['max_scroll_percent'] ?? 0),
                'last_activity_at' => now(),
            ]);
        }
        if (! $view->wasRecentlyCreated && $data['event'] === 'share') {
            $view->increment('share_count');
            $view->update(['last_shared_at' => now()]);
        }

        return response()->json(['accepted' => true], $view->wasRecentlyCreated ? 201 : 200);
    }

    private function device(Request $request): string
    {
        $ua = Str::lower((string) $request->userAgent());
        if (Str::contains($ua, ['ipad', 'tablet'])) {
            return 'tablet';
        }
        if (Str::contains($ua, ['mobile', 'android', 'iphone'])) {
            return 'mobile';
        }

        return 'desktop';
    }
}
