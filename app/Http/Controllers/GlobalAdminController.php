<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Models\AiAuditEvent;
use App\Models\AiExecution;
use App\Models\AiProvider;
use App\Models\NewsArticle;
use App\Models\NewsNarration;
use App\Models\Site;
use App\Models\SystemAuditLog;
use App\Services\Database\DatabaseHealthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GlobalAdminController extends Controller
{
    public function __invoke(DatabaseHealthService $databaseHealth): View
    {
        abort_unless(auth()->user()?->hasRole('Super Admin'), 403);
        $sites = Site::withCount(['domains', 'users'])->orderByDesc('is_default')->orderBy('name')->get();
        $contentBySite = NewsArticle::forAllSites()->selectRaw('site_id, count(*) as total, sum(case when status = ? then 1 else 0 end) as published', ['published'])
            ->groupBy('site_id')->get()->keyBy('site_id');
        $adsBySite = AdCampaign::forAllSites()->selectRaw('site_id, count(*) as total')->groupBy('site_id')->pluck('total', 'site_id');
        $health = $databaseHealth->audit();

        return view('admin.global.index', [
            'sites' => $sites, 'contentBySite' => $contentBySite, 'adsBySite' => $adsBySite,
            'totals' => [
                'sites' => $sites->count(), 'active_sites' => $sites->where('is_active', true)->count(),
                'users' => DB::table('site_user')->distinct()->count('user_id'),
                'news' => NewsArticle::forAllSites()->count(), 'ads' => AdCampaign::forAllSites()->count(),
            ],
            'costs' => [
                'ai_executions' => AiExecution::count(),
                'ai_cost' => ((int) AiExecution::sum('estimated_cost_micros')) / 1_000_000,
                'audio_cost' => (float) NewsNarration::query()->selectRaw('coalesce(sum(coalesce(actual_cost, estimated_cost)),0) as total')->value('total'),
            ],
            'health' => [
                'database' => $health,
                'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
                'failed_webhooks' => Schema::hasTable('webhook_deliveries') ? DB::table('webhook_deliveries')->where('status', 'failed')->count() : 0,
                'providers' => AiProvider::select(['name', 'health_status', 'consecutive_failures', 'last_checked_at'])->orderBy('priority')->get(),
            ],
            'auditLogs' => SystemAuditLog::with('user')->latest('created_at')->limit(30)->get(),
            'aiAuditLogs' => AiAuditEvent::with(['user', 'provider'])->latest()->limit(20)->get(),
            'recentNews' => NewsArticle::forAllSites()->with('site')->latest()->limit(10)->get(),
            'recentAds' => AdCampaign::forAllSites()->with('site')->latest()->limit(10)->get(),
        ]);
    }
}
