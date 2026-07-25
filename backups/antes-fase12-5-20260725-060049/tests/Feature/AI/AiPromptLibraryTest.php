<?php
namespace Tests\Feature\AI;
use App\Models\AiPromptTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class AiPromptLibraryTest extends TestCase {
 use RefreshDatabase;
 private function admin():User{$u=User::factory()->create();Role::findOrCreate('Admin');$u->assignRole('Admin');return $u;}
 public function test_edit_creates_version_and_restore_preserves_history():void{
  $this->actingAs($this->admin());
  $p=AiPromptTemplate::query()->first();
  $payload=['key'=>$p->key,'name'=>$p->name,'purpose'=>$p->purpose,'system_prompt'=>'Sistema novo','user_template'=>'Texto {{briefing}}','is_active'=>'1','change_notes'=>'Teste'];
  $this->put(route('admin.ai.prompts.update',$p),$payload)->assertRedirect();
  $p->refresh();$this->assertDatabaseHas('ai_prompt_versions',['ai_prompt_template_id'=>$p->id,'version'=>$p->version]);
  $v=$p->versions()->first();
  $this->post(route('admin.ai.prompts.restore',[$p,$v]))->assertRedirect();
  $this->assertGreaterThanOrEqual(2,$p->versions()->count());
 }
 public function test_arbitrary_placeholder_is_rejected():void{
  $this->actingAs($this->admin());$p=AiPromptTemplate::query()->first();
  $this->put(route('admin.ai.prompts.update',$p),['key'=>$p->key,'name'=>$p->name,'purpose'=>$p->purpose,'system_prompt'=>'x','user_template'=>'{{dangerous_code}}'])->assertSessionHasErrors('user_template');
 }
}
