<?php
use App\Http\Controllers\AdminVideoScriptController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/tv/roteiros')->name('admin.video-scripts.')->middleware(['auth','roles:Super Admin,Admin,Jornalista'])->group(function(){Route::get('/',[AdminVideoScriptController::class,'index'])->name('index');Route::post('/',[AdminVideoScriptController::class,'store'])->name('store');Route::patch('/{script}/revisao',[AdminVideoScriptController::class,'review'])->name('review');Route::get('/{script}/teleprompter',[AdminVideoScriptController::class,'teleprompter'])->name('teleprompter');});
