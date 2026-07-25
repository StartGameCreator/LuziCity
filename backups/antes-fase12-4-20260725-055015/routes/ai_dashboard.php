<?php

use App\Http\Controllers\AdminAiDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.ai.')
    ->middleware(['auth', 'roles:Super Admin,Admin,Jornalista'])
    ->group(function (): void {
        Route::get('/ia', AdminAiDashboardController::class)->name('dashboard');
        Route::get('/ia/dashboard', AdminAiDashboardController::class)->name('dashboard.alias');
    });
