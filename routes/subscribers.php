<?php

use App\Http\Controllers\AdminSubscriberController;
use App\Http\Controllers\SubscriberPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('minha-assinatura')->name('subscriber.')->group(function (): void {
    Route::get('/', [SubscriberPortalController::class, 'show'])->name('show');
    Route::post('/cancelar', [SubscriberPortalController::class, 'cancel'])->name('cancel');
});

Route::get('/admin/assinaturas/assinantes', [AdminSubscriberController::class, 'index'])
    ->middleware(['auth', 'roles:Super Admin,Admin'])->name('admin.subscribers.index');
