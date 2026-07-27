<?php

namespace Tests\Feature;

use App\Models\AnalyticsPageview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyticsCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pageview_and_engagement_update_same_event_idempotently(): void
    {
        $uuid = (string) Str::uuid();
        $base = [
            'event_uuid' => $uuid, 'page_path' => '/noticias/teste?utm_source=facebook',
            'page_title' => 'Notícia teste', 'referrer' => 'https://facebook.com/post',
            'source' => 'facebook', 'medium' => 'social', 'campaign' => 'lancamento',
        ];
        $this->withHeaders(['X-Analytics-Consent' => 'accepted', 'User-Agent' => 'Mozilla/5.0 (iPhone; Mobile)'])->postJson(route('analytics.collect'), $base + ['event' => 'page_view'])->assertCreated();
        $this->withHeader('X-Analytics-Consent', 'accepted')->postJson(route('analytics.collect'), $base + ['event' => 'engagement', 'reading_time_seconds' => 35, 'max_scroll_percent' => 70])->assertOk();
        $this->withHeader('X-Analytics-Consent', 'accepted')->postJson(route('analytics.collect'), $base + ['event' => 'engagement', 'reading_time_seconds' => 20, 'max_scroll_percent' => 40])->assertOk();
        $this->withHeader('X-Analytics-Consent', 'accepted')->postJson(route('analytics.collect'), $base + ['event' => 'share'])->assertOk();
        $this->assertDatabaseCount('analytics_pageviews', 1);
        $view = AnalyticsPageview::first();
        $this->assertSame(35, $view->reading_time_seconds);
        $this->assertSame(70, $view->max_scroll_percent);
        $this->assertSame('mobile', $view->device_type);
        $this->assertSame('facebook.com', $view->referrer_host);
        $this->assertSame(1, $view->share_count);
        $this->assertNotNull($view->last_shared_at);
    }

    public function test_admin_can_view_collection_dashboard(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        AnalyticsPageview::create([
            'event_uuid' => (string) Str::uuid(), 'session_hash' => hash('sha256', 'session'),
            'page_path' => '/noticias/local', 'source' => 'newsletter', 'campaign' => 'manha',
            'device_type' => 'desktop', 'reading_time_seconds' => 50, 'max_scroll_percent' => 90,
            'viewed_at' => now(), 'last_activity_at' => now(),
        ]);
        $this->actingAs($admin)->get(route('admin.analytics.index'))->assertOk()
            ->assertSee('/noticias/local')->assertSee('newsletter');
    }
}
