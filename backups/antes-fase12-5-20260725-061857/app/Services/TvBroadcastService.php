<?php
namespace App\Services;use App\Models\TvBroadcast;use App\Models\TvChannel;use Carbon\CarbonInterface;
class TvBroadcastService{
 public function liveFor(TvChannel $channel,?CarbonInterface $now=null):?TvBroadcast{$now=$now?:now();return TvBroadcast::where('tv_channel_id',$channel->id)->where(fn($q)=>$q->where('force_live',true)->orWhere(function($q)use($now){$q->where('status','live')->where(fn($x)=>$x->whereNull('starts_at')->orWhere('starts_at','<=',$now))->where(fn($x)=>$x->whereNull('ends_at')->orWhere('ends_at','>=',$now));}))->orderByDesc('force_live')->orderBy('starts_at')->first();}
 public function embedUrl(TvBroadcast $broadcast):?string{$url=$broadcast->playback_url;if(!$url)return null;if($broadcast->provider==='youtube'&&preg_match('~(?:youtu\\.be/|youtube\\.com/(?:watch\\?v=|live/|embed/))([A-Za-z0-9_-]{6,})~',$url,$m))return 'https://www.youtube-nocookie.com/embed/'.$m[1];if($broadcast->provider==='vimeo'&&preg_match('~vimeo\\.com/(?:video/)?(\\d+)~',$url,$m))return 'https://player.vimeo.com/video/'.$m[1];return in_array($broadcast->provider,['hls','embed'],true)?$url:null;}
}
