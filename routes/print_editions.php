<?php

use App\Http\Controllers\AdminPrintEditionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/impresso/edicoes')->name('admin.print-editions.')
    ->middleware(['auth', 'roles:Super Admin,Admin,Jornalista'])->group(function (): void {
        Route::get('/', [AdminPrintEditionController::class, 'index'])->name('index');
        Route::get('/nova', [AdminPrintEditionController::class, 'create'])->name('create');
        Route::post('/', [AdminPrintEditionController::class, 'store'])->name('store');
        Route::get('/{printEdition}/editar', [AdminPrintEditionController::class, 'edit'])->name('edit');
        Route::get('/{printEdition}/pdf', [AdminPrintEditionController::class, 'pdf'])->name('pdf');
        Route::get('/{printEdition}/previa', [AdminPrintEditionController::class, 'preview'])->name('preview');
        Route::post('/{printEdition}/revisao', [AdminPrintEditionController::class, 'submitReview'])->name('review');
        Route::post('/{printEdition}/aprovar', [AdminPrintEditionController::class, 'approve'])->name('approve');
        Route::post('/{printEdition}/reabrir', [AdminPrintEditionController::class, 'reopen'])->name('reopen');
        Route::put('/{printEdition}', [AdminPrintEditionController::class, 'update'])->name('update');
        Route::delete('/{printEdition}', [AdminPrintEditionController::class, 'destroy'])->name('destroy');
    });
