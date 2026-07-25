<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TvBroadcast extends Model{protected $fillable=['tv_channel_id','title','description','provider','playback_url','embed_code','rtmp_server','rtmp_key','starts_at','ends_at','status','force_live'];protected $hidden=['rtmp_key'];protected function casts():array{return['starts_at'=>'datetime','ends_at'=>'datetime','force_live'=>'boolean','rtmp_key'=>'encrypted'];}public function channel():BelongsTo{return $this->belongsTo(TvChannel::class,'tv_channel_id');}}
