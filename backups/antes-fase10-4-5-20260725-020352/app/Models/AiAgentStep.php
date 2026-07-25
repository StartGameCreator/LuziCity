<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AiAgentStep extends Model{protected $fillable=['ai_agent_run_id','sequence','status','output','editor_note','decided_by','decided_at'];protected function casts():array{return['decided_at'=>'datetime'];}public function run():BelongsTo{return $this->belongsTo(AiAgentRun::class,'ai_agent_run_id');}public function decider():BelongsTo{return $this->belongsTo(User::class,'decided_by');}}
