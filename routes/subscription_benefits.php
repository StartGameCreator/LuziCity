<?php

use App\Http\Controllers\AdminSubscriptionBenefitController;
use App\Http\Controllers\SubscriberBenefitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('meus-beneficios')->name('subscriber.benefits.')->group(function (): void {
    Route::get('/', [SubscriberBenefitController::class, 'index'])->name('index');
    Route::post('/{benefit}/resgatar', [SubscriberBenefitController::class, 'redeem'])->name('redeem');
});
Route::prefix('admin/assinaturas/beneficios')->name('admin.subscription-benefits.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminSubscriptionBenefitController::class, 'index'])->name('index');
        Route::post('/', [AdminSubscriptionBenefitController::class, 'store'])->name('store');
        Route::put('/{benefit}', [AdminSubscriptionBenefitController::class, 'update'])->name('update');
    });
