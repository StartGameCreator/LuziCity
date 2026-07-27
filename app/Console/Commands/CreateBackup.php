<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Throwable;

class CreateBackup extends Command
{
    protected $signature = 'luzicity:backup {--verify : Verifica o arquivo e testa a restauracao}';

    protected $description = 'Cria backup do banco e storage da LuziCity';

    public function handle(BackupService $backups): int
    {
        try {
            $result = $backups->create();
            $this->info("Backup criado em {$result['disk']}:{$result['path']} ({$result['files']} arquivos).");

            if ($this->option('verify')) {
                $verification = $backups->verify($result['path']);
                $this->info("Restauracao verificada com sucesso ({$verification['files']} arquivos).");
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
