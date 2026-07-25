<?php
namespace App\Services\AI;
use App\Models\AiAgent;use App\Models\AiAgentRun;use App\Models\AiAgentStep;use App\Models\EditorialPitch;use App\Models\User;use Illuminate\Validation\ValidationException;
class AiAgentWorkflowService{
 public function record(EditorialPitch $pitch,AiAgent $agent,User $user,string $output):AiAgentRun{$run=AiAgentRun::create(['editorial_pitch_id'=>$pitch->id,'ai_agent_id'=>$agent->id,'requested_by'=>$user->id,'status'=>'pending_review','current_step'=>1]);$run->steps()->create(['sequence'=>1,'status'=>'pending_review','output'=>$output]);return $run;}
 public function decide(AiAgentStep $step,User $user,string $decision,?string $note):void{if($step->status!=='pending_review')throw ValidationException::withMessages(['decision'=>'Esta etapa já foi decidida.']);$step->update(['status'=>$decision,'editor_note'=>$note,'decided_by'=>$user->id,'decided_at'=>now()]);$run=$step->run;if($decision==='accepted')$run->update(['status'=>'accepted']);elseif($decision==='rejected')$run->update(['status'=>'rejected']);else{$run->update(['status'=>'redo_requested','current_step'=>$step->sequence+1]);}}
}
