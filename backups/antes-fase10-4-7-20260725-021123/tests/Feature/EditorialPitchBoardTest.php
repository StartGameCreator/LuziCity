<?php
namespace Tests\Feature;
use App\Models\Category;use App\Models\EditorialPitch;use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class EditorialPitchBoardTest extends TestCase{use RefreshDatabase;
 public function test_editor_can_create_and_move_pitch():void{Role::findOrCreate('Jornalista');$u=User::factory()->create();$u->assignRole('Jornalista');$c=Category::create(['name'=>'Local','slug'=>'local','is_active'=>true]);$this->actingAs($u)->post('/admin/redacao/pautas',['title'=>'Nova pauta','status'=>'idea','priority'=>'high','category_id'=>$c->id,'tasks'=>[['description'=>'Confirmar dados']]])->assertRedirect();$p=EditorialPitch::first();$this->assertSame($u->id,$p->created_by);$this->assertCount(1,$p->tasks);$this->actingAs($u)->patch("/admin/redacao/pautas/{$p->id}/mover",['status'=>'research'])->assertRedirect();$this->assertSame('research',$p->fresh()->status);}
 public function test_guest_cannot_open_board():void{$this->get('/admin/redacao/pautas')->assertRedirect('/login');}
}
