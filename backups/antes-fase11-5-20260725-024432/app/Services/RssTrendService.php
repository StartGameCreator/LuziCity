<?php
namespace App\Services;
use App\Models\RssImportedArticle;
use App\Models\RssTrend;
use App\Models\RssTrendAlert;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RssTrendService {
 public function analyze():array {
  $end=now()->startOfHour(); $start=$end->copy()->subDay(); $previous=$start->copy()->subDay();
  $current=RssImportedArticle::query()->whereBetween('published_at',[$start,$end])->get();
  $old=RssImportedArticle::query()->whereBetween('published_at',[$previous,$start])->get();
  $currentCounts=$this->counts($current); $oldCounts=$this->counts($old); $saved=0; $alerts=0;
  foreach($currentCounts as $key=>$row){
   if($row['count']<2)continue; $before=$oldCounts[$key]['count']??0;
   $growth=$before>0?(($row['count']-$before)/$before)*100:($row['count']>1?100:0);
   $trend=RssTrend::updateOrCreate(
    ['term'=>$row['term'],'category'=>$row['category'],'location'=>$row['location'],'window_ended_at'=>$end],
    ['mention_count'=>$row['count'],'previous_count'=>$before,'growth_percent'=>round($growth,2),'score'=>round($row['count']*(1+max(0,$growth)/100),2),'window_started_at'=>$start]
   ); $saved++;
   if($row['count']>=3&&($growth>=50||$row['count']>=5)){
    RssTrendAlert::firstOrCreate(['rss_trend_id'=>$trend->id],[
     'severity'=>$row['count']>=5?'high':'attention','title'=>"Assunto em alta: {$row['term']}",
     'pitch_suggestion'=>"Avaliar pauta sobre “{$row['term']}” com {$row['count']} menções recentes".($row['location']?" em {$row['location']}":'').'. Confirmar fatos e consultar fontes locais antes de produzir.',
     'detected_at'=>now(),
    ]); $alerts++;
   }
  }
  return compact('saved','alerts');
 }
 private function counts(Collection $articles):array {
  $counts=[];
  foreach($articles as $article){
   foreach($this->terms($article->title) as $term){
    $location=$this->location($article->title); $key=implode('|',[$term,$article->category,$location]);
    $counts[$key]??=['term'=>$term,'category'=>$article->category,'location'=>$location,'count'=>0]; $counts[$key]['count']++;
   }
  }
  return $counts;
 }
 private function terms(string $title):array {
  $stop=['para','como','mais','pela','pelo','sobre','entre','apos','com','sem','uma','dos','das','que','por','em','de','do','da','no','na'];
  $text=preg_replace('/[^a-z0-9\s]/',' ',Str::ascii(mb_strtolower($title)))?:'';
  return collect(preg_split('/\s+/',trim($text))?:[])->filter(fn($w)=>strlen($w)>=4&&!in_array($w,$stop,true))->unique()->values()->all();
 }
 private function location(string $title):?string {
  $plain=Str::ascii(mb_strtolower($title));
  foreach(config('luzicity.city_locations',[]) as $city){$name=$city['name']??'';if($name!==''&&str_contains($plain,Str::ascii(mb_strtolower($name))))return $name;}
  return null;
 }
}
