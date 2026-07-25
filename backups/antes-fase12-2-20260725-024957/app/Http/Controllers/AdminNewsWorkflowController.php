<?php
namespace App\Http\Controllers;
use App\Models\NewsArticle;use App\Services\NewsEditorialWorkflowService;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\Validation\Rule;use Illuminate\View\View;
class AdminNewsWorkflowController extends Controller{
 public function show(NewsArticle $news):View{$news->load(['versions.creator','editorialReviews.user','publisher']);return view('admin.news.workflow',compact('news'));}
 public function transition(Request $r,NewsArticle $news,NewsEditorialWorkflowService $flow):RedirectResponse{$d=$r->validate(['action'=>['required',Rule::in(array_keys(NewsEditorialWorkflowService::ACTIONS))],'note'=>['nullable','string','max:5000'],'scheduled_for'=>['nullable','date']]);$flow->transition($news,$r->user(),$d['action'],$d['note']??null,$d['scheduled_for']??null);return back()->with('status','Ação editorial registrada.');}
}
