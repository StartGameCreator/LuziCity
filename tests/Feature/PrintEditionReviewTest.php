<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\PrintEdition;
use App\Models\PrintTemplate;
use App\Models\Site;
use App\Models\User;
use App\Services\PrintEditionPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintEditionReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_preview_reports_page_count_and_overflow_warning(): void
    {
        [$admin, $edition] = $this->edition(str_repeat('Texto editorial extenso. ', 500));

        $review = app(PrintEditionPdfService::class)->review($edition);
        $this->assertSame(3, $review['page_count']);
        $this->assertTrue($review['has_warnings']);
        $this->assertStringContainsString('Texto excedente', $review['warnings'][0]['message']);

        $this->actingAs($admin)->get(route('admin.print-editions.preview', $edition))
            ->assertOk()->assertSee('3 página(s)')->assertSee('Texto excedente');
    }

    public function test_warning_must_be_acknowledged_before_final_approval(): void
    {
        [$admin, $edition] = $this->edition(str_repeat('Conteúdo muito longo. ', 600));

        $this->actingAs($admin)->post(route('admin.print-editions.approve', $edition))
            ->assertSessionHasErrors('acknowledge_warnings');

        $this->actingAs($admin)->post(route('admin.print-editions.approve', $edition), [
            'acknowledge_warnings' => 1,
        ])->assertRedirect();

        $edition->refresh();
        $this->assertTrue($edition->isApproved());
        $this->assertSame($admin->id, $edition->approved_by);
        $this->assertSame(3, $edition->pdf_page_count);
        Storage::disk('local')->assertExists($edition->approved_pdf_path);
        $this->assertSame(
            hash('sha256', Storage::disk('local')->get($edition->approved_pdf_path)),
            $edition->approved_pdf_sha256,
        );
    }

    public function test_approved_edition_is_locked_until_an_admin_reopens_it(): void
    {
        [$admin, $edition] = $this->edition('Conteúdo curto.');
        $this->actingAs($admin)->post(route('admin.print-editions.approve', $edition))->assertRedirect();

        $this->actingAs($admin)->put(route('admin.print-editions.update', $edition), [])
            ->assertStatus(423);
        $this->actingAs($admin)->delete(route('admin.print-editions.destroy', $edition))
            ->assertStatus(423);

        $this->actingAs($admin)->post(route('admin.print-editions.reopen', $edition))->assertRedirect();
        $this->assertSame('draft', $edition->refresh()->review_status);
        $this->assertNull($edition->approved_at);
    }

    public function test_editor_can_submit_review_but_cannot_approve(): void
    {
        [$admin, $edition] = $this->edition('Conteúdo revisado.');
        Role::findOrCreate('Jornalista');
        $journalist = User::factory()->create();
        $journalist->assignRole('Jornalista');

        $this->actingAs($journalist)->post(route('admin.print-editions.review', $edition), [
            'review_notes' => 'Pauta conferida pela redação.',
        ])->assertRedirect();
        $this->assertSame('review', $edition->refresh()->review_status);

        $this->actingAs($journalist)->post(route('admin.print-editions.approve', $edition))->assertForbidden();
        $this->assertSame('review', $edition->refresh()->review_status);
    }

    private function edition(string $body): array
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $site = Site::where('is_default', true)->firstOrFail();
        app()->instance('currentSite', $site);
        $template = PrintTemplate::create([
            'name' => 'Template revisão '.uniqid(),
            'cover_style' => 'classic', 'cover_columns' => 3,
            'internal_style' => 'columns', 'internal_columns' => 3,
            'credits' => 'Expediente da edição.', 'show_page_numbers' => true, 'is_default' => true,
        ]);
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'title' => 'Matéria em revisão',
            'slug' => 'materia-revisao-'.uniqid(), 'body' => $body,
            'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        $edition = PrintEdition::create([
            'created_by' => $admin->id, 'print_template_id' => $template->id,
            'title' => 'Edição para revisão', 'edition_date' => '2026-07-30',
            'pdf_format' => 'a4', 'bleed_mm' => 3, 'high_resolution_images' => true,
        ]);
        $section = $edition->sections()->create(['name' => 'Cidade', 'position' => 0]);
        $section->items()->create(['news_article_id' => $article->id, 'position' => 0]);

        return [$admin, $edition];
    }
}
