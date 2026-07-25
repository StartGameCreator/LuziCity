<?php
use App\Http\Controllers\AdminEditorialCalendarController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/redacao/calendario')->name('admin.editorial-calendar.')->middleware(['auth','roles:Super Admin,Admin,Jornalista,Colunista'])->group(function(){Route::get('/',[AdminEditorialCalendarController::class,'index'])->name('index');Route::post('/eventos',[AdminEditorialCalendarController::class,'store'])->name('store');Route::post('/sugestoes',[AdminEditorialCalendarController::class,'suggest'])->name('suggest');});
