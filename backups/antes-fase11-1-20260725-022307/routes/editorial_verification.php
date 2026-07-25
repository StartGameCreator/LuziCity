<?php
use App\Http\Controllers\AdminEditorialVerificationController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/redacao/verificacao')->name('admin.editorial-verification.')->middleware(['auth','roles:Super Admin,Admin,Jornalista,Colunista'])->group(function(){Route::get('/pautas/{pitch}',[AdminEditorialVerificationController::class,'index'])->name('index');Route::post('/afirmacoes/{claim}',[AdminEditorialVerificationController::class,'review'])->name('review');});
