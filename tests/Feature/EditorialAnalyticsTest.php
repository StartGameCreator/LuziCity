<?php

namespace Tests\Feature;

use App\Models\AnalyticsPageview;
use App\Models\Category;
use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditorialAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_reports_editorial_metrics_by_category_and_hour(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $category = Category::create(['name' => 'Cidade', 'slug' => 'cidade']);
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'category_id' => $category->id,
            'title' => 'Leitura editorial', 'slug' => 'leitura-editorial', 'body' => 'Conteúdo',
            'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        AnalyticsPageview::create([
            'event_uuid' => (string) Str::uuid(), 'session_hash' => hash('sha256', 'reader'),
            'news_article_id' => $article->id, 'page_path' => '/noticias/leitura-editorial',
            'device_type' => 'mobile', 'reading_time_seconds' => 60, 'max_scroll_percent' => 90,
            'share_count' => 2, 'viewed_at' => now()->setHour(9), 'last_activity_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.analytics.index', ['period' => 7]))->assertOk()
            ->assertSee('Analytics editorial')->assertSee('Conclusão de leitura')
            ->assertSee('Cidade')->assertSee('Compartilhamentos')->assertSee('09h');
    }
}
