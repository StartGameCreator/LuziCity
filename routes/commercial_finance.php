<?php

use App\Http\Controllers\AdminCommercialFinanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/comercial/financeiro')->name('admin.commercial-finance.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminCommercialFinanceController::class, 'index'])->name('index');
        Route::post('/', [AdminCommercialFinanceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [AdminCommercialFinanceController::class, 'show'])->name('show');
        Route::post('/{invoice}/pagamentos', [AdminCommercialFinanceController::class, 'payment'])->name('payments.store');
        Route::post('/{invoice}/renovar', [AdminCommercialFinanceController::class, 'renew'])->name('renew');
        Route::post('/{invoice}/cancelar', [AdminCommercialFinanceController::class, 'cancel'])->name('cancel');
    });
