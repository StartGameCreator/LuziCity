<?php
namespace App\Services;use App\Models\NewsNarration;use Illuminate\Support\Facades\Http;use Illuminate\Support\Facades\Storage;use Illuminate\Support\Str;use RuntimeException;
class NewsNarrationService{
 public function generate(NewsNarration $narration):void{$profile=$narration->voiceProfile;$narration->update(['status'=>'generating','error_message'=>null]);$key=(string)config('luzicity.ai.openai_api_key');if($profile->provider!=='openai'||$key==='')throw new RuntimeException('Provedor de voz não configurado.');
  $response=Http::withToken($key)->timeout(120)->accept('audio/mpeg')->post('https://api.openai.com/v1/audio/speech',['model'=>$profile->model,'voice'=>$profile->voice,'input'=>$narration->input_text,'response_format'=>$profile->format]);
  if(!$response->successful())throw new RuntimeException('Falha no provedor de voz: HTTP '.$response->status());
  $path='narrations/'.Str::uuid().'.'.$profile->format;Storage::disk('public')->put($path,$response->body());$narration->update(['status'=>'pending_review','audio_path'=>$path,'audio_bytes'=>strlen($response->body()),'actual_cost'=>$narration->estimated_cost,'generated_at'=>now()]);
 }
 public function fail(NewsNarration $narration,\Throwable $e):void{$narration->update(['status'=>'failed','error_message'=>Str::limit($e->getMessage(),2000,'')]);}
}
