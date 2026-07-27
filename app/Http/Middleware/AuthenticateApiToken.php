<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();
        $token = $plainTextToken
            ? ApiToken::query()->with('user')->where('token_hash', hash('sha256', $plainTextToken))->first()
            : null;

        if (! $token || $token->revoked_at || ($token->expires_at && $token->expires_at->isPast()) || ! $token->user?->is_active) {
            return response()->json(['message' => 'Token ausente, inválido, expirado ou revogado.'], 401);
        }
        if (Site::current() && ! $token->user->canAccessSite(Site::current())) {
            return response()->json(['message' => 'Token sem acesso ao site atual.'], 403);
        }

        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('api_token', $token);
        $token->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
