<?php
namespace App\Services;use App\Models\RssImportedArticle;use App\Models\RssPrePitch;
class RssPrePitchService{
 public function generate(RssImportedArticle $article,?int $userId=null):RssPrePitch{
  $related=$article->topic_group_id?$article->relatedSources()->get():collect();
  $sources=$related->prepend($article)->unique('original_url')->map(fn($item)=>['name'=>$item->source_name,'url'=>$item->original_url,'title'=>$item->title])->values()->all();
  return RssPrePitch::updateOrCreate(['rss_imported_article_id'=>$article->id],[
   'status'=>'pending_review','title'=>$article->title,'summary'=>$article->excerpt?:'Resumo ainda precisa ser apurado pela redação.','source_links'=>$sources,
   'questions'=>['O fato está confirmado por fonte oficial?','Quem é diretamente afetado?','Quais versões ainda precisam ser ouvidas?'],
   'risks'=>['Informação baseada inicialmente em fonte externa.','Dados, nomes e contexto exigem verificação humana.','Evitar reproduzir texto integral da publicação de origem.'],
   'local_relevance'=>$this->localRelevance($article),'editorial_recommendation'=>'Avaliar interesse público, confirmar os fatos e consultar fontes independentes antes de avançar para redação.',
   'generated_by'=>$userId,'generated_at'=>now(),
  ]);
 }
 private function localRelevance(RssImportedArticle $article):string{
  $text=mb_strtolower($article->title.' '.$article->excerpt);
  foreach(config('luzicity.city_locations',[]) as $city){$name=$city['name']??'';if($name&&str_contains($text,mb_strtolower($name)))return "Há menção direta a {$name}; priorizar fontes e impactos locais.";}
  return 'Relevância local ainda não confirmada; verificar conexão com a área de cobertura da Luzicity.';
 }
}
