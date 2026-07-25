<?php
namespace App\Http\Controllers;use App\Services\TvDashboardService;use Illuminate\View\View;
class AdminTvDashboardController extends Controller{public function __invoke(TvDashboardService $service):View{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin']),403);return view('admin.tv-dashboard.index',$service->data());}}
