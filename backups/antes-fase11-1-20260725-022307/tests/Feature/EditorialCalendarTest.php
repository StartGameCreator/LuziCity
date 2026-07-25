<?php
namespace Tests\Feature;
use App\Models\EditorialCalendarEvent;use App\Models\EditorialPitch;use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class EditorialCalendarTest extends TestCase{use RefreshDatabase;
 private function editor():User{Role::findOrCreate('Jornalista');$u=User::factory()->create();$u->assignRole('Jornalista');return $u;}
 public function test_calendar_shows_pitch_deadline():void{$u=$this->editor();EditorialPitch::create(['title'=>'Pauta com prazo','status'=>'research','priority'=>'high','assignee_id'=>$u->id,'due_at'=>now()->addDay()]);$this->actingAs($u)->get('/admin/redacao/calendario?view=week&date='.now()->toDateString())->assertOk()->assertSee('Pauta com prazo');}
 public function test_ai_suggestion_never_creates_pitch():void{$u=$this->editor();$before=EditorialPitch::count();$this->actingAs($u)->post('/admin/redacao/calendario/sugestoes',['title'=>'Sugestão local','starts_at'=>now()->addDays(2)->format('Y-m-d H:i:s')])->assertRedirect();$this->assertSame($before,EditorialPitch::count());$this->assertDatabaseHas('editorial_calendar_events',['title'=>'Sugestão local','status'=>'suggested','is_ai_suggestion'=>true]);}
}
