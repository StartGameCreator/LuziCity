<?php

namespace App\Jobs;

use App\Services\RssImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportRssFeedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(public readonly int $limit = 12)
    {
        $this->onQueue((string) config('luzicity.rss_queue', 'rss'));
    }

    public function handle(RssImportService $service): void
    {
        $service->importAll($this->limit);
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }
}
