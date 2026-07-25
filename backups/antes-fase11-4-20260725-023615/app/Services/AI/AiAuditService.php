<?php

namespace App\Services\AI;

use App\Models\AiAuditEvent;
use App\Models\AiExecution;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AiAuditService
{
    public function record(AiExecution $execution, string $action, array $parameters = [], ?string $error = null): AiAuditEvent
    {
        return AiAuditEvent::create([
            'execution_id' => $execution->id, 'user_id' => $execution->user_id,
            'provider_id' => $execution->provider_id, 'prompt_template_id' => $execution->prompt_template_id,
            'article_id' => Arr::get($parameters, 'article_id'), 'action' => $action,
            'model' => $execution->model ?: $execution->provider?->model,
            'safe_parameters' => $this->sanitize($parameters),
            'result_status' => $execution->status, 'error_message' => $error ? Str::limit($error, 1000, '') : null,
            'ip_address' => request()?->ip(), 'user_agent' => Str::limit((string) request()?->userAgent(), 500, ''),
        ]);
    }

    private function sanitize(array $payload): array
    {
        return collect($payload)->mapWithKeys(function ($value, $key) {
            $sensitive = Str::contains(Str::lower((string) $key), ['key','token','secret','password','credential','content','body','source']);
            if ($sensitive) return [$key => '[REDACTED]'];
            if (is_array($value)) return [$key => $this->sanitize($value)];
            return [$key => is_string($value) ? Str::limit($value, 250, '') : $value];
        })->all();
    }
}
