<?php
namespace Tests\Feature;
use App\Models\RssImportedArticle;
use App\Models\RssTrendAlert;
use App\Services\RssTrendService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class RssTrendTest extends TestCase {
 use RefreshDatabase;
 public function test_recurring_local_subject_generates_alert_and_pitch_suggestion():void {
  foreach(range(1,3) as $i)RssImportedArticle::create(['title'=>"Chuva forte causa alagamento em Luziania {$i}",'original_url'=>"https://example.com/{$i}",'category'=>'Cidade','published_at'=>now()->subHour(),'is_visible'=>true]);
  $result=app(RssTrendService::class)->analyze();
  $this->assertGreaterThan(0,$result['alerts']);
  $alert=RssTrendAlert::with('trend')->first();
  $this->assertStringContainsString('Confirmar fatos',$alert->pitch_suggestion);
  $this->assertSame('Cidade',$alert->trend->category);
 }
}
