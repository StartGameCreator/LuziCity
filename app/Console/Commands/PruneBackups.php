<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class PruneBackups extends Command
{
    protected $signature = 'luzicity:backup-prune {--days= : Sobrescreve a retencao configurada}';

    protected $description = 'Remove backups anteriores ao periodo de retencao';

    public function handle(BackupService $backups): int
    {
        $days = $this->option('days');
        $removed = $backups->prune($days === null ? null : max(1, (int) $days));
        $this->info("{$removed} backup(s) expirado(s) removido(s).");

        return self::SUCCESS;
    }
}
