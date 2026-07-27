<?php

namespace App\Http\Controllers;

use App\Models\PrintTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPrintTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.print-templates.index', [
            'templates' => PrintTemplate::withCount(['adSlots', 'editions'])->orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.print-templates.form', ['template' => new PrintTemplate()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $template = DB::transaction(function () use ($request): PrintTemplate {
            $data = $this->validated($request);
            $this->clearDefaultWhenNeeded($request);
            $template = PrintTemplate::create($data);
            $this->syncAdSlots($template, $request);

            return $template;
        });

        return redirect()->route('admin.print-templates.edit', $template)->with('status', 'Template criado.');
    }

    public function edit(PrintTemplate $printTemplate): View
    {
        return view('admin.print-templates.form', ['template' => $printTemplate->load('adSlots')]);
    }

    public function update(Request $request, PrintTemplate $printTemplate): RedirectResponse
    {
        DB::transaction(function () use ($request, $printTemplate): void {
            $this->clearDefaultWhenNeeded($request, $printTemplate);
            $printTemplate->update($this->validated($request, $printTemplate));
            $printTemplate->adSlots()->delete();
            $this->syncAdSlots($printTemplate, $request);
        });

        return back()->with('status', 'Template atualizado.');
    }

    public function destroy(PrintTemplate $printTemplate): RedirectResponse
    {
        $printTemplate->delete();

        return redirect()->route('admin.print-templates.index')->with('status', 'Template removido.');
    }

    private function validated(Request $request, ?PrintTemplate $template = null): array
    {
        $siteId = app('currentSite')?->id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('print_templates')->where('site_id', $siteId)->ignore($template)],
            'cover_style' => ['required', Rule::in(['classic', 'modern', 'minimal'])],
            'cover_columns' => ['required', 'integer', 'min:1', 'max:4'],
            'internal_style' => ['required', Rule::in(['columns', 'magazine', 'compact'])],
            'internal_columns' => ['required', 'integer', 'min:1', 'max:4'],
            'credits' => ['nullable', 'string', 'max:10000'],
            'slot_names' => ['nullable', 'array', 'max:20'],
            'slot_names.*' => ['nullable', 'string', 'max:120'],
            'slot_page_types.*' => ['nullable', Rule::in(['cover', 'internal', 'back_cover'])],
            'slot_placements.*' => ['nullable', Rule::in(['top', 'bottom', 'sidebar', 'full_page', 'half_page'])],
            'slot_sizes.*' => ['nullable', Rule::in(['full', 'half', 'quarter', 'banner'])],
        ]);

        return [
            ...$data,
            'show_page_numbers' => $request->boolean('show_page_numbers'),
            'is_default' => $request->boolean('is_default'),
        ];
    }

    private function clearDefaultWhenNeeded(Request $request, ?PrintTemplate $except = null): void
    {
        if ($request->boolean('is_default')) {
            PrintTemplate::query()->when($except, fn ($query) => $query->whereKeyNot($except->id))->update(['is_default' => false]);
        }
    }

    private function syncAdSlots(PrintTemplate $template, Request $request): void
    {
        foreach ($request->input('slot_names', []) as $position => $name) {
            if (! filled($name)) {
                continue;
            }
            $template->adSlots()->create([
                'name' => $name,
                'page_type' => $request->input("slot_page_types.$position", 'internal'),
                'placement' => $request->input("slot_placements.$position", 'bottom'),
                'size' => $request->input("slot_sizes.$position", 'banner'),
                'position' => $position,
            ]);
        }
    }
}
