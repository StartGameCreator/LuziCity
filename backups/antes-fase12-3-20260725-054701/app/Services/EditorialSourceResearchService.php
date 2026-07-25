<?php
namespace App\Services;
use App\Services\Security\PublicUrlGuard;use DOMDocument;use Illuminate\Support\Facades\Http;use Illuminate\Validation\ValidationException;
class EditorialSourceResearchService{
 public function __construct(private PublicUrlGuard $guard){}
 public function fetch(string $url):array{$url=$this->guard->validate($url);$response=Http::timeout(8)->connectTimeout(3)->withOptions(['allow_redirects'=>false])->withHeaders(['User-Agent'=>'LuziCity-Research/1.0','Accept'=>'text/html,text/plain'])->get($url);if($response->redirect())throw ValidationException::withMessages(['url'=>'Redirecionamentos são bloqueados; informe a URL final.']);$response->throw();$body=$response->body();if(strlen($body)>1_000_000)throw ValidationException::withMessages(['url'=>'Fonte excede o limite de 1 MB.']);$type=strtolower($response->header('Content-Type'));if(!str_contains($type,'text/html')&&!str_contains($type,'text/plain'))throw ValidationException::withMessages(['url'=>'Tipo de conteúdo remoto não permitido.']);$text=$this->text($body);return['title'=>$this->title($body)?:parse_url($url,PHP_URL_HOST),'metadata'=>['content_type'=>$type,'bytes'=>strlen($body)],'excerpt'=>mb_substr($text,0,1200),'summary'=>mb_substr($text,0,500),'fetched_at'=>now()];}
 private function text(string $html):string{$clean=preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is',' ',$html);return trim(preg_replace('/\s+/u',' ',strip_tags($clean??''))??'');}
 private function title(string $html):?string{if(!preg_match('/<title[^>]*>(.*?)<\/title>/is',$html,$m))return null;return mb_substr(trim(html_entity_decode(strip_tags($m[1]),ENT_QUOTES|ENT_HTML5,'UTF-8')),0,180);}
}
