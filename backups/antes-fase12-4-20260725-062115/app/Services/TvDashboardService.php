<?php
namespace App\Services;use App\Models\TvBroadcast;use App\Models\TvChannel;use App\Models\Video;use App\Models\VideoCategory;use App\Models\VideoClip;use App\Models\VideoPlaylist;use App\Models\VideoScript;use App\Models\VideoSeries;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Schema;use Symfony\Component\Process\ExecutableFinder;
class TvDashboardService{
 public function data():array{$channels=TvChannel::where('is_active',true)->get();$live=$channels->map(fn($channel)=>app(TvBroadcastService::class)->liveFor($channel))->filter()->values();$binary=(string)config('media.ffmpeg_binary','ffmpeg');return[
  'stats'=>['channels'=>$channels->count(),'live_now'=>$live->count(),'scheduled'=>TvBroadcast::where('status','scheduled')->count(),'published_videos'=>Video::where('is_published',true)->count(),'series'=>VideoSeries::count(),'playlists'=>VideoPlaylist::count(),'categories'=>VideoCategory::count(),'scripts_pending'=>VideoScript::where('status','pending_review')->count(),'clips_queued'=>VideoClip::whereIn('status',['queued','rendering'])->count(),'clips_pending'=>VideoClip::where('status','pending_review')->count()],
  'live'=>$live,'schedule'=>TvBroadcast::with('channel')->where('status','scheduled')->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','>=',now()))->orderBy('starts_at')->limit(15)->get(),
  'videos'=>Video::with(['series','category'])->latest()->limit(12)->get(),'scripts'=>VideoScript::with('article')->latest()->limit(10)->get(),'clips'=>VideoClip::with('video')->latest()->limit(10)->get(),
  'ffmpegConfigured'=>is_file($binary)||!empty((new ExecutableFinder)->find($binary)),
  'renderQueue'=>Schema::hasTable('jobs')?DB::table('jobs')->where('queue',config('media.video_render_queue','video-render'))->count():0,
 ];}}
