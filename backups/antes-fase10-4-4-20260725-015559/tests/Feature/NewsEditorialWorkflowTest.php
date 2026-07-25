<?php
namespace Tests\Feature;
use App\Models\NewsArticle;use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class NewsEditorialWorkflowTest extends TestCase{use RefreshDatabase;
 private function user(string $role):User{Role::findOrCreate($role);$u=User::factory()->create();$u->assignRole($role);return $u;}
 public function test_direct_publish_is_blocked_and_version_is_created():void{$u=$this->user('Jornalista');$this->actingAs($u)->post('/admin/news',['title'=>'Matéria','body'=>'Texto revisável','status'=>'published'])->assertRedirect();$a=NewsArticle::first();$this->assertSame('draft',$a->status);$this->assertSame('draft',$a->workflow_status);$this->assertCount(1,$a->versions);}
 public function test_only_admin_publishes_after_human_approval():void{$j=$this->user('Jornalista');$admin=$this->user('Admin');$a=NewsArticle::create(['author_id'=>$j->id,'title'=>'Matéria','slug'=>'materia','body'=>'Texto','status'=>'draft','workflow_status'=>'editorial_review']);$this->actingAs($j)->post("/admin/news/{$a->id}/fluxo",['action'=>'approve'])->assertForbidden();$this->actingAs($admin)->post("/admin/news/{$a->id}/fluxo",['action'=>'approve','note'=>'Revisado'])->assertRedirect();$this->actingAs($admin)->post("/admin/news/{$a->id}/fluxo",['action'=>'publish','note'=>'Publicação autorizada'])->assertRedirect();$this->assertSame('published',$a->fresh()->status);$this->assertSame($admin->id,$a->fresh()->published_by);$this->assertCount(2,$a->editorialReviews);}
}
