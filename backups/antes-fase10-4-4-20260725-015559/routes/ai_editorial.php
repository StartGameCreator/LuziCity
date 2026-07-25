<?php

use App\Http\Controllers\AdminAiEditorialController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])
    ->group(function (): void {
        Route::get('/ia-editorial', [AdminAiEditorialController::class, 'index'])
            ->name('ai-editorial.index');

        Route::put('/ia-editorial/provedores/{provider}', [AdminAiEditorialController::class, 'updateProvider'])
            ->name('ai-editorial.providers.update');

        Route::put('/ia-editorial/prompts/{template}', [AdminAiEditorialController::class, 'updateTemplate'])
            ->name('ai-editorial.templates.update');
    });
