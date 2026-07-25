<?php

namespace App\Http\Controllers;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptVersion;
use App\Services\AI\AiEditorialManager;
use App\Services\AI\AiPromptVersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAiPromptController extends Controller
{
    public function index(Request $request): View
    {
        $templates = AiPromptTemplate::query()->withCount('versions')
            ->when($request->filled('purpose'), fn ($q) => $q->where('purpose', $request->string('purpose')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$request->string('q').'%')->orWhere('key', 'like', '%'.$request->string('q').'%')))
            ->orderBy('purpose')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.ai.prompts.index', compact('templates') + ['purposes' => AiPromptVersionService::PURPOSES]);
    }

    public function create(): View
    {
        return view('admin.ai.prompts.form', ['template' => new AiPromptTemplate(), 'purposes' => AiPromptVersionService::PURPOSES, 'allowedVariables' => AiPromptVersionService::ALLOWED_VARIABLES]);
    }

    public function store(Request $request, AiPromptVersionService $service): RedirectResponse
    {
        $template = $service->create($this->validated($request), $request->user()->id);
        return redirect()->route('admin.ai.prompts.show', $template)->with('status', 'Prompt criado e versão inicial registrada.');
    }

    public function show(AiPromptTemplate $prompt): View
    {
        $prompt->load(['versions' => fn ($q) => $q->with('author:id,name')->latest('version')]);
        return view('admin.ai.prompts.show', compact('prompt'));
    }

    public function edit(AiPromptTemplate $prompt): View
    {
        return view('admin.ai.prompts.form', ['template' => $prompt, 'purposes' => AiPromptVersionService::PURPOSES, 'allowedVariables' => AiPromptVersionService::ALLOWED_VARIABLES]);
    }

    public function update(Request $request, AiPromptTemplate $prompt, AiPromptVersionService $service): RedirectResponse
    {
        $service->update($prompt, $this->validated($request, $prompt), $request->user()->id);
        return redirect()->route('admin.ai.prompts.show', $prompt)->with('status', 'Nova versão criada.');
    }

    public function duplicate(Request $request, AiPromptTemplate $prompt, AiPromptVersionService $service): RedirectResponse
    {
        $copy = $service->create([
            'key' => $prompt->key.'.copy.'.now()->format('YmdHis'), 'name' => $prompt->name.' (cópia)',
            'purpose' => $prompt->purpose, 'system_prompt' => $prompt->system_prompt,
            'user_template' => $prompt->user_template, 'output_schema' => $prompt->output_schema,
            'is_active' => false, 'is_default' => false, 'change_notes' => "Duplicado de {$prompt->key}.",
        ], $request->user()->id);
        return redirect()->route('admin.ai.prompts.edit', $copy)->with('status', 'Cópia criada inativa. Revise antes de ativar.');
    }

    public function toggle(AiPromptTemplate $prompt): RedirectResponse
    {
        $prompt->update(['is_active' => ! $prompt->is_active]);
        return back()->with('status', $prompt->is_active ? 'Prompt ativado.' : 'Prompt desativado.');
    }

    public function test(Request $request, AiPromptTemplate $prompt, AiPromptVersionService $service, AiEditorialManager $manager): View
    {
        $values = collect(AiPromptVersionService::ALLOWED_VARIABLES)->mapWithKeys(fn ($key) => [$key => (string) $request->input("variables.$key", "[$key]")])->all();
        return view('admin.ai.prompts.test', [
            'prompt' => $prompt, 'variables' => $service->placeholders($prompt->system_prompt."\n".$prompt->user_template),
            'values' => $values, 'renderedSystem' => $manager->render($prompt->system_prompt, $values),
            'renderedUser' => $manager->render($prompt->user_template, $values),
        ]);
    }

    public function restore(Request $request, AiPromptTemplate $prompt, AiPromptVersion $version, AiPromptVersionService $service): RedirectResponse
    {
        $service->restore($prompt, $version, $request->user()->id);
        return back()->with('status', "Versão {$version->version} restaurada como uma nova versão.");
    }

    public function compare(Request $request, AiPromptTemplate $prompt): View
    {
        $data = $request->validate(['from' => ['required', 'integer'], 'to' => ['required', 'integer', 'different:from']]);
        $versions = $prompt->versions()->whereIn('version', [$data['from'], $data['to']])->get()->keyBy('version');
        abort_unless($versions->count() === 2, 404);
        return view('admin.ai.prompts.versions', ['prompt' => $prompt, 'from' => $versions[$data['from']], 'to' => $versions[$data['to']]]);
    }

    private function validated(Request $request, ?AiPromptTemplate $template = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/', Rule::unique('ai_prompt_templates', 'key')->ignore($template)],
            'name' => ['required', 'string', 'max:160'], 'purpose' => ['required', Rule::in(array_keys(AiPromptVersionService::PURPOSES))],
            'system_prompt' => ['required', 'string', 'max:20000'], 'user_template' => ['required', 'string', 'max:50000'],
            'output_schema_json' => ['nullable', 'json'], 'change_notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'], 'is_default' => ['nullable', 'boolean'],
        ]);
        $data['output_schema'] = filled($data['output_schema_json'] ?? null) ? json_decode($data['output_schema_json'], true, 512, JSON_THROW_ON_ERROR) : null;
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');
        return $data;
    }
}
