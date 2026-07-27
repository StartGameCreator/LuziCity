<?php

use App\Http\Controllers\AnalyticsPrivacyController;
use Illuminate\Support\Facades\Route;

Route::get('/privacidade/analytics', [AnalyticsPrivacyController::class, 'show'])->name('privacy.analytics');
Route::post('/privacidade/analytics/consentimento', [AnalyticsPrivacyController::class, 'consent'])->name('privacy.analytics.consent');
Route::post('/privacidade/analytics/opt-out', [AnalyticsPrivacyController::class, 'optOut'])->name('privacy.analytics.opt-out');
