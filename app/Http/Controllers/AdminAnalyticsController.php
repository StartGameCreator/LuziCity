<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsPageview;
use App\Models\SubscriptionBenefitRedemption;
use App\Models\SubscriptionHistory;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $days = in_array($request->integer('period'), [7, 30, 90], true) ? $request->integer('period') : 30;
        $from = today()->subDays($days - 1);
        $base = AnalyticsPageview::where('viewed_at', '>=', $from);
        $sessions = (clone $base)->distinct()->count('session_hash');
        $paid = SubscriptionPayment::where('status', 'paid')->where('paid_at', '>=', $from)->count();
        $subscriptions = SubscriptionHistory::where('event', 'created')->where('created_at', '>=', $from)->count();
        $benefits = SubscriptionBenefitRedemption::where('redeemed_at', '>=', $from)->count();
        $conversionTotal = $paid + $subscriptions + $benefits;
        $newsViews = (clone $base)->whereNotNull('news_article_id');
        $newsViewCount = (clone $newsViews)->count();
        $abandonments = (clone $newsViews)->where('reading_time_seconds', '<', 15)
            ->where('max_scroll_percent', '<', 25)->count();
        $hourExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%H', analytics_pageviews.viewed_at)"
            : 'hour(analytics_pageviews.viewed_at)';

        return view('admin.analytics.index', [
            'period' => $days,
            'metrics' => [
                'views' => (clone $base)->count(),
                'visitors' => $sessions,
                'identified_visitors' => (clone $base)->whereNotNull('user_id')->distinct()->count('user_id'),
                'read_time' => (int) (clone $base)->avg('reading_time_seconds'),
                'conversion_rate' => $sessions > 0 ? round(($conversionTotal / $sessions) * 100, 2) : 0,
                'editorial_read_time' => (int) (clone $newsViews)->avg('reading_time_seconds'),
                'completion_rate' => $newsViewCount > 0
                    ? round(((clone $newsViews)->where('max_scroll_percent', '>=', 75)->count() / $newsViewCount) * 100, 2) : 0,
                'abandonment_rate' => $newsViewCount > 0 ? round(($abandonments / $newsViewCount) * 100, 2) : 0,
                'shares' => (int) (clone $newsViews)->sum('share_count'),
            ],
            'pages' => (clone $base)->selectRaw('page_path, count(*) as views, count(distinct session_hash) as visitors')
                ->groupBy('page_path')->orderByDesc('views')->limit(20)->get(),
            'news' => (clone $base)->join('news_articles', 'news_articles.id', '=', 'analytics_pageviews.news_article_id')
                ->selectRaw('news_articles.id, news_articles.title, count(analytics_pageviews.id) as views, count(distinct analytics_pageviews.session_hash) as visitors, avg(analytics_pageviews.reading_time_seconds) as reading_time')
                ->groupBy('news_articles.id', 'news_articles.title')->orderByDesc('views')->limit(15)->get(),
            'authors' => (clone $base)->join('news_articles', 'news_articles.id', '=', 'analytics_pageviews.news_article_id')
                ->join('users', 'users.id', '=', 'news_articles.author_id')
                ->selectRaw('users.id, users.name, count(analytics_pageviews.id) as views, count(distinct analytics_pageviews.session_hash) as visitors')
                ->groupBy('users.id', 'users.name')->orderByDesc('views')->limit(15)->get(),
            'sources' => (clone $base)->selectRaw("coalesce(source, 'direto') as source_name, count(*) as views, count(distinct session_hash) as visitors")
                ->groupBy('source_name')->orderByDesc('views')->limit(10)->get(),
            'campaigns' => (clone $base)->whereNotNull('campaign')->selectRaw('campaign, count(*) as views, count(distinct session_hash) as visitors')
                ->groupBy('campaign')->orderByDesc('views')->limit(10)->get(),
            'conversions' => [
                'subscriptions' => $subscriptions, 'payments' => $paid,
                'benefits' => $benefits, 'total' => $conversionTotal,
            ],
            'daily' => (clone $base)->select([
                DB::raw('date(viewed_at) as day'), DB::raw('count(*) as views'),
                DB::raw('count(distinct session_hash) as visitors'),
            ])->groupBy('day')->orderBy('day')->get(),
            'categories' => (clone $newsViews)->join('news_articles', 'news_articles.id', '=', 'analytics_pageviews.news_article_id')
                ->leftJoin('categories', 'categories.id', '=', 'news_articles.category_id')
                ->selectRaw("coalesce(categories.name, 'Sem categoria') as category_name, count(*) as views, count(distinct analytics_pageviews.session_hash) as visitors, avg(analytics_pageviews.reading_time_seconds) as reading_time, avg(analytics_pageviews.max_scroll_percent) as scroll_depth, sum(analytics_pageviews.share_count) as shares")
                ->groupBy('category_name')->orderByDesc('views')->limit(15)->get(),
            'hours' => (clone $newsViews)->selectRaw("$hourExpression as view_hour, count(*) as views, count(distinct session_hash) as visitors, avg(reading_time_seconds) as reading_time")
                ->groupBy('view_hour')->orderBy('view_hour')->get(),
        ]);
    }
}
