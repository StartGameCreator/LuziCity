<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

class LoadTest extends Command
{
    protected $signature = 'luzicity:load-test
        {--requests=100 : Total de requisicoes}
        {--path=/health/ready : Caminho local seguro}
        {--max-p95=500 : Latencia p95 maxima em ms}
        {--force : Permite execucao em producao}';

    protected $description = 'Executa benchmark local controlado para regressao de desempenho';

    public function handle(Kernel $kernel): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Teste de carga bloqueado em producao sem --force.');

            return self::FAILURE;
        }

        $requests = max(1, min(5000, (int) $this->option('requests')));
        $path = '/'.ltrim((string) $this->option('path'), '/');
        $maxP95 = max(1, (int) $this->option('max-p95'));
        $durations = [];
        $failures = 0;

        for ($index = 0; $index < $requests; $index++) {
            $startedAt = hrtime(true);
            $request = Request::create($path, 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
            $response = $kernel->handle($request);
            $durations[] = (hrtime(true) - $startedAt) / 1_000_000;
            $failures += $response->isSuccessful() ? 0 : 1;
            $kernel->terminate($request, $response);
        }

        sort($durations);
        $p95 = $durations[(int) ceil(count($durations) * 0.95) - 1];
        $average = array_sum($durations) / count($durations);
        $this->line(sprintf('Requisicoes: %d | falhas: %d | media: %.2f ms | p95: %.2f ms', $requests, $failures, $average, $p95));

        if ($failures > 0 || $p95 > $maxP95) {
            $this->error('Teste de carga fora dos limites definidos.');

            return self::FAILURE;
        }

        $this->info('Teste de carga aprovado.');

        return self::SUCCESS;
    }
}
