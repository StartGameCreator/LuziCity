<?php
namespace App\Services\AI;
use App\Models\AiProvider;use App\Models\Setting;
class AiProviderHealthService{
 public function check(AiProvider $p):array{$s=Setting::aiSettings();$configured=match($p->slug){'chatgpt'=>filled($s['openai_api_key']??null),'gemini'=>filled($s['gemini_api_key']??null),'copilot'=>filled($s['copilot_api_key']??null)&&filled($s['copilot_endpoint']??null),default=>false};$message=$configured?'Credencial localizada com segurança. Provedor pronto para uma chamada editorial controlada.':'Credencial ou endpoint ainda não configurado.';$p->update(['last_checked_at'=>now(),'health_status'=>$configured?'ready':'not_configured','last_failure_message'=>$configured?null:$message]);return ['ok'=>$configured,'message'=>$message];}
 public function failure(AiProvider $p,string $message):void{$fail=$p->consecutive_failures+1;$p->update(['consecutive_failures'=>$fail,'health_status'=>'failing','last_failure_message'=>mb_substr($message,0,500),'circuit_open_until'=>$fail>=3?now()->addMinutes(15):null]);}
 public function success(AiProvider $p):void{$p->update(['consecutive_failures'=>0,'health_status'=>'healthy','last_failure_message'=>null,'circuit_open_until'=>null,'last_checked_at'=>now()]);}
}
