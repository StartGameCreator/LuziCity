<?php
use App\Http\Controllers\AdminEditorialRoomController;use Illuminate\Support\Facades\Route;
Route::get('/admin/redacao',AdminEditorialRoomController::class)->middleware(['auth','roles:Super Admin,Admin,Jornalista,Colunista'])->name('admin.editorial-room.dashboard');
