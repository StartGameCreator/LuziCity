<?php
namespace App\Http\Controllers;use App\Services\RadioDashboardService;use Illuminate\View\View;
class AdminRadioDashboardController extends Controller{public function __invoke(RadioDashboardService $service):View{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin']),403);return view('admin.radio-dashboard.index',$service->data());}}
