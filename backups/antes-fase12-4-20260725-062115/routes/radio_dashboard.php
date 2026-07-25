<?php
use App\Http\Controllers\AdminRadioDashboardController;use Illuminate\Support\Facades\Route;
Route::get('/admin/radio-central',AdminRadioDashboardController::class)->middleware(['auth','roles:Super Admin,Admin'])->name('admin.radio-dashboard');
