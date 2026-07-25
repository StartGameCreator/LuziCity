<?php
use App\Http\Controllers\AdminAudioAdvertisingController;use App\Http\Controllers\AudioAdPlayController;use Illuminate\Support\Facades\Route;
Route::post('/radio/publicidade/{campaign}/reproducao',[AudioAdPlayController::class,'store'])->middleware('throttle:30,1')->name('audio-ads.play');
Route::prefix('admin/radio/publicidade')->name('admin.audio-ads.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){Route::get('/',[AdminAudioAdvertisingController::class,'index'])->name('index');Route::post('/spots',[AdminAudioAdvertisingController::class,'spot'])->name('spot');Route::post('/campanhas',[AdminAudioAdvertisingController::class,'campaign'])->name('campaign');});
