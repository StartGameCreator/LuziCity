<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\PrintEdition;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintEditionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_an_edition_with_sections_selected_news_and_order(): void
    {
        $admin = $this->admin();
        [$first, $second] = $this->articles($admin);

        $response = $this->actingAs($admin)->post('/admin/impresso/edicoes', [
            'title' => 'Edição de domingo',
            'edition_date' => '2026-07-27',
            'pdf_format' => 'a4',
            'bleed_mm' => 3,
            'high_resolution_images' => 1,
            'article_ids' => [$first->id, $second->id],
            'sections' => [$first->id => 'Cidade', $second->id => 'Cidade'],
            'positions' => [$first->id => 2, $second->id => 1],
        ]);

        $edition = PrintEdition::with('sections.items')->firstOrFail();
        $response->assertRedirect(route('admin.print-editions.edit', $edition));
        $this->assertSame('Edição de domingo', $edition->title);
        $this->assertCount(1, $edition->sections);
        $this->assertSame([$second->id, $first->id], $edition->sections->first()->items->pluck('news_article_id')->all());
    }

    public function test_edition_requires_news_and_a_section_for_each_selected_article(): void
    {
        $admin = $this->admin();
        [$article] = $this->articles($admin);

        $this->actingAs($admin)->post('/admin/impresso/edicoes', [
            'title' => 'Edição incompleta',
            'edition_date' => '2026-07-27',
            'pdf_format' => 'a4',
            'bleed_mm' => 3,
            'article_ids' => [$article->id],
            'sections' => [$article->id => ''],
        ])->assertSessionHasErrors(["sections.{$article->id}"]);
    }

    public function test_guest_cannot_access_print_edition_administration(): void
    {
        $this->get('/admin/impresso/edicoes')->assertRedirect('/login');
    }

    private function admin(): User
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }

    private function articles(User $author): array
    {
        $site = Site::where('is_default', true)->firstOrFail();
        app()->instance('currentSite', $site);

        return [
            NewsArticle::create([
                'author_id' => $author->id, 'title' => 'Primeira matéria', 'slug' => 'primeira-materia',
                'body' => 'Conteúdo', 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
            ]),
            NewsArticle::create([
                'author_id' => $author->id, 'title' => 'Segunda matéria', 'slug' => 'segunda-materia',
                'body' => 'Conteúdo', 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
            ]),
        ];
    }
}
