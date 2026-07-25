<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AudioAdPlay extends Model{protected $fillable=['audio_campaign_id','session_hash','completed','listened_seconds','user_agent_hash','played_at'];protected function casts():array{return['completed'=>'boolean','played_at'=>'datetime'];}public function campaign():BelongsTo{return $this->belongsTo(AudioCampaign::class,'audio_campaign_id');}}
