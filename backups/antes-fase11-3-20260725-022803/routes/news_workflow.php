<?php
use App\Http\Controllers\AdminNewsWorkflowController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/news')->name('admin.news.workflow.')->middleware(['auth','roles:Super Admin,Admin,Jornalista,Colunista'])->group(function(){Route::get('/{news}/fluxo',[AdminNewsWorkflowController::class,'show'])->name('show');Route::post('/{news}/fluxo',[AdminNewsWorkflowController::class,'transition'])->name('transition');});
