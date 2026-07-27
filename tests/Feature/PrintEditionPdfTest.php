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

class PrintEditionPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_contains_bleed_trim_box_high_resolution_image_and_credits(): void
    {
        [$admin, $edition] = $this->edition('a4');

        $response = $this->actingAs($admin)->get(route('admin.print-editions.pdf', $edition));
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $pdf = $response->getContent();
        $samplePath = base_path('output/pdf/fase-19-3-edicao-a4.pdf');
        if (! is_dir(dirname($samplePath))) {
            mkdir(dirname($samplePath), 0777, true);
        }
        file_put_contents($samplePath, $pdf);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('/TrimBox', $pdf);
        $this->assertStringContainsString('/DCTDecode', $pdf);
        $this->assertStringContainsString('/Width 1200 /Height 800', $pdf);
        $this->assertStringContainsString('EXPEDIENTE E CREDITOS', $pdf);
        $this->assertNotNull($edition->refresh()->pdf_generated_at);
    }

    public function test_generator_supports_a4_tabloid_and_magazine_page_sizes(): void
    {
        foreach (['a4', 'tabloid', 'magazine'] as $format) {
            [, $edition] = $this->edition($format, false);
            $pdf = app(PrintEditionPdfService::class)->generate($edition);
            $this->assertStringContainsString('/MediaBox [0 0 ', $pdf, "Formato {$format} sem MediaBox.");
            $this->assertStringContainsString('/TrimBox [', $pdf, "Formato {$format} sem TrimBox.");
        }
    }

    public function test_pdf_requires_a_template(): void
    {
        [$admin, $edition] = $this->edition('a4');
        $edition->update(['print_template_id' => null]);

        $this->actingAs($admin)->get(route('admin.print-editions.pdf', $edition))->assertStatus(422);
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete('print-tests/high-resolution.jpg');
        parent::tearDown();
    }

    private function edition(string $format, bool $withImage = true): array
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $site = Site::where('is_default', true)->firstOrFail();
        app()->instance('currentSite', $site);
        $template = PrintTemplate::create([
            'name' => 'Template '.$format.' '.uniqid(),
            'cover_style' => 'modern', 'cover_columns' => 3,
            'internal_style' => 'columns', 'internal_columns' => 3,
            'credits' => 'Direção: Luzicity. Redação: Equipe editorial.',
            'show_page_numbers' => true, 'is_default' => true,
        ]);
        $template->adSlots()->create([
            'name' => 'Rodapé', 'page_type' => 'cover', 'placement' => 'bottom', 'size' => 'banner', 'position' => 0,
        ]);
        $coverPath = null;
        if ($withImage) {
            $image = imagecreatetruecolor(1200, 800);
            $blue = imagecolorallocate($image, 0, 103, 192);
            imagefill($image, 0, 0, $blue);
            ob_start();
            imagejpeg($image, null, 96);
            Storage::disk('public')->put('print-tests/high-resolution.jpg', ob_get_clean());
            imagedestroy($image);
            $coverPath = 'storage/print-tests/high-resolution.jpg';
        }
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'title' => 'A cidade ganha uma nova edição impressa',
            'subtitle' => 'Projeto reúne informação local e design editorial.',
            'slug' => 'edicao-impressa-'.$format.'-'.uniqid(), 'excerpt' => 'Notícias selecionadas para os leitores.',
            'body' => str_repeat('Conteúdo jornalístico revisado e preparado para diagramação. ', 80),
            'cover_image_path' => $coverPath, 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        $edition = PrintEdition::create([
            'created_by' => $admin->id, 'print_template_id' => $template->id,
            'title' => 'Luzicity - Edição especial', 'edition_date' => '2026-07-29',
            'pdf_format' => $format, 'bleed_mm' => 3, 'high_resolution_images' => true,
        ]);
        $section = $edition->sections()->create(['name' => 'Cidade', 'position' => 0]);
        $section->items()->create(['news_article_id' => $article->id, 'position' => 0]);

        return [$admin, $edition];
    }
}
