<?php
namespace App\Http\Controllers;use App\Models\NewsArticle;use App\Models\VideoScript;use App\Services\VideoScriptGenerator;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\View\View;
class AdminVideoScriptController extends Controller{
 public function index():View{$this->guard();return view('admin.video-scripts.index',['articles'=>NewsArticle::latest()->limit(50)->get(),'scripts'=>VideoScript::with(['article','creator','reviewer'])->latest()->paginate(30)]);}
 public function store(Request $r,VideoScriptGenerator $generator):RedirectResponse{$this->guard();$d=$r->validate(['news_article_id'=>'required|exists:news_articles,id','target_duration_seconds'=>'required|integer|min:30|max:600']);$generator->generate(NewsArticle::findOrFail($d['news_article_id']),$d['target_duration_seconds'],$r->user()->id);return back()->with('status','Roteiro gerado para revisão humana.');}
 public function review(Request $r,VideoScript $script):RedirectResponse{$this->guard();$d=$r->validate(['decision'=>'required|in:approved,rejected','teleprompter_text'=>'required|string|max:20000','editorial_notes'=>'nullable|string|max:5000']);$script->update(['status'=>$d['decision'],'teleprompter_text'=>$d['teleprompter_text'],'editorial_notes'=>$d['editorial_notes']??null,'reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]);return back()->with('status','Revisão registrada.');}
 public function teleprompter(VideoScript $script):View{$this->guard();return view('admin.video-scripts.teleprompter',compact('script'));}
 private function guard():void{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin','Jornalista']),403);}
}
