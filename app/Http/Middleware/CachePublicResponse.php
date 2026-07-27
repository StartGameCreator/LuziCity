<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicResponse
{
    public function handle(Request $request, Closure $next, int $seconds = 60): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->user() || ! $response->isSuccessful()) {
            return $response;
        }

        $etag = '"'.hash('sha256', (string) $response->getContent()).'"';
        $response->headers->set('ETag', $etag);
        $response->headers->set('Vary', 'Accept, Accept-Encoding');

        if ($request->is('api/*')) {
            $response->headers->set('Cache-Control', "public, max-age={$seconds}, s-maxage={$seconds}, stale-while-revalidate=30");
        } else {
            // HTML permanece privado porque pode conter token CSRF de sessao.
            $response->headers->set('Cache-Control', "private, max-age={$seconds}, must-revalidate");
        }

        if ($request->headers->get('If-None-Match') === $etag) {
            $response->setNotModified();
        }

        return $response;
    }
}
