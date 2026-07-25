<?php
use App\Http\Controllers\AdminAdvertiserController;
use App\Http\Controllers\AdminAdvertiserRelationshipController;
use Illuminate\Support\Facades\Route;
Route::prefix('admin/comercial/anunciantes')->name('admin.advertisers.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){
 Route::get('/',[AdminAdvertiserController::class,'index'])->name('index');
 Route::get('/novo',[AdminAdvertiserController::class,'create'])->name('create');
 Route::post('/',[AdminAdvertiserController::class,'store'])->name('store');
 Route::get('/{advertiser}',[AdminAdvertiserController::class,'show'])->name('show');
 Route::get('/{advertiser}/editar',[AdminAdvertiserController::class,'edit'])->name('edit');
 Route::put('/{advertiser}',[AdminAdvertiserController::class,'update'])->name('update');
 Route::post('/{advertiser}/contatos',[AdminAdvertiserRelationshipController::class,'contact'])->name('contacts.store');
 Route::post('/{advertiser}/enderecos',[AdminAdvertiserRelationshipController::class,'address'])->name('addresses.store');
 Route::post('/{advertiser}/historico',[AdminAdvertiserRelationshipController::class,'history'])->name('histories.store');
 Route::post('/{advertiser}/documentos',[AdminAdvertiserRelationshipController::class,'document'])->name('documents.store');
});
