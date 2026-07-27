<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordRequestTelemetry
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $requestId = $this->requestId($request);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->record($request, $requestId, 500, $startedAt, $exception);
            throw $exception;
        }

        $response->headers->set('X-Request-ID', $requestId);
        $this->record($request, $requestId, $response->getStatusCode(), $startedAt);

        return $response;
    }

    private function record(Request $request, string $requestId, int $status, int $startedAt, ?Throwable $exception = null): void
    {
        $durationMs = max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000));
        $context = [
            'event' => 'http.request',
            'request_id' => $requestId,
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'path' => Str::limit($request->path(), 255, ''),
            'status' => $status,
            'duration_ms' => $durationMs,
            'memory_bytes' => memory_get_peak_usage(true),
            'is_api' => $request->is('api/*'),
            'user_id' => $request->user()?->id,
            'exception' => $exception?->getMessage(),
        ];

        Log::log($status >= 500 ? 'error' : ($durationMs >= config('observability.slow_request_ms') ? 'warning' : 'info'), 'http.request', $context);

        try {
            if (! config('observability.metrics_enabled')
                || random_int(1, 100) > config('observability.sample_rate')
                || ! Schema::hasTable('request_metrics')) {
                return;
            }

            DB::table('request_metrics')->insert([
                'request_id' => $requestId,
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
                'path' => $context['path'],
                'status' => $status,
                'duration_ms' => $durationMs,
                'memory_bytes' => $context['memory_bytes'],
                'is_api' => $context['is_api'],
                'occurred_at' => now(),
            ]);
        } catch (Throwable) {
            // Telemetria nunca deve interromper a requisicao.
        }
    }

    private function requestId(Request $request): string
    {
        $provided = $request->header('X-Request-ID');

        return is_string($provided) && Str::isUuid($provided) ? $provided : (string) Str::uuid();
    }
}
