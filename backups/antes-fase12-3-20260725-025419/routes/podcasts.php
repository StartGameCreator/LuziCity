<?php
use App\Http\Controllers\AdminPodcastController;use App\Http\Controllers\PodcastController;use Illuminate\Support\Facades\Route;
Route::get('/podcasts',[PodcastController::class,'index'])->name('podcasts.index');Route::get('/podcasts/{series}/feed.xml',[PodcastController::class,'feed'])->name('podcasts.feed');
Route::prefix('admin/podcasts')->name('admin.podcasts.')->middleware(['auth','roles:Super Admin,Admin'])->group(function(){Route::get('/',[AdminPodcastController::class,'index'])->name('index');Route::post('/series',[AdminPodcastController::class,'series'])->name('series');Route::post('/series/{series}/episodios',[AdminPodcastController::class,'episode'])->name('episode');});
