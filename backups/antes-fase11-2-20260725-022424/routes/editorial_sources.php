<?php
use App\Http\Controllers\AdminEditorialSourceController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/redacao/fontes')->name('admin.editorial-sources.')->middleware(['auth','roles:Super Admin,Admin,Jornalista,Colunista'])->group(function(){Route::post('/pautas/{pitch}',[AdminEditorialSourceController::class,'store'])->name('store');Route::post('/{source}/capturar',[AdminEditorialSourceController::class,'fetch'])->middleware('throttle:10,1')->name('fetch');Route::post('/{source}/afirmacoes',[AdminEditorialSourceController::class,'claim'])->name('claims.store');});
