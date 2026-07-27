<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\PrintEdition;
use App\Models\PrintTemplate;
use App\Services\PrintEditionPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminPrintEditionController extends Controller
{
    public function index(): View
    {
        return view('admin.print-editions.index', [
            'editions' => PrintEdition::withCount('sections')->latest('edition_date')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new PrintEdition(['edition_date' => today()]));
    }

    public function store(Request $request): RedirectResponse
    {
        $edition = DB::transaction(function () use ($request): PrintEdition {
            $edition = PrintEdition::create([
                ...$this->validatedEdition($request),
                'created_by' => $request->user()->id,
            ]);
            $this->syncContents($edition, $request);

            return $edition;
        });

        return redirect()->route('admin.print-editions.edit', $edition)
            ->with('status', 'Edição criada e pauta ordenada.');
    }

    public function edit(PrintEdition $printEdition): View
    {
        return $this->formView($printEdition->load('sections.items.article'));
    }

    public function update(Request $request, PrintEdition $printEdition): RedirectResponse
    {
        abort_if($printEdition->isApproved(), 423, 'A edição aprovada precisa ser reaberta antes de ser alterada.');
        DB::transaction(function () use ($request, $printEdition): void {
            $printEdition->update($this->validatedEdition($request));
            $printEdition->sections()->delete();
            $this->syncContents($printEdition, $request);
        });

        return back()->with('status', 'Edição atualizada.');
    }

    public function destroy(PrintEdition $printEdition): RedirectResponse
    {
        abort_if($printEdition->isApproved(), 423, 'Uma edição aprovada não pode ser removida.');
        $printEdition->delete();

        return redirect()->route('admin.print-editions.index')->with('status', 'Edição removida.');
    }

    public function pdf(PrintEdition $printEdition, PrintEditionPdfService $pdf): Response
    {
        abort_unless($printEdition->print_template_id, 422, 'Selecione um template antes de gerar o PDF.');
        $document = $printEdition->isApproved() && $printEdition->approved_pdf_path
            && Storage::disk('local')->exists($printEdition->approved_pdf_path)
                ? Storage::disk('local')->get($printEdition->approved_pdf_path)
                : $pdf->generate($printEdition);
        $review = $pdf->review($printEdition);
        $printEdition->update(['pdf_generated_at' => now(), 'pdf_page_count' => $review['page_count']]);
        $filename = Str::slug($printEdition->title).'-'.$printEdition->edition_date->format('Y-m-d').'.pdf';

        return response($document, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Content-Length' => strlen($document),
        ]);
    }

    public function preview(PrintEdition $printEdition, PrintEditionPdfService $pdf): View
    {
        return view('admin.print-editions.preview', [
            'edition' => $printEdition->load('template', 'approver', 'sections.items.article'),
            'review' => $pdf->review($printEdition),
        ]);
    }

    public function submitReview(Request $request, PrintEdition $printEdition, PrintEditionPdfService $pdf): RedirectResponse
    {
        abort_if($printEdition->isApproved(), 423, 'A edição já foi aprovada.');
        $data = $request->validate(['review_notes' => ['nullable', 'string', 'max:10000']]);
        $review = $pdf->review($printEdition);
        if ($review['has_errors']) {
            throw ValidationException::withMessages(['review' => 'Corrija os erros da prévia antes de enviar para aprovação.']);
        }
        $printEdition->update([
            'review_status' => 'review',
            'review_notes' => $data['review_notes'] ?? null,
            'pdf_page_count' => $review['page_count'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('status', 'Edição enviada para aprovação final.');
    }

    public function approve(Request $request, PrintEdition $printEdition, PrintEditionPdfService $pdf): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
        $review = $pdf->review($printEdition);
        if ($review['has_errors']) {
            throw ValidationException::withMessages(['approval' => 'A edição possui erros que impedem a aprovação.']);
        }
        if ($review['has_warnings'] && ! $request->boolean('acknowledge_warnings')) {
            throw ValidationException::withMessages(['acknowledge_warnings' => 'Confirme que os alertas foram revisados.']);
        }
        $document = $pdf->generate($printEdition);
        $snapshotPath = "print-editions/{$printEdition->site_id}/{$printEdition->id}/approved.pdf";
        Storage::disk('local')->put($snapshotPath, $document);
        $printEdition->update([
            'review_status' => 'approved',
            'pdf_page_count' => $review['page_count'],
            'pdf_generated_at' => now(),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approved_pdf_path' => $snapshotPath,
            'approved_pdf_sha256' => hash('sha256', $document),
        ]);

        return back()->with('status', 'Edição aprovada e bloqueada para alterações.');
    }

    public function reopen(Request $request, PrintEdition $printEdition): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
        $printEdition->update([
            'review_status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'approved_pdf_path' => null,
            'approved_pdf_sha256' => null,
        ]);

        return back()->with('status', 'Edição reaberta para ajustes.');
    }

    private function formView(PrintEdition $edition): View
    {
        return view('admin.print-editions.form', [
            'edition' => $edition,
            'articles' => NewsArticle::query()->published()->latest('published_at')->limit(150)->get(),
            'templates' => PrintTemplate::orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    private function validatedEdition(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'edition_date' => ['required', 'date'],
            'pdf_format' => ['required', Rule::in(['a4', 'tabloid', 'magazine'])],
            'bleed_mm' => ['required', 'numeric', 'min:0', 'max:10'],
            'print_template_id' => [
                'nullable',
                Rule::exists('print_templates', 'id')->where('site_id', app('currentSite')?->id),
            ],
            'article_ids' => ['required', 'array', 'min:1'],
            'article_ids.*' => [
                'integer',
                Rule::exists('news_articles', 'id')->where(
                    fn ($query) => $query->where('site_id', app('currentSite')?->id),
                ),
            ],
            'sections' => ['required', 'array'],
            'sections.*' => ['required', 'string', 'max:120'],
            'positions' => ['nullable', 'array'],
            'positions.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'article_ids.required' => 'Selecione pelo menos uma notícia.',
            'sections.*.required' => 'Informe a seção de cada notícia selecionada.',
        ]) + ['high_resolution_images' => $request->boolean('high_resolution_images')];
    }

    private function syncContents(PrintEdition $edition, Request $request): void
    {
        $sectionPositions = [];

        foreach ($request->input('article_ids', []) as $articleId) {
            $name = trim((string) $request->input("sections.$articleId"));
            if (! array_key_exists($name, $sectionPositions)) {
                $sectionPositions[$name] = count($sectionPositions);
            }

            $section = $edition->sections()->firstOrCreate(
                ['name' => $name],
                ['position' => $sectionPositions[$name]],
            );
            $section->items()->create([
                'news_article_id' => $articleId,
                'position' => (int) $request->input("positions.$articleId", 0),
            ]);
        }
    }
}
