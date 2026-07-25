<?php
namespace Tests\Feature;use App\Models\RssImportedArticle;use App\Models\RssPrePitch;use App\Services\RssPrePitchService;use Illuminate\Foundation\Testing\RefreshDatabase;use Tests\TestCase;
class RssPrePitchTest extends TestCase{use RefreshDatabase;
 public function test_generation_prepares_review_material_without_creating_news_or_pitch():void{$article=RssImportedArticle::create(['title'=>'Obra pública em Luziania','original_url'=>'https://example.com/obra','excerpt'=>'Anúncio inicial da obra.','source_name'=>'Fonte','is_visible'=>true]);$item=app(RssPrePitchService::class)->generate($article);
  $this->assertSame('pending_review',$item->status);$this->assertNotEmpty($item->questions);$this->assertNotEmpty($item->risks);$this->assertSame(0,\App\Models\NewsArticle::count());$this->assertSame(0,\App\Models\EditorialPitch::count());$this->assertSame(1,RssPrePitch::count());
 }}
