<?php
namespace App\Http\Controllers;
use App\Models\EditorialPitch;use App\Models\EditorialSourceClaim;use App\Services\EditorialFactCheckService;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\Validation\Rule;use Illuminate\View\View;
class AdminEditorialVerificationController extends Controller{
 public function index(EditorialPitch $pitch,EditorialFactCheckService $checker):View{$pitch->load(['sources.claims.source','sources.claims.reviews.reviewer']);$claims=$pitch->sources->flatMap->claims;return view('admin.editorial-verification.index',compact('pitch','claims','checker'));}
 public function review(Request $r,EditorialSourceClaim $claim,EditorialFactCheckService $checker):RedirectResponse{$d=$r->validate(['decision'=>['required',Rule::in(['confirmed','unconfirmed','conflicting','opinion','review_required'])],'rationale'=>['required','string','min:10','max:5000'],'evidence_excerpt'=>['nullable','string','max:3000']]);$claim->reviews()->create($d+['reviewed_by'=>$r->user()->id,'alerts'=>$checker->alerts($claim)]);$claim->update(['status'=>$d['decision'],'contradiction_note'=>$d['decision']==='conflicting'?$d['rationale']:$claim->contradiction_note]);return back()->with('status','Revisão humana registrada.');}
}
