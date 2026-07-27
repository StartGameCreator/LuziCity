<?php

use App\Http\Controllers\AdminQueueMonitorController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/sistema/filas')->name('admin.queue-monitor.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminQueueMonitorController::class, 'index'])->name('index');
        Route::post('/falhas/reprocessar-todas', [AdminQueueMonitorController::class, 'retryAll'])->name('retry-all');
        Route::post('/falhas/limpar-antigas', [AdminQueueMonitorController::class, 'prune'])->name('prune');
        Route::post('/falhas/{uuid}/reprocessar', [AdminQueueMonitorController::class, 'retry'])->name('retry');
        Route::delete('/falhas/{uuid}', [AdminQueueMonitorController::class, 'forget'])->name('forget');
    });
