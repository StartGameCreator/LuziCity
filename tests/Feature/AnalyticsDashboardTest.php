<?php

namespace Tests\Feature;

use App\Models\AnalyticsPageview;
use App\Models\NewsArticle;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_aggregates_news_authors_sources_and_conversions(): void
    {
        $admin = User::factory()->create(['name' => 'Autora Métrica']);
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'title' => 'Notícia medida', 'slug' => 'noticia-medida',
            'body' => 'Conteúdo', 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        foreach (['session-a', 'session-b'] as $session) {
            AnalyticsPageview::create([
                'event_uuid' => (string) Str::uuid(), 'session_hash' => hash('sha256', $session),
                'news_article_id' => $article->id, 'page_path' => '/noticias/noticia-medida',
                'source' => 'newsletter', 'campaign' => 'edicao-matutina', 'device_type' => 'mobile',
                'reading_time_seconds' => 45, 'max_scroll_percent' => 80, 'viewed_at' => now(), 'last_activity_at' => now(),
            ]);
        }
        $reader = User::factory()->create();
        $plan = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        $subscription = app(SubscriptionService::class)->update($reader, [
            'subscription_plan_id' => $plan->id, 'status' => 'active', 'billing_cycle' => 'monthly',
            'price' => $plan->monthly_price, 'starts_at' => now(),
        ], $admin);
        SubscriptionPayment::create([
            'subscription_id' => $subscription->id, 'user_id' => $reader->id, 'external_reference' => 'DASH-PAY',
            'provider_payment_id' => 'DASH-1', 'status' => 'paid', 'amount' => $plan->monthly_price, 'paid_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.analytics.index', ['period' => 7]))->assertOk()
            ->assertSee('Notícia medida')->assertSee('Autora Métrica')
            ->assertSee('newsletter')->assertSee('edicao-matutina')
            ->assertSee('Pagamentos aprovados');
    }
}
