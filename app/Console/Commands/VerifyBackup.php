<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Throwable;

class VerifyBackup extends Command
{
    protected $signature = 'luzicity:backup-verify {path : Caminho do backup no disco configurado}';

    protected $description = 'Valida checksums e testa a restauracao segura do backup';

    public function handle(BackupService $backups): int
    {
        try {
            $result = $backups->verify($this->argument('path'));
            $this->info("Backup valido. Restauracao testada para {$result['files']} arquivos.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
