<?php
namespace Tests\Feature;
use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Illuminate\Support\Facades\Route;use Spatie\Permission\Models\Role;use Tests\TestCase;
class EditorialRoomConsolidationTest extends TestCase{use RefreshDatabase;
 public function test_editor_reaches_consolidated_room_modules():void{Role::findOrCreate('Jornalista');$u=User::factory()->create();$u->assignRole('Jornalista');foreach(['/admin/redacao','/admin/redacao/pautas','/admin/redacao/agentes','/admin/redacao/calendario'] as $uri)$this->actingAs($u)->get($uri)->assertOk();$this->actingAs($u)->get('/admin/redacao')->assertSee('Sala de Redação')->assertSee('agentes e sugestões não publicam');}
 public function test_all_routes_remain_unique():void{$routes=collect(Route::getRoutes()->getRoutes());$names=$routes->pluck('action.as')->filter();$pairs=$routes->map(fn($r)=>implode('|',$r->methods()).' '.$r->uri());$this->assertSame($names->count(),$names->unique()->count());$this->assertSame($pairs->count(),$pairs->unique()->count());}
}
