<?php
namespace Tests\Feature;
use App\Models\RssFeed;use App\Models\RssImportedArticle;use App\Models\User;use Illuminate\Foundation\Testing\RefreshDatabase;use Spatie\Permission\Models\Role;use Tests\TestCase;
class RssFeedManagementTest extends TestCase{use RefreshDatabase;
 public function test_admin_configures_frequency_and_deduplication():void{Role::findOrCreate('Admin');$u=User::factory()->create();$u->assignRole('Admin');$this->actingAs($u)->post('/admin/rss-feeds',['name'=>'Fonte local','url'=>'https://example.com/feed.xml','category'=>'Local','frequency_minutes'=>30,'deduplication_strategy'=>'url','is_active'=>1])->assertRedirect();$this->assertDatabaseHas('rss_feeds',['name'=>'Fonte local','frequency_minutes'=>30,'deduplication_strategy'=>'url','is_active'=>true]);}
 public function test_original_url_remains_unique_for_deduplication():void{$f=RssFeed::first();RssImportedArticle::create(['rss_feed_id'=>$f->id,'title'=>'Item','original_url'=>'https://example.com/item']);$this->expectException(\Illuminate\Database\QueryException::class);RssImportedArticle::create(['rss_feed_id'=>$f->id,'title'=>'Duplicado','original_url'=>'https://example.com/item']);}
}
