<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\NewsDistribution;
use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\User;
use App\Services\NewsDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SharedNewsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_is_visible_on_target_without_copy_and_attributes_origin(): void
    {
        [$sourceSite, $targetSite] = $this->sites();
        app()->instance('currentSite', $sourceSite);
        $admin = $this->admin();
        $article = $this->article($admin, 'Notícia original', 'noticia-original');
        app(NewsDistributionService::class)->distribute($article, $targetSite, 'reference', $admin);

        app()->instance('currentSite', $targetSite);
        $visible = NewsArticle::published()->firstOrFail();
        $this->assertSame($article->id, $visible->id);
        $this->assertSame(1, NewsArticle::forAllSites()->count());
        $this->assertSame('Site de origem', $visible->load('site')->attributionSite()?->name);

        $admin->sites()->syncWithoutDetaching([$targetSite->id => ['permissions' => json_encode([]), 'is_active' => true]]);
        $this->actingAs($admin)->get('http://destino.test/noticias/noticia-original')->assertOk()
            ->assertSee('originalmente publicado por')->assertSee('Site de origem');
        $this->actingAs($admin)->get('http://destino.test/admin/news/'.$article->id.'/edit')->assertNotFound();
    }

    public function test_copy_is_independent_draft_with_origin_and_duplicate_is_blocked(): void
    {
        [$sourceSite, $targetSite] = $this->sites();
        app()->instance('currentSite', $sourceSite);
        $admin = $this->admin();
        $article = $this->article($admin, 'Copiar notícia', 'copiar-noticia');
        $distribution = app(NewsDistributionService::class)->distribute($article, $targetSite, 'copy', $admin);
        $copy = NewsArticle::forAllSites()->findOrFail($distribution->target_article_id);

        $this->assertSame('draft', $copy->status);
        $this->assertSame($article->id, $copy->origin_article_id);
        $this->assertSame($sourceSite->id, $copy->origin_site_id);
        $this->assertSame($targetSite->id, $copy->site_id);
        $this->assertNotSame($article->slug, $copy->slug);

        $this->expectException(ValidationException::class);
        app(NewsDistributionService::class)->distribute($article, $targetSite, 'copy', $admin);
    }

    public function test_admin_can_distribute_and_unique_constraint_prevents_accidental_duplicate(): void
    {
        [$sourceSite, $targetSite] = $this->sites();
        app()->instance('currentSite', $sourceSite);
        $admin = $this->admin();
        $article = $this->article($admin, 'Distribuição administrativa', 'distribuicao-administrativa');

        $this->actingAs($admin)->post('http://origem.test/admin/news/'.$article->id.'/distribuir', [
            'target_site_id' => $targetSite->id, 'mode' => 'reference',
        ])->assertRedirect();
        $this->assertDatabaseHas('news_distributions', [
            'source_article_id' => $article->id, 'target_site_id' => $targetSite->id, 'mode' => 'reference',
        ]);
        $this->assertSame(1, NewsDistribution::count());

        $this->actingAs($admin)->post('http://origem.test/admin/news/'.$article->id.'/distribuir', [
            'target_site_id' => $targetSite->id, 'mode' => 'reference',
        ])->assertSessionHasErrors('target_site_id');
        $this->assertSame(1, NewsDistribution::count());
    }

    private function article(User $author, string $title, string $slug): NewsArticle
    {
        return NewsArticle::create([
            'author_id' => $author->id, 'title' => $title, 'slug' => $slug, 'body' => 'Conteúdo',
            'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
    }

    private function admin(): User
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }

    private function sites(): array
    {
        $source = Site::where('is_default', true)->firstOrFail();
        $source->update(['name' => 'Site de origem', 'slug' => 'origem']);
        $source->domains()->delete();
        SiteDomain::create(['site_id' => $source->id, 'domain' => 'origem.test', 'is_primary' => true]);
        $target = Site::create([
            'name' => 'Site de destino', 'slug' => 'destino', 'theme_primary' => '#0067c0',
            'theme_secondary' => '#004e8c', 'is_active' => true,
        ]);
        SiteDomain::create(['site_id' => $target->id, 'domain' => 'destino.test', 'is_primary' => true]);

        return [$source, $target];
    }
}
