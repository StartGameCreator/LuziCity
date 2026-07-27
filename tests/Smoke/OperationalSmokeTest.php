<?php

namespace Tests\Smoke;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OperationalSmokeTest extends TestCase
{
    public function test_smoke_command_validates_deployed_endpoints(): void
    {
        Http::fake(function (Request $request) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/up' => Http::response(['status' => 'ok']),
                '/health/ready' => Http::response(['status' => 'ready']),
                '/api/v1/categories' => Http::response(['data' => []]),
                default => Http::response([], 404),
            };
        });

        $this->artisan('luzicity:smoke --base-url=https://staging.example.com')
            ->expectsOutputToContain('Smoke test concluido com sucesso.')
            ->assertSuccessful();
    }

    public function test_controlled_load_test_respects_latency_and_error_budget(): void
    {
        $this->artisan('luzicity:load-test --requests=5 --max-p95=5000')
            ->expectsOutputToContain('Teste de carga aprovado.')
            ->assertSuccessful();
    }
}
