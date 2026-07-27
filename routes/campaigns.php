<?php

use App\Http\Controllers\AdCampaignTrackingController;
use App\Http\Controllers\AdminAdCampaignController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/comercial/campanhas')->name('admin.campaigns.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])->group(function (): void {
        Route::get('/', [AdminAdCampaignController::class, 'index'])->name('index');
        Route::get('/nova', [AdminAdCampaignController::class, 'create'])->name('create');
        Route::post('/', [AdminAdCampaignController::class, 'store'])->name('store');
        Route::get('/{campaign}/editar', [AdminAdCampaignController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [AdminAdCampaignController::class, 'update'])->name('update');
        Route::post('/{campaign}/aprovar', [AdminAdCampaignController::class, 'approve'])->name('approve');
    });

Route::get('/publicidade/{campaign}/impressao.gif', [AdCampaignTrackingController::class, 'impression'])
    ->middleware('throttle:120,1')->name('campaigns.impression');
Route::get('/publicidade/{campaign}/clique', [AdCampaignTrackingController::class, 'click'])
    ->middleware('throttle:60,1')->name('campaigns.click');
