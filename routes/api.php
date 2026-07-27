<?php

use App\Http\Controllers\Api\V1\ApiDocumentationController;
use App\Http\Controllers\Api\V1\ApiTokenController;
use App\Http\Controllers\Api\V1\MobileController;
use App\Http\Controllers\Api\V1\PublicContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/docs', [ApiDocumentationController::class, 'index'])->name('docs.index');
    Route::get('/docs/openapi.yaml', [ApiDocumentationController::class, 'openapi'])->name('docs.openapi');
    Route::get('/docs/guide.md', [ApiDocumentationController::class, 'guide'])->name('docs.guide');
    Route::middleware(['throttle:api-public', 'cache.public:60'])->group(function (): void {
        Route::get('/news', [PublicContentController::class, 'news'])->name('news.index');
        Route::get('/categories', [PublicContentController::class, 'categories'])->name('categories.index');
        Route::get('/videos', [PublicContentController::class, 'videos'])->name('videos.index');
        Route::get('/podcasts', [PublicContentController::class, 'podcasts'])->name('podcasts.index');
        Route::get('/events', [PublicContentController::class, 'events'])->name('events.index');
    });
    Route::post('/auth/tokens', [ApiTokenController::class, 'store'])
        ->middleware('throttle:api-token-issue')->name('auth.tokens.store');
    Route::post('/mobile/auth/tokens', [ApiTokenController::class, 'store'])
        ->middleware('throttle:api-token-issue')->name('mobile.auth.tokens.store');
    Route::middleware(['api.token', 'throttle:api-authenticated'])->group(function (): void {
        Route::get('/auth/me', [ApiTokenController::class, 'show'])
            ->middleware('api.ability:profile:read')->name('auth.me');
        Route::delete('/auth/tokens/current', [ApiTokenController::class, 'destroy'])
            ->name('auth.tokens.destroy');
    });
    Route::prefix('mobile')->name('mobile.')->middleware(['api.token', 'throttle:api-authenticated'])->group(function (): void {
        Route::middleware('api.ability:mobile:read')->group(function (): void {
            Route::get('/feed', [MobileController::class, 'feed'])->name('feed');
            Route::get('/search', [MobileController::class, 'search'])->name('search');
            Route::get('/favorites', [MobileController::class, 'favorites'])->name('favorites.index');
            Route::get('/profile', [MobileController::class, 'profile'])->name('profile.show');
        });
        Route::middleware('api.ability:mobile:write')->group(function (): void {
            Route::post('/favorites/{news:slug}', [MobileController::class, 'favorite'])->name('favorites.store');
            Route::delete('/favorites/{news:slug}', [MobileController::class, 'unfavorite'])->name('favorites.destroy');
            Route::post('/notifications/devices', [MobileController::class, 'subscribe'])->name('notifications.store');
            Route::delete('/notifications/devices', [MobileController::class, 'unsubscribe'])->name('notifications.destroy');
            Route::patch('/profile', [MobileController::class, 'updateProfile'])->name('profile.update');
        });
    });
});
