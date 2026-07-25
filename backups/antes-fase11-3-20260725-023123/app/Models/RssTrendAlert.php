<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RssTrendAlert extends Model {
 protected $fillable=['rss_trend_id','severity','title','pitch_suggestion','is_read','detected_at'];
 protected function casts():array{return['is_read'=>'boolean','detected_at'=>'datetime'];}
 public function trend():BelongsTo{return $this->belongsTo(RssTrend::class,'rss_trend_id');}
}
