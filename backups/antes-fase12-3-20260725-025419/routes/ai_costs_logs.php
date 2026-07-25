<?php

use App\Http\Controllers\AdminAiCostController;
use App\Http\Controllers\AdminAiLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/ia')->name('admin.ai.')->middleware(['auth','roles:Super Admin,Admin'])->group(function () {
    Route::get('/custos', AdminAiCostController::class)->name('costs.index');
    Route::get('/logs', [AdminAiLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{execution}', [AdminAiLogController::class, 'show'])->name('logs.show');
});
