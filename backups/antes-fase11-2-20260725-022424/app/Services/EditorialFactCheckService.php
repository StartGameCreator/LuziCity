<?php
namespace App\Services;
use App\Models\EditorialSourceClaim;use Illuminate\Support\Str;
class EditorialFactCheckService{
 public function alerts(EditorialSourceClaim $claim):array{$text=$claim->claim;$source=(string)($claim->source?->excerpt?:$claim->source?->summary);$alerts=[];$checks=['dates'=>'/\b(?:\d{1,2}[\/.-]\d{1,2}(?:[\/.-]\d{2,4})?|\d{4})\b/u','numbers'=>'/\b\d+(?:[.,]\d+)?%?\b/u','quotes'=>'/[“"]([^”"]{3,})[”"]/u','names'=>'/\b[A-ZÁÉÍÓÚÂÊÔÃÕÇ][a-záéíóúâêôãõç]+(?:\s+[A-ZÁÉÍÓÚÂÊÔÃÕÇ][a-záéíóúâêôãõç]+)+/u'];foreach($checks as $kind=>$regex){preg_match_all($regex,$text,$matches);foreach(array_unique($matches[0]??[]) as $value)$alerts[]=['type'=>$kind,'value'=>$value,'found_in_source'=>$source!==''&&Str::contains(Str::lower($source),Str::lower(trim($value,'“”"')))];}if($source==='')$alerts[]=['type'=>'evidence','value'=>'Fonte sem trecho capturado; evidência insuficiente.','found_in_source'=>false];return $alerts;}
 public function automaticStatus(EditorialSourceClaim $claim):string{return $claim->status==='conflicting'?'conflicting':'review_required';}
}
