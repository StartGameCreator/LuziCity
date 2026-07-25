<?php
use App\Http\Controllers\AdminVideoClipController;use App\Http\Controllers\VideoClipController;use Illuminate\Support\Facades\Route;
Route::get('/shorts/{clip}',[VideoClipController::class,'show'])->name('video-clips.show');
Route::prefix('admin/tv/recortes')->name('admin.video-clips.')->middleware(['auth','roles:Super Admin,Admin,Jornalista'])->group(function(){Route::get('/',[AdminVideoClipController::class,'index'])->name('index');Route::post('/',[AdminVideoClipController::class,'store'])->name('store');Route::post('/{clip}/repetir',[AdminVideoClipController::class,'retry'])->name('retry');Route::patch('/{clip}/revisao',[AdminVideoClipController::class,'review'])->name('review');});
