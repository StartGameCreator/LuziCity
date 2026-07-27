<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NewsArticle;
use App\Models\PaywallCategoryRule;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaywallManagementTest extends TestCase
{
    use RefreshDatabase;

    private function article(User $author, string $slug, string $body = 'Trecho inicial. Conteúdo exclusivo completo para assinantes.'): NewsArticle
    {
        return NewsArticle::create([
            'author_id' => $author->id, 'title' => 'Exclusivo '.$slug, 'slug' => $slug, 'body' => $body,
            'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(), 'is_premium' => true,
        ]);
    }

    public function test_guest_sees_preview_and_subscription_call_to_action(): void
    {
        $author = User::factory()->create();
        $article = $this->article($author, 'premium-guest', str_repeat('Conteúdo protegido ', 100));
        $this->get(route('news.show', $article))->assertOk()
            ->assertSee('Continue lendo com uma assinatura')
            ->assertDontSee(str_repeat('Conteúdo protegido ', 80));
    }

    public function test_member_limit_counts_unique_articles_and_blocks_next_article(): void
    {
        $author = User::factory()->create();
        $reader = User::factory()->create();
        $plan = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        $plan->update(['monthly_article_limit' => 1]);
        $reader->subscription()->create([
            'subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(),
        ]);
        $first = $this->article($author, 'primeiro', 'PRIMEIRO COMPLETO');
        $second = $this->article($author, 'segundo', 'SEGUNDO COMPLETO');
        $this->actingAs($reader)->get(route('news.show', $first))->assertSee('PRIMEIRO COMPLETO');
        $this->actingAs($reader)->get(route('news.show', $first))->assertSee('PRIMEIRO COMPLETO');
        $this->actingAs($reader)->get(route('news.show', $second))
            ->assertSee('Continue lendo com uma assinatura');
    }

    public function test_category_rule_protects_non_premium_article(): void
    {
        $author = User::factory()->create();
        $category = Category::create(['name' => 'Especial', 'slug' => 'especial', 'is_active' => true]);
        PaywallCategoryRule::create(['category_id' => $category->id, 'is_enabled' => true, 'preview_characters' => 100]);
        $article = NewsArticle::create([
            'author_id' => $author->id, 'category_id' => $category->id, 'title' => 'Especial', 'slug' => 'especial-paywall',
            'body' => str_repeat('Texto ', 100), 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
            'is_premium' => false,
        ]);
        $this->get(route('news.show', $article))->assertSee('Continue lendo com uma assinatura');
    }
}
