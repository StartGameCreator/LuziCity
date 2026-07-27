<?php

use App\Http\Controllers\AdminSponsoredContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/comercial/conteudo-patrocinado')->name('admin.sponsored-content.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminSponsoredContentController::class, 'index'])->name('index');
        Route::post('/{article}/aprovar', [AdminSponsoredContentController::class, 'approve'])->name('approve');
        Route::post('/{article}/revogar', [AdminSponsoredContentController::class, 'revoke'])->name('revoke');
    });
