<?php

use App\Http\Controllers\AzuraCastController;
use Illuminate\Support\Facades\Route;

Route::get('/radio/estado', [AzuraCastController::class, 'publicState'])
    ->middleware('throttle:60,1')
    ->name('radio.state');

Route::prefix('admin/radio/azuracast')
    ->name('admin.radio.azuracast.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])
    ->group(function (): void {
        Route::get('/health', [AzuraCastController::class, 'health'])
            ->middleware('throttle:30,1')
            ->name('health');
        Route::post('/test', [AzuraCastController::class, 'test'])
            ->middleware('throttle:10,1')
            ->name('test');
        Route::post('/station/{action}', [AzuraCastController::class, 'control'])
            ->whereIn('action', ['start', 'stop', 'restart'])
            ->middleware('throttle:6,1')
            ->name('control');
    });
