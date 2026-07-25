<?php
namespace App\Http\Controllers;use App\Models\TvChannel;use App\Services\TvBroadcastService;use Illuminate\View\View;
class TvController extends Controller{public function index(TvBroadcastService $service):View{$channels=TvChannel::where('is_active',true)->with(['broadcasts'=>fn($q)=>$q->whereIn('status',['scheduled','live'])->orderBy('starts_at')])->get();$live=$channels->mapWithKeys(fn($c)=>[$c->id=>$service->liveFor($c)])->filter();return view('tv.index',compact('channels','live','service'));}}
