<?php
namespace Tests\Feature;use App\Models\AgencyApprovalAction;use App\Models\EditorialPitch;use App\Models\NewsArticle;use App\Models\RssImportedArticle;use App\Models\User;use App\Services\AgencyApprovalService;use Illuminate\Foundation\Testing\RefreshDatabase;use Tests\TestCase;
class AgencyApprovalTest extends TestCase{use RefreshDatabase;
 public function test_human_approval_creates_idea_not_news():void{$user=User::factory()->create();$article=RssImportedArticle::create(['title'=>'Fato local','original_url'=>'https://example.com/fato','excerpt'=>'Resumo','source_name'=>'Fonte','collection_status'=>'pending_review','is_visible'=>true]);$service=app(AgencyApprovalService::class);$pre=$service->approveArticle($article,$user);$pitch=$service->approvePrePitch($pre,$user);
  $this->assertSame('idea',$pitch->status);$this->assertSame(1,EditorialPitch::count());$this->assertSame(0,NewsArticle::count());$this->assertSame(2,AgencyApprovalAction::count());
 }}
