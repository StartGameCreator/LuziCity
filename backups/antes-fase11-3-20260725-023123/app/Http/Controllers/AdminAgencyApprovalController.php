<?php
namespace App\Http\Controllers;use App\Models\NewsArticle;use App\Models\RssImportedArticle;use App\Models\RssPrePitch;use App\Services\AgencyApprovalService;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\View\View;
class AdminAgencyApprovalController extends Controller{
 public function index():View{$this->guard();return view('admin.agency-approval.index',['articles'=>RssImportedArticle::where('collection_status','pending_review')->latest('collected_at')->limit(30)->get(),'prePitches'=>RssPrePitch::with('article')->where('status','pending_review')->latest()->limit(30)->get(),'drafts'=>NewsArticle::where('status','draft')->latest()->limit(30)->get()]);}
 public function article(Request $r,RssImportedArticle $article,AgencyApprovalService $service):RedirectResponse{$this->guard();$action=$this->action($r);$action==='approve'?$service->approveArticle($article,$r->user()):$service->decide($article,$action,$r->user(),$r->input('note'));return back()->with('status','Decisão registrada.');}
 public function prePitch(Request $r,RssPrePitch $prePitch,AgencyApprovalService $service):RedirectResponse{$this->guard();$action=$this->action($r);$action==='approve'?$service->approvePrePitch($prePitch,$r->user()):$service->decide($prePitch,$action,$r->user(),$r->input('note'));return back()->with('status','Decisão registrada. Nenhuma notícia foi publicada.');}
 private function action(Request $r):string{return $r->validate(['action'=>'required|in:approve,reject,archive','note'=>'nullable|string|max:2000'])['action'];}
 private function guard():void{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin','Jornalista']),403);}
}
