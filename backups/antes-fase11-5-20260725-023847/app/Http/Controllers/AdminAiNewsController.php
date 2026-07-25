<?php

namespace App\Http\Controllers;

use App\Models\AiEditorialProfile;
use App\Services\AI\AiNewsGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminAiNewsController extends Controller
{
    public function create(): View
    {
        $this->authorizeEditor();

        return view('admin.news.ai-create', [
            'profile' => AiEditorialProfile::query()->where('is_default', true)->first(),
        ]);
    }

    public function generate(Request $request, AiNewsGenerator $generator): JsonResponse
    {
        $this->authorizeEditor();

        $data = $request->validate([
            'provider' => ['required', 'in:chatgpt,gemini,copilot'],
            'working_title' => ['nullable', 'string', 'max:180'],
            'brief' => ['required', 'string', 'min:20', 'max:20000'],
            'source_text' => ['nullable', 'string', 'max:60000'],
            'source_url' => ['nullable', 'url', 'max:2048'],
        ]);

        try {
            return response()->json([
                'ok' => true,
                'article' => $generator->generate($data),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);

        $profile = AiEditorialProfile::query()->where('is_default', true)->firstOrFail();

        $data = $request->validate([
            'tone' => ['required', 'string', 'max:180'],
            'max_title_length' => ['required', 'integer', 'min:30', 'max:180'],
            'max_excerpt_length' => ['required', 'integer', 'min:80', 'max:600'],
            'editorial_rules' => ['required', 'string', 'max:10000'],
        ]);

        $profile->update([
            ...$data,
            'require_source_attribution' => $request->boolean('require_source_attribution'),
            'avoid_sensationalism' => $request->boolean('avoid_sensationalism'),
            'human_review_required' => true,
        ]);

        return back()->with('status', 'Memória editorial atualizada.');
    }

    private function authorizeEditor(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['Super Admin', 'Admin', 'Jornalista', 'Colunista']),
            403
        );
    }
}
