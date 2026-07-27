<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ObservabilityService
{
    public function summary(int $minutes = 60): array
    {
        if (! Schema::hasTable('request_metrics')) {
            return ['requests' => 0, 'errors' => 0, 'error_rate' => 0.0, 'average_ms' => 0, 'slow' => 0];
        }

        $query = DB::table('request_metrics')->where('occurred_at', '>=', now()->subMinutes($minutes));
        $requests = (clone $query)->count();
        $errors = (clone $query)->where('status', '>=', 500)->count();

        return [
            'requests' => $requests,
            'errors' => $errors,
            'error_rate' => $requests > 0 ? round(($errors / $requests) * 100, 2) : 0.0,
            'average_ms' => (int) round((float) ((clone $query)->avg('duration_ms') ?? 0)),
            'slow' => (clone $query)->where('duration_ms', '>=', config('observability.slow_request_ms'))->count(),
        ];
    }

    public function alerts(): array
    {
        $summary = $this->summary();
        $alerts = [];
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        if ($summary['error_rate'] >= config('observability.error_rate_warning_percent')) {
            $alerts[] = ['level' => 'error', 'message' => "Taxa de erros em {$summary['error_rate']}% na ultima hora."];
        }
        if ($summary['slow'] > 0) {
            $alerts[] = ['level' => 'warning', 'message' => "{$summary['slow']} requisicao(oes) lenta(s) na ultima hora."];
        }
        if ($failedJobs >= config('observability.failed_jobs_warning')) {
            $alerts[] = ['level' => 'error', 'message' => "{$failedJobs} job(s) aguardando no dead-letter."];
        }

        return $alerts;
    }
}
