<?php

use App\Http\Controllers\AdminPaywallController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/assinaturas/paywall')->name('admin.paywall.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminPaywallController::class, 'index'])->name('index');
        Route::put('/categorias/{category}', [AdminPaywallController::class, 'updateRule'])->name('categories.update');
    });
