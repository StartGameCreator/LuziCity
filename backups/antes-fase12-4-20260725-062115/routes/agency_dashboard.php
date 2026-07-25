<?php
use App\Http\Controllers\AdminAgencyDashboardController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/agencia')->name('admin.agency-dashboard.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){Route::get('/',[AdminAgencyDashboardController::class,'index'])->name('index');Route::put('/fontes/{feed}/politica',[AdminAgencyDashboardController::class,'policy'])->name('policy');});
