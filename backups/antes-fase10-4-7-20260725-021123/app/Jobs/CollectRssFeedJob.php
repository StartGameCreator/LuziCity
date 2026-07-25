<?php

namespace App\Jobs;

use App\Models\RssCollectionRun;
use App\Models\RssFeed;
use App\Services\RssImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class CollectRssFeedJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $uniqueFor = 300;

    public function __construct(public readonly int $feedId, public readonly int $limit = 12)
    {
        $this->onQueue((string) config('luzicity.rss_queue', 'rss'));
    }

    public function uniqueId(): string
    {
        return 'rss-feed-'.$this->feedId;
    }

    public function handle(RssImportService $service): void
    {
        $feed = RssFeed::query()->usable()->findOrFail($this->feedId);
        $run = RssCollectionRun::create([
            'rss_feed_id' => $feed->id,
            'job_uuid' => (string) Str::uuid(),
            'status' => 'running',
            'requested_limit' => $this->limit,
            'started_at' => now(),
        ]);

        try {
            $result = $service->importFeed($feed, $this->limit);
            $run->update([
                'status' => $result['failed'] > 0 ? 'failed' : 'completed',
                'created_count' => $result['created'],
                'duplicate_count' => $result['updated'],
                'failed_count' => $result['failed'],
                'message' => $result['message'],
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'failed_count' => 1, 'message' => Str::limit($exception->getMessage(), 1000, ''), 'finished_at' => now()]);
            throw $exception;
        }
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }
}
