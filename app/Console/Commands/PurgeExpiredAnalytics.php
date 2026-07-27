<?php

namespace App\Console\Commands;

use App\Models\AnalyticsPageview;
use Illuminate\Console\Command;

class PurgeExpiredAnalytics extends Command
{
    protected $signature = 'analytics:purge-expired';

    protected $description = 'Remove eventos de analytics além do prazo de retenção';

    public function handle(): int
    {
        $days = max(1, (int) config('analytics.retention_days'));
        $deleted = AnalyticsPageview::where('viewed_at', '<', now()->subDays($days))->delete();
        $this->info("Eventos removidos: {$deleted}");

        return self::SUCCESS;
    }
}
