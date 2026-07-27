<?php

return [
    'policy_version' => env('ANALYTICS_POLICY_VERSION', '1.0'),
    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 395),
];
