<?php

return [
    'metrics_enabled' => filter_var(env('OBSERVABILITY_METRICS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'sample_rate' => min(100, max(0, (int) env('OBSERVABILITY_SAMPLE_RATE', 100))),
    'retention_days' => max(1, (int) env('OBSERVABILITY_RETENTION_DAYS', 30)),
    'slow_request_ms' => max(100, (int) env('OBSERVABILITY_SLOW_REQUEST_MS', 1500)),
    'error_rate_warning_percent' => min(100, max(1, (float) env('OBSERVABILITY_ERROR_RATE_WARNING', 5))),
    'failed_jobs_warning' => max(1, (int) env('OBSERVABILITY_FAILED_JOBS_WARNING', 1)),
];
