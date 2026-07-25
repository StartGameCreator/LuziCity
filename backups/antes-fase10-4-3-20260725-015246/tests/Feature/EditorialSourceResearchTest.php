<?php
namespace Tests\Feature;
use App\Models\EditorialPitch;use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class EditorialSourceResearchTest extends TestCase{use RefreshDatabase;
 public function test_editor_adds_source_and_claim():void{Role::findOrCreate('Jornalista');$u=User::factory()->create();$u->assignRole('Jornalista');$p=EditorialPitch::create(['title'=>'Fato local','status'=>'research','priority'=>'normal','created_by'=>$u->id]);$this->actingAs($u)->post("/admin/redacao/fontes/pautas/{$p->id}",['title'=>'Documento oficial','url'=>'https://example.com','reliability'=>'high'])->assertRedirect();$s=$p->sources()->first();$this->actingAs($u)->post("/admin/redacao/fontes/{$s->id}/afirmacoes",['claim'=>'O evento ocorrerá em agosto'])->assertRedirect();$this->actingAs($u)->put("/admin/redacao/pautas/{$p->id}",['title'=>'Fato local atualizado','summary'=>'Resumo','status'=>'research','priority'=>'normal'])->assertRedirect();$this->assertDatabaseHas('editorial_pitch_sources',['id'=>$s->id,'reliability'=>'high']);$this->assertDatabaseHas('editorial_source_claims',['editorial_pitch_source_id'=>$s->id]);}
}
