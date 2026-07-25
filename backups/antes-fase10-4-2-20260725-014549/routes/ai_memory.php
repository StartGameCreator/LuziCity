<?php
use App\Http\Controllers\AdminAiEditorialMemoryController as C;use Illuminate\Support\Facades\Route;
Route::prefix('admin/ia/memoria')->name('admin.ai.memory.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){
 Route::get('/',[C::class,'index'])->name('index');Route::post('/perfis',[C::class,'storeProfile'])->name('profiles.store');Route::get('/perfis/{profile}',[C::class,'edit'])->name('edit');Route::put('/perfis/{profile}',[C::class,'update'])->name('update');
 Route::post('/perfis/{profile}/termos',[C::class,'storeTerm'])->name('terms.store');Route::put('/termos/{term}',[C::class,'updateTerm'])->name('terms.update');Route::delete('/termos/{term}',[C::class,'destroyTerm'])->name('terms.destroy');
 Route::post('/perfis/{profile}/regras',[C::class,'storeRule'])->name('rules.store');Route::put('/regras/{rule}',[C::class,'updateRule'])->name('rules.update');Route::delete('/regras/{rule}',[C::class,'destroyRule'])->name('rules.destroy');
});
