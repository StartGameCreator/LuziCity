<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class DeployCheck extends Command
{
    protected $signature = 'luzicity:deploy-check {--environment= : Ambiente esperado}';

    protected $description = 'Valida dependencias e configuracoes antes do deploy';

    public function handle(): int
    {
        $expected = $this->option('environment');
        $checks = [
            'Ambiente' => ! $expected || app()->environment($expected),
            'APP_KEY' => filled(config('app.key')),
            'APP_DEBUG desativado' => ! config('app.debug'),
            'APP_URL absoluto' => filter_var(config('app.url'), FILTER_VALIDATE_URL) !== false,
            'Storage gravavel' => is_writable(storage_path()),
            'Cache gravavel' => is_writable(storage_path('framework/cache')),
            'Banco conectado' => $this->can(fn () => DB::connection()->getPdo()),
            'Cache acessivel' => $this->can(fn () => cache()->put('deploy:check', true, 30)),
            'Fila acessivel' => $this->can(fn () => Queue::connection()->size()),
            'Build frontend' => file_exists(public_path('build/manifest.json')),
        ];

        foreach ($checks as $label => $ok) {
            $this->line(($ok ? '<info>OK</info>  ' : '<error>ERRO</error> ').$label);
        }

        if (in_array(false, $checks, true)) {
            return self::FAILURE;
        }

        Artisan::call('migrate:status');
        $this->info('Preflight concluido. Estado das migracoes consultado.');

        return self::SUCCESS;
    }

    private function can(callable $callback): bool
    {
        try {
            $callback();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
