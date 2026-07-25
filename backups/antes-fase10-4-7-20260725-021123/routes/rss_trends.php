<?php
use App\Http\Controllers\AdminRssTrendController;
use Illuminate\Support\Facades\Route;
Route::prefix('admin')->name('admin.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){
 Route::get('/tendencias-rss',[AdminRssTrendController::class,'index'])->name('rss-trends.index');
});
