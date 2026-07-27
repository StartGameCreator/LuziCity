<?php

use App\Http\Controllers\AdminSubscriptionPlanController;
use App\Http\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/assinaturas/planos', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');

Route::prefix('admin/assinaturas/planos')->name('admin.subscription-plans.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminSubscriptionPlanController::class, 'index'])->name('index');
        Route::post('/', [AdminSubscriptionPlanController::class, 'store'])->name('store');
        Route::put('/{plan}', [AdminSubscriptionPlanController::class, 'update'])->name('update');
    });
