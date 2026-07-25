<?php
use App\Http\Controllers\AdminAiProviderController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/ia/provedores')->name('admin.ai.providers.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){Route::get('/',[AdminAiProviderController::class,'index'])->name('index');Route::get('/{provider}/editar',[AdminAiProviderController::class,'edit'])->name('edit');Route::put('/{provider}',[AdminAiProviderController::class,'update'])->name('update');Route::post('/{provider}/testar',[AdminAiProviderController::class,'test'])->middleware('throttle:5,1')->name('test');});
