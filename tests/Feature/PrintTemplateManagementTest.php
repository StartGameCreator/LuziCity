<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\PrintEdition;
use App\Models\PrintTemplate;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_template_with_cover_internal_pages_ads_and_credits(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/impresso/templates', [
            'name' => 'Jornal clássico',
            'cover_style' => 'classic',
            'cover_columns' => 3,
            'internal_style' => 'columns',
            'internal_columns' => 4,
            'show_page_numbers' => 1,
            'is_default' => 1,
            'credits' => "Direção: Luzicity\nRedação: Equipe editorial",
            'slot_names' => ['Rodapé da capa', 'Página inteira'],
            'slot_page_types' => ['cover', 'internal'],
            'slot_placements' => ['bottom', 'full_page'],
            'slot_sizes' => ['banner', 'full'],
        ]);

        $template = PrintTemplate::with('adSlots')->firstOrFail();
        $response->assertRedirect(route('admin.print-templates.edit', $template));
        $this->assertTrue($template->is_default);
        $this->assertSame(3, $template->cover_columns);
        $this->assertSame(4, $template->internal_columns);
        $this->assertCount(2, $template->adSlots);
        $this->assertSame('internal', $template->adSlots->last()->page_type);
        $this->assertStringContainsString('Equipe editorial', $template->credits);
    }

    public function test_edition_can_use_a_template_from_the_current_site(): void
    {
        $admin = $this->admin();
        $site = Site::where('is_default', true)->firstOrFail();
        app()->instance('currentSite', $site);
        $template = PrintTemplate::create($this->templateData('Revista moderna'));
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'title' => 'Capa da edição', 'slug' => 'capa-da-edicao',
            'body' => 'Conteúdo', 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);

        $this->actingAs($admin)->post('/admin/impresso/edicoes', [
            'title' => 'Edição com template',
            'edition_date' => '2026-07-28',
            'pdf_format' => 'magazine',
            'bleed_mm' => 3,
            'high_resolution_images' => 1,
            'print_template_id' => $template->id,
            'article_ids' => [$article->id],
            'sections' => [$article->id => 'Capa'],
            'positions' => [$article->id => 0],
        ])->assertRedirect();

        $this->assertSame($template->id, PrintEdition::firstOrFail()->print_template_id);
    }

    public function test_only_one_template_is_default_per_site(): void
    {
        $admin = $this->admin();
        $first = PrintTemplate::create([...$this->templateData('Primeiro'), 'is_default' => true]);

        $this->actingAs($admin)->post('/admin/impresso/templates', [
            ...$this->templateData('Segundo'),
            'is_default' => 1,
        ])->assertRedirect();

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue(PrintTemplate::where('name', 'Segundo')->firstOrFail()->is_default);
    }

    private function templateData(string $name): array
    {
        return [
            'name' => $name,
            'cover_style' => 'modern',
            'cover_columns' => 2,
            'internal_style' => 'magazine',
            'internal_columns' => 3,
            'show_page_numbers' => true,
            'is_default' => false,
        ];
    }

    private function admin(): User
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }
}
