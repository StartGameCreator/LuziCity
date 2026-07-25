<?php

namespace App\Services\AI;

use App\Models\AiExecution;
use App\Models\AiPromptTemplate;
use App\Models\AiProvider;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class AiEditorialManager
{
    /**
     * Registra uma operação de IA com auditoria, duração, estado e payloads.
     *
     * O callback recebe o provedor e o prompt já renderizado.
     */
    public function execute(
        string $feature,
        string $templateKey,
        array $variables,
        Closure $callback,
        ?string $providerSlug = null
    ): array {
        $template = AiPromptTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->firstOrFail();

        $provider = $this->resolveProvider($providerSlug);
        $prompt = $this->render($template->user_template, $variables);

        $execution = AiExecution::query()->create([
            'user_id' => auth()->id(),
            'provider_id' => $provider?->id,
            'prompt_template_id' => $template->id,
            'feature' => $feature,
            'status' => 'running',
            'input_hash' => hash('sha256', json_encode($variables, JSON_UNESCAPED_UNICODE)),
            'input_payload' => [
                'variables' => $this->redact($variables),
                'prompt_version' => $template->version,
            ],
            'started_at' => now(),
        ]);

        $started = hrtime(true);

        try {
            $result = $callback($provider, [
                'system' => $template->system_prompt,
                'user' => $prompt,
                'schema' => $template->output_schema,
            ]);

            $duration = (int) round((hrtime(true) - $started) / 1_000_000);

            $execution->update([
                'status' => 'completed',
                'output_payload' => is_array($result) ? $result : ['text' => (string) $result],
                'duration_ms' => $duration,
                'finished_at' => now(),
            ]);

            return [
                'execution_id' => $execution->id,
                'provider' => $provider?->slug,
                'result' => $result,
            ];
        } catch (Throwable $exception) {
            $duration = (int) round((hrtime(true) - $started) / 1_000_000);

            $execution->update([
                'status' => 'failed',
                'duration_ms' => $duration,
                'error_message' => Str::limit($exception->getMessage(), 5000, ''),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function render(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function (array $matches) use ($variables): string {
            $value = data_get($variables, $matches[1], '');

            return is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }, $template) ?? $template;
    }

    private function resolveProvider(?string $slug): ?AiProvider
    {
        $query = AiProvider::query()->where('is_enabled', true);

        if ($slug) {
            return $query->where('slug', $slug)->firstOrFail();
        }

        return $query->orderBy('id')->first();
    }

    private function redact(array $payload): array
    {
        return collect($payload)->mapWithKeys(function ($value, $key): array {
            $sensitive = Str::contains(Str::lower((string) $key), [
                'key', 'token', 'secret', 'password', 'credential',
            ]);

            return [$key => $sensitive ? '[REDACTED]' : $value];
        })->all();
    }
}
