<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'path' => trim(env('BACKUP_PATH', 'backups'), '/'),
    'retention_days' => max(1, (int) env('BACKUP_RETENTION_DAYS', 30)),
    'include_storage' => filter_var(env('BACKUP_INCLUDE_STORAGE', true), FILTER_VALIDATE_BOOLEAN),
];
