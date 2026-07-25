<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class RssTrend extends Model {
 protected $fillable=['term','category','location','mention_count','previous_count','growth_percent','score','window_started_at','window_ended_at'];
 protected function casts():array{return['growth_percent'=>'float','score'=>'float','window_started_at'=>'datetime','window_ended_at'=>'datetime'];}
 public function alerts():HasMany{return $this->hasMany(RssTrendAlert::class);}
}
