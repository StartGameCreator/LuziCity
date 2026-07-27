<?php

namespace App\Services\AzuraCast;

use App\Exceptions\AzuraCastException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AzuraCastClient
{
    private const CIRCUIT_KEY = 'azuracast:circuit';

    public function enabled(): bool
    {
        return (bool) config('services.azuracast.enabled');
    }

    public function hasApiKey(): bool
    {
        return filled(config('services.azuracast.api_key'));
    }

    public function baseUrl(): string
    {
        $baseUrl = rtrim((string) config('services.azuracast.base_url'), '/');
        $parts = parse_url($baseUrl);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $isLoopback = in_array($host, ['127.0.0.1', 'localhost', '::1'], true);

        if (! in_array($scheme, ['http', 'https'], true) || ($scheme !== 'https' && ! $isLoopback)) {
            throw new AzuraCastException('A URL do AzuraCast deve usar HTTPS, exceto no ambiente local.');
        }

        return $baseUrl;
    }

    public function get(string $path, bool $authenticated = true): array
    {
        if (Cache::has(self::CIRCUIT_KEY)) {
            throw new AzuraCastException('AzuraCast temporariamente indisponível.');
        }

        try {
            $response = $this->request($authenticated)
                ->retry(2, 250, throw: false)
                ->get(ltrim($path, '/'));

            return $this->decode($response);
        } catch (AzuraCastException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->openCircuit($exception);
            throw new AzuraCastException('Não foi possível conectar ao AzuraCast.');
        }
    }

    public function post(string $path): array
    {
        try {
            return $this->decode($this->request(true)->post(ltrim($path, '/')));
        } catch (AzuraCastException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->openCircuit($exception);
            throw new AzuraCastException('O comando não pôde ser enviado ao AzuraCast.');
        }
    }

    private function request(bool $authenticated): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl().'/api')
            ->acceptJson()
            ->asJson()
            ->timeout(max(1, (int) config('services.azuracast.timeout', 10)))
            ->withOptions(['verify' => (bool) config('services.azuracast.verify_ssl', true)]);

        if ($authenticated) {
            $token = (string) config('services.azuracast.api_key');
            if ($token === '') {
                throw new AzuraCastException('A API Key do AzuraCast ainda não foi configurada.', 401);
            }

            $request = $request->withToken($token);
        }

        return $request;
    }

    private function decode(Response $response): array
    {
        if ($response->successful()) {
            Cache::forget(self::CIRCUIT_KEY);

            return $response->json() ?? [];
        }

        $messages = [
            401 => 'API Key do AzuraCast inválida.',
            403 => 'A API Key não possui permissão para esta operação.',
            404 => 'Emissora ou recurso não encontrado no AzuraCast.',
            409 => 'O AzuraCast recusou o comando por conflito de estado.',
            422 => 'O AzuraCast recusou os dados enviados.',
            429 => 'Limite de requisições do AzuraCast atingido.',
        ];
        $status = $response->status();
        $message = $messages[$status] ?? ($status >= 500
            ? 'O serviço AzuraCast está temporariamente indisponível.'
            : 'Resposta inesperada do AzuraCast.');

        if ($status >= 500) {
            $this->openCircuit(new AzuraCastException($message, $status));
        }

        throw new AzuraCastException($message, $status);
    }

    private function openCircuit(Throwable $exception): void
    {
        Cache::put(self::CIRCUIT_KEY, true, now()->addSeconds(15));
        Log::warning('Falha na integração AzuraCast.', [
            'type' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
