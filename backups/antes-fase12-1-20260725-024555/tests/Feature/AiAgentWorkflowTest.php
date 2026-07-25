<?php
namespace Tests\Feature;
use App\Models\AiAgent;use App\Models\AiAgentRun;use App\Models\EditorialPitch;use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class AiAgentWorkflowTest extends TestCase{use RefreshDatabase;
 public function test_human_controls_agent_step_decision():void{Role::findOrCreate('Jornalista');$u=User::factory()->create();$u->assignRole('Jornalista');$p=EditorialPitch::create(['title'=>'Pauta','status'=>'idea','priority'=>'normal','created_by'=>$u->id]);$a=AiAgent::first();$this->actingAs($u)->post("/admin/redacao/agentes/pautas/{$p->id}/etapas",['ai_agent_id'=>$a->id,'output'=>'Resultado sujeito a revisão.'])->assertRedirect();$run=AiAgentRun::with('steps')->first();$this->assertSame('pending_review',$run->status);$step=$run->steps->first();$this->actingAs($u)->patch("/admin/redacao/agentes/etapas/{$step->id}/decidir",['decision'=>'accepted','editor_note'=>'Conferido'])->assertRedirect();$this->assertSame('accepted',$step->fresh()->status);$this->assertSame('idea',$p->fresh()->status);}
 public function test_agents_page_states_the_no_publish_rule():void{Role::findOrCreate('Admin');$u=User::factory()->create();$u->assignRole('Admin');$this->actingAs($u)->get('/admin/redacao/agentes')->assertOk()->assertSee('agentes não publicam conteúdo');}
}
