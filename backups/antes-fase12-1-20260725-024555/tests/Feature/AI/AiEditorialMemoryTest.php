<?php
namespace Tests\Feature\AI;
use App\Models\AiEditorialProfile;use App\Models\AiEditorialTerm;use App\Models\User;use App\Services\AI\AiEditorialMemoryService;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class AiEditorialMemoryTest extends TestCase{
 use RefreshDatabase;
 public function test_forbidden_term_creates_review_note_and_memory_requires_human_review():void{$p=AiEditorialProfile::where('is_default',true)->first();$p->terms()->create(['term'=>'bombástico','type'=>'forbidden','active'=>true]);$m=app(AiEditorialMemoryService::class);$p=$m->profile();$this->assertStringContainsString('aprovação humana',$m->compile($p));$this->assertStringContainsString('Termo proibido',$m->review('Um caso bombástico',$p)[0]);}
 public function test_admin_can_open_memory_and_journalist_cannot():void{$a=User::factory()->create();Role::findOrCreate('Admin');$a->assignRole('Admin');$this->actingAs($a)->get('/admin/ia/memoria')->assertOk()->assertSee('Memória Editorial');$j=User::factory()->create();Role::findOrCreate('Jornalista');$j->assignRole('Jornalista');$this->actingAs($j)->get('/admin/ia/memoria')->assertForbidden();}
}
