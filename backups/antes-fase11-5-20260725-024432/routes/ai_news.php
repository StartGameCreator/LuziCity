<?php

use App\Http\Controllers\AdminAiNewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/noticias/ia')
    ->name('admin.news.ai.')
    ->middleware(['auth', 'roles:Super Admin,Admin,Jornalista,Colunista'])
    ->group(function (): void {
        Route::get('/', [AdminAiNewsController::class, 'create'])->name('create');
        Route::post('/gerar', [AdminAiNewsController::class, 'generate'])
            ->middleware('throttle:10,1')
            ->name('generate');
        Route::put('/memoria-editorial', [AdminAiNewsController::class, 'updateProfile'])
            ->name('profile.update');
    });
