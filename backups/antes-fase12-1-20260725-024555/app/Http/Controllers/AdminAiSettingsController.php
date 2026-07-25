<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AiWritingAssistant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAiSettingsController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.ai-settings.edit', [
            'aiSettings' => Setting::aiSettings(),
            'keyStatus' => $this->keyStatus(Setting::aiSettings()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'ai_provider' => ['required', 'in:chatgpt,gemini,copilot'],
            'openai_api_key' => ['nullable', 'string', 'max:4000'],
            'openai_key_file' => ['nullable', 'file', 'max:256'],
            'chatgpt_model' => ['nullable', 'string', 'max:120'],
            'gemini_api_key' => ['nullable', 'string', 'max:4000'],
            'gemini_key_file' => ['nullable', 'file', 'max:256'],
            'gemini_model' => ['nullable', 'string', 'max:120'],
            'copilot_api_key' => ['nullable', 'string', 'max:4000'],
            'copilot_key_file' => ['nullable', 'file', 'max:256'],
            'copilot_endpoint' => ['nullable', 'url', 'max:2048'],
        ]);

        $aiValues = [
            'provider' => $data['ai_provider'],
            'openai_api_key' => $this->valueFromInputOrFile($request, 'openai_api_key', 'openai_key_file', ['OPENAI_API_KEY', 'api_key', 'key']),
            'chatgpt_model' => $data['chatgpt_model'] ?? null,
            'gemini_api_key' => $this->valueFromInputOrFile($request, 'gemini_api_key', 'gemini_key_file', ['GEMINI_API_KEY', 'api_key', 'key']),
            'gemini_model' => $data['gemini_model'] ?? null,
            'copilot_api_key' => $this->valueFromInputOrFile($request, 'copilot_api_key', 'copilot_key_file', ['COPILOT_API_KEY', 'api_key', 'key', 'token']),
            'copilot_endpoint' => $data['copilot_endpoint'] ?? null,
        ];

        foreach ($aiValues as $key => $value) {
            if ((str_ends_with($key, '_api_key') || $key === 'copilot_endpoint') && blank($value)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['group' => 'ai', 'key' => $key],
                ['value' => (string) $value]
            );
        }

        return back()->with('status', 'Chaves de IA atualizadas.');
    }

    public function test(Request $request, AiWritingAssistant $assistant): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'provider' => ['required', 'in:chatgpt,gemini,copilot'],
        ]);

        $result = $assistant->draftResult(
            'news_summary',
            'Teste de conexao da Luzicity. Responda com uma frase curta confirmando que a IA esta funcionando.',
            'Teste de IA',
            $data['provider']
        );

        return back()->with('ai_test', [
            'provider' => $data['provider'],
            'ok' => ($result['source'] ?? 'local') !== 'local',
            'message' => $result['message'] ?? 'Teste concluido.',
            'text' => $result['text'] ?? '',
        ]);
    }

    private function valueFromInputOrFile(Request $request, string $input, string $file, array $jsonKeys): ?string
    {
        $value = trim((string) $request->input($input, ''));

        if ($request->hasFile($file)) {
            $fileContent = trim((string) file_get_contents($request->file($file)->getRealPath()));
            $value = $this->extractKeyFromFile($fileContent, $jsonKeys) ?: $fileContent;
        }

        return filled($value) ? $value : null;
    }

    private function extractKeyFromFile(string $content, array $keys): ?string
    {
        $json = json_decode($content, true);

        if (is_array($json)) {
            foreach ($keys as $key) {
                $value = data_get($json, $key);

                if (filled($value)) {
                    return trim((string) $value);
                }
            }
        }

        foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
            foreach ($keys as $key) {
                if (Str::startsWith(trim($line), $key.'=')) {
                    return trim(Str::after($line, '='));
                }
            }
        }

        return null;
    }

    private function keyStatus(array $settings): array
    {
        return [
            'chatgpt' => $this->maskKey($settings['openai_api_key'] ?? ''),
            'gemini' => $this->maskKey($settings['gemini_api_key'] ?? ''),
            'copilot' => $this->maskKey($settings['copilot_api_key'] ?? ''),
            'copilot_endpoint' => filled($settings['copilot_endpoint'] ?? ''),
        ];
    }

    private function maskKey(?string $key): array
    {
        $key = trim((string) $key);

        if ($key === '') {
            return [
                'saved' => false,
                'label' => 'sem chave cadastrada',
            ];
        }

        return [
            'saved' => true,
            'label' => 'chave cadastrada, final '.Str::of($key)->substr(-4),
        ];
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
