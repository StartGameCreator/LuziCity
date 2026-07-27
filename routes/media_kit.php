<?php

use App\Http\Controllers\AdminMediaKitController;
use Illuminate\Support\Facades\Route;

Route::get('/midia-kit.pdf', [AdminMediaKitController::class, 'mediaKitPdf'])->name('media-kit.pdf');

Route::prefix('admin/comercial/midia-kit')->name('admin.media-kit.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminMediaKitController::class, 'index'])->name('index');
        Route::post('/formatos', [AdminMediaKitController::class, 'storeFormat'])->name('formats.store');
        Route::put('/formatos/{format}', [AdminMediaKitController::class, 'updateFormat'])->name('formats.update');
        Route::post('/propostas', [AdminMediaKitController::class, 'storeProposal'])->name('proposals.store');
        Route::get('/propostas/{proposal}', [AdminMediaKitController::class, 'showProposal'])->name('proposals.show');
        Route::post('/propostas/{proposal}/aprovar', [AdminMediaKitController::class, 'approveProposal'])->name('proposals.approve');
        Route::get('/propostas/{proposal}/pdf', [AdminMediaKitController::class, 'proposalPdf'])->name('proposals.pdf');
    });
