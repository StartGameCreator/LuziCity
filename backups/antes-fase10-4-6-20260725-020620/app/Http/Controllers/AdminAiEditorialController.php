<?php

namespace App\Http\Controllers;

use App\Models\AiExecution;
use App\Models\AiPromptTemplate;
use App\Models\AiProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiEditorialController extends Controller
{
    public function index(): View
    {
        $providers = AiProvider::query()
            ->withCount([
                'executions',
                'executions as successful_executions_count' => fn ($query) => $query->where('status', 'completed'),
                'executions as failed_executions_count' => fn ($query) => $query->where('status', 'failed'),
            ])
            ->orderBy('id')
            ->get();

        return view('admin.ai-editorial.index', [
            'providers' => $providers,
            'templates' => AiPromptTemplate::query()->orderBy('purpose')->orderBy('name')->get(),
            'executions' => AiExecution::query()
                ->with(['provider:id,name,slug', 'promptTemplate:id,name,key', 'user:id,name'])
                ->latest()
                ->limit(30)
                ->get(),
            'stats' => [
                'total' => AiExecution::query()->count(),
                'completed' => AiExecution::query()->where('status', 'completed')->count(),
                'failed' => AiExecution::query()->where('status', 'failed')->count(),
                'today' => AiExecution::query()->whereDate('created_at', today())->count(),
                'average_ms' => (int) round((float) AiExecution::query()->where('status', 'completed')->avg('duration_ms')),
            ],
        ]);
    }

    public function updateProvider(Request $request, AiProvider $provider): RedirectResponse
    {
        $data = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'model' => ['nullable', 'string', 'max:160'],
            'endpoint' => ['nullable', 'url', 'max:2048'],
            'monthly_budget_reais' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'daily_request_limit' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $provider->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'model' => filled($data['model'] ?? null) ? trim($data['model']) : null,
            'endpoint' => filled($data['endpoint'] ?? null) ? trim($data['endpoint']) : null,
            'monthly_budget_cents' => (int) round(((float) ($data['monthly_budget_reais'] ?? 0)) * 100),
            'daily_request_limit' => $data['daily_request_limit'],
        ]);

        return back()->with('status', "Provedor {$provider->name} atualizado.");
    }

    public function updateTemplate(Request $request, AiPromptTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'system_prompt' => ['required', 'string', 'max:20000'],
            'user_template' => ['required', 'string', 'max:50000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $template->update([
            'system_prompt' => $data['system_prompt'],
            'user_template' => $data['user_template'],
            'is_active' => $request->boolean('is_active'),
            'version' => $template->version + 1,
        ]);

        return back()->with('status', "Prompt {$template->name} atualizado para a versão {$template->version}.");
    }
}
