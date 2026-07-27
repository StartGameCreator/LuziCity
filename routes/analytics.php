<?php

use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AnalyticsCollectionController;
use Illuminate\Support\Facades\Route;

Route::post('/analytics/coletar', [AnalyticsCollectionController::class, 'store'])
    ->middleware('throttle:120,1')->name('analytics.collect');
Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index'])
    ->middleware(['auth', 'roles:Super Admin,Admin'])->name('admin.analytics.index');
