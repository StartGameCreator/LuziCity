<?php
namespace App\Services;use App\Models\AudioCampaign;use Carbon\CarbonInterface;
class AudioAdvertisingService{
 public function current(?CarbonInterface $now=null):?AudioCampaign{$now=$now?:now();return AudioCampaign::with('spot')->withCount('plays')->where('status','active')->whereHas('spot',fn($q)=>$q->where('is_active',true))->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',$now))->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',$now))->orderByDesc('priority')->get()->first(function($c)use($now){if($c->max_plays!==null&&$c->plays_count>=$c->max_plays)return false;if($c->weekdays&&!in_array($now->dayOfWeek,$c->weekdays))return false;$time=$now->format('H:i:s');if($c->daily_starts_at&&$time<$c->daily_starts_at)return false;if($c->daily_ends_at&&$time>$c->daily_ends_at)return false;return true;});}
}
