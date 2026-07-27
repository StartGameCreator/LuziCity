<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAdminMutations
{
    public function __construct(private readonly RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin/*') || $request->isMethodSafe()) {
            return $next($request);
        }
        $key = 'admin-mutation:'.($request->user()?->id ?: $request->ip());
        if ($this->limiter->tooManyAttempts($key, 120)) {
            return response('Muitas alterações em pouco tempo.', 429, [
                'Retry-After' => $this->limiter->availableIn($key),
            ]);
        }
        $this->limiter->hit($key, 60);

        return $next($request);
    }
}
