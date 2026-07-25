<?php
use App\Http\Controllers\AdminAiAgentController;use Illuminate\Support\Facades\Route;
Route::prefix('admin/redacao/agentes')->name('admin.ai-agents.')->middleware(['auth','roles:Super Admin,Admin,Jornalista,Colunista'])->group(function(){Route::get('/',[AdminAiAgentController::class,'index'])->name('index');Route::post('/pautas/{pitch}/etapas',[AdminAiAgentController::class,'record'])->name('record');Route::patch('/etapas/{step}/decidir',[AdminAiAgentController::class,'decide'])->name('decide');});
