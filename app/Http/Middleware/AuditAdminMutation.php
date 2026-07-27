<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\SystemAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditAdminMutation
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $status = $exception instanceof ValidationException
                ? 422
                : (method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500);
            $this->record($request, $status);
            throw $exception;
        }
        $this->record($request, $response->getStatusCode());

        return $response;
    }

    private function record(Request $request, int $status): void
    {
        if (! $request->is('admin/*') || $request->isMethodSafe() || ! Schema::hasTable('system_audit_logs')) {
            return;
        }
        SystemAuditLog::create([
            'user_id' => $request->user()?->id,
            'event' => 'admin.'.($request->route()?->getName() ?: 'mutation'),
            'new_values' => [
                'site_id' => Site::current()?->id, 'method' => $request->method(),
                'path' => $request->path(), 'response_status' => $status,
            ],
            'ip_address' => $request->ip(), 'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'request_id' => $request->header('X-Request-ID') ?: (string) Str::uuid(),
            'created_at' => now(),
        ]);
    }
}
