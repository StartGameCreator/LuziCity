<?php

namespace App\Console\Commands;

use App\Jobs\CollectRssFeedJob;
use App\Models\RssFeed;
use Illuminate\Console\Command;

class CollectDueRssFeeds extends Command
{
    protected $signature = 'luzicity:rss-collect-due {--limit=12} {--feeds=50}';
    protected $description = 'Agenda a coleta dos feeds RSS vencidos';

    public function handle(): int
    {
        $limit = max(1, min(30, (int) $this->option('limit')));
        $feeds = max(1, min(200, (int) $this->option('feeds')));
        $due = RssFeed::query()->due()->orderBy('next_collection_at')->limit($feeds)->get();

        foreach ($due as $feed) {
            config('luzicity.rss_queue_enabled', true)
                ? CollectRssFeedJob::dispatch($feed->id, $limit)
                : CollectRssFeedJob::dispatchSync($feed->id, $limit);
        }

        $this->info($due->count().' fonte(s) RSS encaminhada(s) para coleta.');
        return self::SUCCESS;
    }
}
