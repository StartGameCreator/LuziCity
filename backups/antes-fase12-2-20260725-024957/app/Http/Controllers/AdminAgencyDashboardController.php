<?php
namespace App\Http\Controllers;
use App\Models\AgencyApprovalAction;use App\Models\RssCollectionRun;use App\Models\RssFeed;use App\Models\RssImportedArticle;use App\Models\RssPrePitch;use App\Models\RssTrendAlert;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\View\View;
class AdminAgencyDashboardController extends Controller{
 public function index():View{$this->guard();return view('admin.agency-dashboard.index',['stats'=>['sources'=>RssFeed::count(),'healthy'=>RssFeed::where('is_active',true)->where('consecutive_failures',0)->count(),'failures'=>RssFeed::where('consecutive_failures','>',0)->count(),'pending'=>RssImportedArticle::where('collection_status','pending_review')->count(),'pre_pitches'=>RssPrePitch::where('status','pending_review')->count(),'alerts'=>RssTrendAlert::where('is_read',false)->count()],'feeds'=>RssFeed::latest('last_collected_at')->get(),'runs'=>RssCollectionRun::with('feed')->latest('started_at')->limit(20)->get(),'actions'=>AgencyApprovalAction::with('user')->latest()->limit(20)->get()]);}
 public function policy(Request $r,RssFeed $feed):RedirectResponse{$this->guard();$data=$r->validate(['source_policy'=>'required|in:review,trusted,blocked','max_items_per_run'=>'required|integer|min:1|max:30','require_human_review'=>'nullable|boolean']);$feed->update($data+['require_human_review'=>$r->boolean('require_human_review')]);return back()->with('status','Política da fonte atualizada.');}
 private function guard():void{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin']),403);}
}
