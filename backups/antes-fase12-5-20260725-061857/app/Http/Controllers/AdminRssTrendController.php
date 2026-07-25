<?php
namespace App\Http\Controllers;
use App\Models\RssTrend;
use App\Models\RssTrendAlert;
use Illuminate\View\View;
class AdminRssTrendController extends Controller {
 public function index():View {
  abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin']),403);
  return view('admin.rss-trends.index',[
   'alerts'=>RssTrendAlert::with('trend')->where('is_read',false)->latest('detected_at')->limit(20)->get(),
   'trends'=>RssTrend::latest('window_ended_at')->orderByDesc('score')->limit(50)->get(),
  ]);
 }
}
