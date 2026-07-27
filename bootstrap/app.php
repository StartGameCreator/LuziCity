<?php

use App\Http\Middleware\AuditAdminMutation;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\CachePublicResponse;
use App\Http\Middleware\EnsureApiTokenAbility;
use App\Http\Middleware\EnsureUserHasAnyRole;
use App\Http\Middleware\RateLimitAdminMutations;
use App\Http\Middleware\RecordRequestTelemetry;
use App\Http\Middleware\ResolveCurrentSite;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ValidateUploadedFiles;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', ResolveCurrentSite::class);
        $middleware->appendToGroup('web', ValidateUploadedFiles::class);
        $middleware->appendToGroup('web', RateLimitAdminMutations::class);
        $middleware->appendToGroup('web', AuditAdminMutation::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->appendToGroup('web', RecordRequestTelemetry::class);
        $middleware->appendToGroup('api', ResolveCurrentSite::class);
        $middleware->appendToGroup('api', SecurityHeaders::class);
        $middleware->appendToGroup('api', RecordRequestTelemetry::class);
        $middleware->encryptCookies(except: [
            'luzicity_analytics_consent',
        ]);
        $middleware->validateCsrfTokens(except: [
            'pagamentos/webhook/mercado-pago',
        ]);
        $middleware->alias([
            'roles' => EnsureUserHasAnyRole::class,
            'api.token' => AuthenticateApiToken::class,
            'api.ability' => EnsureApiTokenAbility::class,
            'cache.public' => CachePublicResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
