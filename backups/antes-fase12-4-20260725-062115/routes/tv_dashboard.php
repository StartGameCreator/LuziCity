<?php
use App\Http\Controllers\AdminTvDashboardController;use Illuminate\Support\Facades\Route;
Route::get('/admin/tv-central',AdminTvDashboardController::class)->middleware(['auth','roles:Super Admin,Admin'])->name('admin.tv-dashboard');
