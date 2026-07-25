<?php
use App\Http\Controllers\AdminTvController;use App\Http\Controllers\TvController;use Illuminate\Support\Facades\Route;
Route::get('/tv',[TvController::class,'index'])->name('tv.index');
Route::prefix('admin/tv')->name('admin.tv.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){Route::get('/',[AdminTvController::class,'index'])->name('index');Route::post('/canais',[AdminTvController::class,'channel'])->name('channel');Route::post('/canais/{channel}/transmissoes',[AdminTvController::class,'broadcast'])->name('broadcast');});
