<?php
namespace App\Services\AI;
use App\Models\AiEditorialProfile;
use Illuminate\Support\Str;
class AiEditorialMemoryService {
 public function profile(?int $categoryId=null):AiEditorialProfile {
  return AiEditorialProfile::query()->with(['rules'=>fn($q)=>$q->where('active',true)->orderBy('priority')->orderBy('id'),'terms'=>fn($q)=>$q->where('active',true)->orderBy('type')->orderBy('term')])
   ->when($categoryId,fn($q)=>$q->where(fn($x)=>$x->where('category_id',$categoryId)->orWhere('is_default',true))->orderByRaw('category_id IS NULL'))
   ->when(!$categoryId,fn($q)=>$q->where('is_default',true))->firstOrFail();
 }
 public function compile(AiEditorialProfile $profile):string {
  $lines=["PERFIL EDITORIAL: {$profile->name}","Tom: {$profile->tone}","Público-alvo: ".($profile->target_audience?:'geral'),"Região prioritária: ".($profile->priority_region?:'não definida')];
  if($profile->editorial_rules)$lines[]="Diretrizes gerais:\n".$profile->editorial_rules;
  foreach($profile->rules as $rule)$lines[]="[{$rule->rule_type} | prioridade {$rule->priority}] {$rule->name}: {$rule->instruction}";
  foreach($profile->terms as $term)$lines[]="[termo {$term->type}] {$term->term}".($term->replacement?" => {$term->replacement}":'').($term->context?" ({$term->context})":'');
  $lines[]='Todo conteúdo permanece rascunho até aprovação humana.';
  return implode("\n",$lines);
 }
 public function review(string $text,AiEditorialProfile $profile):array {
  $notes=[];
  foreach($profile->terms as $term){
   if(!Str::contains(Str::lower($text),Str::lower($term->term)))continue;
   if($term->type==='forbidden')$notes[]="Termo proibido encontrado: {$term->term}.";
   if($term->type==='spelling'&&$term->replacement)$notes[]="Verificar grafia oficial: usar “{$term->replacement}” no lugar de “{$term->term}”.";
   if($term->type==='preferred'&&$term->replacement)$notes[]="Preferir “{$term->replacement}” no lugar de “{$term->term}”.";
  }
  return array_values(array_unique($notes));
 }
}
