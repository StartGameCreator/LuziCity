<?php

use App\Http\Controllers\AdminPrintTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/impresso/templates')->name('admin.print-templates.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminPrintTemplateController::class, 'index'])->name('index');
        Route::get('/novo', [AdminPrintTemplateController::class, 'create'])->name('create');
        Route::post('/', [AdminPrintTemplateController::class, 'store'])->name('store');
        Route::get('/{printTemplate}/editar', [AdminPrintTemplateController::class, 'edit'])->name('edit');
        Route::put('/{printTemplate}', [AdminPrintTemplateController::class, 'update'])->name('update');
        Route::delete('/{printTemplate}', [AdminPrintTemplateController::class, 'destroy'])->name('destroy');
    });
