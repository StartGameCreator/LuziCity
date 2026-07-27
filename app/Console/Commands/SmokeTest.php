<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class SmokeTest extends Command
{
    protected $signature = 'luzicity:smoke {--base-url= : URL da instancia}';

    protected $description = 'Executa smoke tests HTTP em uma instancia implantada';

    public function handle(): int
    {
        $baseUrl = rtrim((string) ($this->option('base-url') ?: config('app.url')), '/');
        $checks = [
            '/up' => fn ($response) => $response->successful(),
            '/health/ready' => fn ($response) => $response->successful() && $response->json('status') === 'ready',
            '/api/v1/categories?per_page=1' => fn ($response) => $response->successful() && is_array($response->json('data')),
        ];

        try {
            foreach ($checks as $path => $assertion) {
                $response = Http::acceptJson()->connectTimeout(3)->timeout(10)->get($baseUrl.$path);
                if (! $assertion($response)) {
                    $this->error("FALHOU {$path}: HTTP {$response->status()}");

                    return self::FAILURE;
                }
                $this->info("OK {$path}");
            }
        } catch (Throwable $exception) {
            $this->error('Smoke test interrompido: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Smoke test concluido com sucesso.');

        return self::SUCCESS;
    }
}
