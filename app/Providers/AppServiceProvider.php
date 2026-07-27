<?php

namespace App\Providers;

use App\Contracts\RadioAutomationProvider;
use App\Models\Category;
use App\Models\EditorialCalendarEvent;
use App\Models\MediaBanner;
use App\Models\NewsArticle;
use App\Models\PodcastEpisode;
use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use App\Models\Setting;
use App\Models\Video;
use App\Observers\FlushHomeCacheObserver;
use App\Observers\OutgoingWebhookObserver;
use App\Services\AzuraCast\AzuraCastRadioProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RadioAutomationProvider::class, AzuraCastRadioProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $queueStartedAt = [];
        Queue::before(function (JobProcessing $event) use (&$queueStartedAt): void {
            $queueStartedAt[$event->job->uuid() ?: spl_object_id($event->job)] = microtime(true);
        });
        Queue::after(function (JobProcessed $event) use (&$queueStartedAt): void {
            $key = $event->job->uuid() ?: spl_object_id($event->job);
            $this->recordQueueActivity($event, 'processed', $queueStartedAt[$key] ?? null);
            unset($queueStartedAt[$key]);
        });
        Queue::failing(function (JobFailed $event) use (&$queueStartedAt): void {
            $key = $event->job->uuid() ?: spl_object_id($event->job);
            $this->recordQueueActivity($event, 'failed', $queueStartedAt[$key] ?? null, $event->exception);
            unset($queueStartedAt[$key]);
        });

        RateLimiter::for('api-public', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('api-token-issue', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('api-authenticated', fn (Request $request) => Limit::perMinute(120)
            ->by(hash('sha256', (string) $request->bearerToken())));

        foreach ([
            NewsArticle::class,
            MediaBanner::class,
            RssFeed::class,
            RssImportedArticle::class,
            Category::class,
            Setting::class,
            Video::class,
            PodcastEpisode::class,
            EditorialCalendarEvent::class,
        ] as $model) {
            $model::observe(FlushHomeCacheObserver::class);
        }
        NewsArticle::observe(OutgoingWebhookObserver::class);
        PodcastEpisode::observe(OutgoingWebhookObserver::class);
        EditorialCalendarEvent::observe(OutgoingWebhookObserver::class);
    }

    private function recordQueueActivity(
        JobProcessed|JobFailed $event,
        string $status,
        ?float $startedAt,
        ?Throwable $exception = null,
    ): void {
        try {
            if (! Schema::hasTable('queue_activity_logs')) {
                return;
            }
            DB::table('queue_activity_logs')->insert([
                'job_uuid' => $event->job->uuid(),
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue() ?: 'default',
                'job_name' => Str::limit($event->job->resolveName(), 255, ''),
                'status' => $status,
                'duration_ms' => $startedAt ? max(0, (int) round((microtime(true) - $startedAt) * 1000)) : null,
                'error' => $exception ? Str::limit($exception->getMessage(), 4000, '') : null,
                'occurred_at' => now(),
            ]);
        } catch (Throwable) {
            // Monitoramento nunca deve interromper o processamento do job.
        }
    }
}
