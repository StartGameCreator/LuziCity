<?php
namespace App\Http\Controllers;
use App\Models\AiAgent;use App\Models\AiAgentRun;use App\Models\AiAgentStep;use App\Models\EditorialPitch;use App\Services\AI\AiAgentWorkflowService;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;use Illuminate\Validation\Rule;use Illuminate\View\View;
class AdminAiAgentController extends Controller{
 public function index():View{return view('admin.ai-agents.index',['agents'=>AiAgent::orderBy('position')->get()]);}
 public function record(Request $r,EditorialPitch $pitch,AiAgentWorkflowService $flow):RedirectResponse{$data=$r->validate(['ai_agent_id'=>['required','exists:ai_agents,id'],'output'=>['required','string','max:50000']]);$agent=AiAgent::whereKey($data['ai_agent_id'])->where('is_enabled',true)->firstOrFail();$flow->record($pitch,$agent,$r->user(),$data['output']);return back()->with('status','Etapa do agente registrada para revisão humana.');}
 public function decide(Request $r,AiAgentStep $step,AiAgentWorkflowService $flow):RedirectResponse{$data=$r->validate(['decision'=>['required',Rule::in(['accepted','rejected','redo_requested'])],'editor_note'=>['nullable','string','max:3000']]);$flow->decide($step,$r->user(),$data['decision'],$data['editor_note']??null);return back()->with('status','Decisão editorial registrada.');}
}
