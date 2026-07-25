<?php
namespace Tests\Feature\AI;
use App\Models\AiExecution;use App\Models\AiProvider;use App\Models\User;use App\Services\AI\AiProviderQuotaService;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class AiProviderManagerTest extends TestCase{use RefreshDatabase;
 public function test_disabled_and_limited_provider_are_unavailable():void{$p=AiProvider::first();$q=app(AiProviderQuotaService::class);$this->assertFalse($q->available($p));$p->update(['is_enabled'=>true,'daily_request_limit'=>1]);AiExecution::create(['provider_id'=>$p->id,'feature'=>'test','status'=>'completed']);$this->assertFalse($q->available($p));}
 public function test_admin_page_never_displays_key():void{$u=User::factory()->create();Role::findOrCreate('Admin');$u->assignRole('Admin');$this->actingAs($u)->get('/admin/ia/provedores')->assertOk()->assertDontSee('OPENAI_API_KEY')->assertSee('As chaves nunca são exibidas');}
}
