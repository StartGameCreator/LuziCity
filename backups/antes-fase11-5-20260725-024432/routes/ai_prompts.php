<?php

use App\Http\Controllers\AdminAiPromptController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/ia/prompts')->name('admin.ai.prompts.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminAiPromptController::class, 'index'])->name('index');
        Route::get('/novo', [AdminAiPromptController::class, 'create'])->name('create');
        Route::post('/', [AdminAiPromptController::class, 'store'])->name('store');
        Route::get('/{prompt}', [AdminAiPromptController::class, 'show'])->name('show');
        Route::get('/{prompt}/editar', [AdminAiPromptController::class, 'edit'])->name('edit');
        Route::put('/{prompt}', [AdminAiPromptController::class, 'update'])->name('update');
        Route::post('/{prompt}/duplicar', [AdminAiPromptController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{prompt}/alternar', [AdminAiPromptController::class, 'toggle'])->name('toggle');
        Route::match(['get', 'post'], '/{prompt}/testar', [AdminAiPromptController::class, 'test'])->name('test');
        Route::post('/{prompt}/versoes/{version}/restaurar', [AdminAiPromptController::class, 'restore'])->name('restore');
        Route::get('/{prompt}/comparar/versoes', [AdminAiPromptController::class, 'compare'])->name('compare');
    });
