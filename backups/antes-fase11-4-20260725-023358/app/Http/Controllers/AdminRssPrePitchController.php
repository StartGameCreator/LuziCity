<?php
namespace App\Http\Controllers;use App\Models\RssImportedArticle;use App\Models\RssPrePitch;use App\Services\RssPrePitchService;use Illuminate\Http\RedirectResponse;use Illuminate\View\View;
class AdminRssPrePitchController extends Controller{
 public function index():View{$this->guard();return view('admin.rss-pre-pitches.index',['items'=>RssPrePitch::with(['article','generator'])->latest()->paginate(30)]);}
 public function store(RssImportedArticle $article,RssPrePitchService $service):RedirectResponse{$this->guard();$service->generate($article,auth()->id());return redirect()->route('admin.rss-pre-pitches.index')->with('status','Pré-pauta gerada para revisão humana.');}
 private function guard():void{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin','Jornalista']),403);}
}
