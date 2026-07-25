<?php
use App\Http\Controllers\AdminAgencyApprovalController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/agencia/aprovacao')->name('admin.agency-approval.')->middleware(['auth','roles:Super Admin,Admin,Jornalista'])->group(function(){Route::get('/',[AdminAgencyApprovalController::class,'index'])->name('index');Route::post('/item/{article}',[AdminAgencyApprovalController::class,'article'])->name('article');Route::post('/pre-pauta/{prePitch}',[AdminAgencyApprovalController::class,'prePitch'])->name('pre-pitch');});
