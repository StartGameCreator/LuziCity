<?php
use App\Http\Controllers\AdminRssPrePitchController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/agencia/pre-pautas')->name('admin.rss-pre-pitches.')->middleware(['auth','roles:Super Admin,Admin,Jornalista'])->group(function(){Route::get('/',[AdminRssPrePitchController::class,'index'])->name('index');Route::post('/de-artigo/{article}',[AdminRssPrePitchController::class,'store'])->name('store');});
