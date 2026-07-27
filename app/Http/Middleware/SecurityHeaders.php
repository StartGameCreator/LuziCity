<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $viteOrigin = app()->environment('local')
            ? ' http://127.0.0.1:5173 ws://127.0.0.1:5173'
            : '';

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(self), geolocation=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; object-src 'none'; form-action 'self'; ".
            "frame-ancestors 'self'; script-src 'self' 'unsafe-inline' https://www.gstatic.com{$viteOrigin}; ".
            "style-src 'self' 'unsafe-inline'{$viteOrigin}; img-src 'self' data: https:; font-src 'self' data:; ".
            "media-src 'self' blob: https:; connect-src 'self' https://*.googleapis.com https://*.firebaseio.com{$viteOrigin}; ".
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com ".
            "https://www.facebook.com https://www.tiktok.com https://*.dlive.tv",
        );
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
