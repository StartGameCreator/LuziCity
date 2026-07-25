<?php
use App\Http\Controllers\AdminNewsNarrationController;use App\Http\Controllers\NewsNarrationController;use Illuminate\Support\Facades\Route;
Route::get('/noticias/{news}/audio',[NewsNarrationController::class,'show'])->name('news.audio');
Route::prefix('admin/audio/noticias')->name('admin.news-narrations.')->middleware(['auth','roles:Super Admin,Admin,Jornalista'])->group(function(){Route::get('/',[AdminNewsNarrationController::class,'index'])->name('index');Route::post('/vozes',[AdminNewsNarrationController::class,'voice'])->name('voice');Route::post('/gerar',[AdminNewsNarrationController::class,'store'])->name('store');Route::patch('/{narration}/revisao',[AdminNewsNarrationController::class,'review'])->name('review');});
