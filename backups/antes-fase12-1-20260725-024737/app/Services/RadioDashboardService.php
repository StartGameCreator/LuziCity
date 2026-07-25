<?php
namespace App\Services;
use App\Models\AudioCampaign;use App\Models\AudioSpot;use App\Models\NewsNarration;use App\Models\PodcastEpisode;use App\Models\PodcastSeries;use App\Models\RadioProgram;use App\Models\RadioScheduleSlot;use App\Models\RadioStation;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Schema;
class RadioDashboardService{
 public function data():array{$station=RadioStation::where('is_active',true)->first();return[
  'station'=>$station,'onAir'=>$station?app(RadioOnAirService::class)->current($station):null,
  'stats'=>['programs'=>RadioProgram::where('is_active',true)->count(),'schedule_slots'=>RadioScheduleSlot::where('is_active',true)->count(),'podcast_series'=>PodcastSeries::count(),'published_episodes'=>PodcastEpisode::where('is_published',true)->count(),'narrations_pending'=>NewsNarration::whereIn('status',['queued','generating','pending_review'])->count(),'audio_campaigns'=>AudioCampaign::where('status','active')->count(),'audio_spots'=>AudioSpot::where('is_active',true)->count()],
  'audioCost'=>NewsNarration::sum(DB::raw('COALESCE(actual_cost, estimated_cost)')),
  'queue'=>Schema::hasTable('jobs')?['audio'=>DB::table('jobs')->where('queue','audio')->count(),'default'=>DB::table('jobs')->where('queue','default')->count()]:['audio'=>0,'default'=>0],
  'narrations'=>NewsNarration::with(['article','voiceProfile'])->latest()->limit(10)->get(),
  'campaigns'=>AudioCampaign::with('spot')->withCount('plays')->latest()->limit(10)->get(),
  'podcasts'=>PodcastSeries::withCount('episodes')->latest()->limit(10)->get(),
 ];}
}
