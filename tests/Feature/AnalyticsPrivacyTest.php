<?php

namespace Tests\Feature;

use App\Models\AnalyticsPageview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_requires_explicit_consent(): void
    {
        $payload = ['event_uuid' => (string) Str::uuid(), 'event' => 'page_view', 'page_path' => '/'];
        $this->postJson(route('analytics.collect'), $payload)->assertForbidden();
        $this->post(route('privacy.analytics.consent'), ['choice' => 'accepted'])
            ->assertRedirect()->assertPlainCookie('luzicity_analytics_consent', 'accepted');
        $this->withHeader('X-Analytics-Consent', 'accepted')->postJson(route('analytics.collect'), $payload)->assertCreated();
    }

    public function test_opt_out_deletes_user_analytics_and_records_request(): void
    {
        $user = User::factory()->create();
        AnalyticsPageview::create([
            'event_uuid' => (string) Str::uuid(), 'session_hash' => hash('sha256', 'other'),
            'user_id' => $user->id, 'page_path' => '/privado', 'device_type' => 'desktop',
            'viewed_at' => now(), 'last_activity_at' => now(),
        ]);
        $this->actingAs($user)->post(route('privacy.analytics.opt-out'))
            ->assertRedirect()->assertPlainCookie('luzicity_analytics_consent', 'denied');
        $this->assertDatabaseMissing('analytics_pageviews', ['user_id' => $user->id]);
        $this->assertDatabaseHas('privacy_data_requests', ['user_id' => $user->id, 'type' => 'analytics_opt_out', 'status' => 'completed']);
    }

    public function test_retention_command_purges_only_expired_events(): void
    {
        config(['analytics.retention_days' => 30]);
        foreach ([now()->subDays(31), now()->subDays(10)] as $date) {
            AnalyticsPageview::create([
                'event_uuid' => (string) Str::uuid(), 'session_hash' => hash('sha256', (string) $date),
                'page_path' => '/teste', 'device_type' => 'desktop', 'viewed_at' => $date, 'last_activity_at' => $date,
            ]);
        }
        $this->artisan('analytics:purge-expired')->assertSuccessful();
        $this->assertSame(1, AnalyticsPageview::count());
    }
}
