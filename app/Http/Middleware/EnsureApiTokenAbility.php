<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');
        if (! $token?->can($ability)) {
            return response()->json(['message' => "O token não possui o escopo {$ability}."], 403);
        }

        return $next($request);
    }
}
