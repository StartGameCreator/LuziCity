<?php
namespace App\Http\Controllers;
use App\Models\AiEditorialProfile;
use App\Models\AiEditorialRule;
use App\Models\AiEditorialTerm;
use App\Models\Category;
use App\Services\AI\AiEditorialMemoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class AdminAiEditorialMemoryController extends Controller {
 public function index():View{return view('admin.ai.memory.index',['profiles'=>AiEditorialProfile::with('category')->withCount(['rules','terms'])->orderByDesc('is_default')->get(),'categories'=>Category::orderBy('name')->get()]);}
 public function storeProfile(Request $r):RedirectResponse{$d=$this->profileData($r);if($r->boolean('is_default'))AiEditorialProfile::query()->update(['is_default'=>false]);$p=AiEditorialProfile::create($d);return redirect()->route('admin.ai.memory.edit',$p)->with('status','Perfil criado.');}
 public function edit(AiEditorialProfile $profile,AiEditorialMemoryService $memory):View{$profile->load(['category','rules'=>fn($q)=>$q->orderBy('priority'),'terms'=>fn($q)=>$q->orderBy('type')->orderBy('term')]);return view('admin.ai.memory.rules',compact('profile')+['compiled'=>$memory->compile($profile),'categories'=>Category::orderBy('name')->get()]);}
 public function update(Request $r,AiEditorialProfile $profile):RedirectResponse{$d=$this->profileData($r);if($r->boolean('is_default'))AiEditorialProfile::whereKeyNot($profile->id)->update(['is_default'=>false]);$profile->update($d);return back()->with('status','Memória editorial atualizada.');}
 public function storeTerm(Request $r,AiEditorialProfile $profile):RedirectResponse{$profile->terms()->create($r->validate(['term'=>['required','string','max:180'],'replacement'=>['nullable','string','max:180'],'type'=>['required',Rule::in(['preferred','forbidden','spelling'])],'context'=>['nullable','string','max:500']])+['active'=>$r->boolean('active')]);return back()->with('status','Termo adicionado.');}
 public function updateTerm(Request $r,AiEditorialTerm $term):RedirectResponse{$term->update($r->validate(['term'=>['required','string','max:180'],'replacement'=>['nullable','string','max:180'],'type'=>['required',Rule::in(['preferred','forbidden','spelling'])],'context'=>['nullable','string','max:500']])+['active'=>$r->boolean('active')]);return back()->with('status','Termo atualizado.');}
 public function destroyTerm(AiEditorialTerm $term):RedirectResponse{$term->delete();return back()->with('status','Termo removido.');}
 public function storeRule(Request $r,AiEditorialProfile $profile):RedirectResponse{$profile->rules()->create($this->ruleData($r));return back()->with('status','Regra adicionada.');}
 public function updateRule(Request $r,AiEditorialRule $rule):RedirectResponse{$rule->update($this->ruleData($r));return back()->with('status','Regra atualizada.');}
 public function destroyRule(AiEditorialRule $rule):RedirectResponse{$rule->delete();return back()->with('status','Regra removida.');}
 private function profileData(Request $r):array{return $r->validate(['name'=>['required','string','max:120'],'category_id'=>['nullable','exists:categories,id'],'language'=>['required','string','max:20'],'tone'=>['required','string','max:180'],'target_audience'=>['nullable','string','max:240'],'priority_region'=>['nullable','string','max:240'],'max_title_length'=>['required','integer','min:30','max:180'],'max_excerpt_length'=>['required','integer','min:80','max:600'],'editorial_rules'=>['nullable','string','max:10000']])+['is_default'=>$r->boolean('is_default'),'require_source_attribution'=>$r->boolean('require_source_attribution'),'avoid_sensationalism'=>$r->boolean('avoid_sensationalism'),'human_review_required'=>true];}
 private function ruleData(Request $r):array{return $r->validate(['name'=>['required','string','max:180'],'rule_type'=>['required',Rule::in(['legal','source_attribution','anti_sensationalism','minors','victims','accusations','politics','corrections','category'])],'instruction'=>['required','string','max:5000'],'priority'=>['required','integer','min:1','max:999']])+['active'=>$r->boolean('active')];}
}
