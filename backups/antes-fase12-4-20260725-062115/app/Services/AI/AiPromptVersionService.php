<?php

namespace App\Services\AI;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiPromptVersionService
{
    public const PURPOSES = [
        'news' => 'Notícia completa', 'title' => 'Título', 'summary' => 'Resumo',
        'seo' => 'SEO', 'review' => 'Revisão', 'translation' => 'Tradução',
        'social' => 'Redes sociais', 'script' => 'Roteiro', 'audio' => 'Áudio',
        'video' => 'Vídeo', 'rewrite' => 'Reescrita', 'tags' => 'Tags',
    ];

    public const ALLOWED_VARIABLES = [
        'briefing', 'content', 'title', 'source_text', 'source_url', 'language',
        'tone', 'rules', 'max_title_length', 'max_excerpt_length', 'audience',
        'platform', 'duration', 'target_language',
    ];

    public function create(array $data, ?int $userId): AiPromptTemplate
    {
        $variables = $this->validatePlaceholders($data['system_prompt']."\n".$data['user_template']);

        return DB::transaction(function () use ($data, $variables, $userId): AiPromptTemplate {
            $template = AiPromptTemplate::query()->create([
                ...$this->templateData($data),
                'version' => 1,
            ]);
            $this->snapshot($template, $variables, $data['change_notes'] ?? 'Versão inicial.', $userId);
            $this->enforceDefault($template);

            return $template;
        });
    }

    public function update(AiPromptTemplate $template, array $data, ?int $userId): AiPromptTemplate
    {
        $variables = $this->validatePlaceholders($data['system_prompt']."\n".$data['user_template']);

        return DB::transaction(function () use ($template, $data, $variables, $userId): AiPromptTemplate {
            $template->update([...$this->templateData($data), 'version' => $template->version + 1]);
            $this->snapshot($template, $variables, $data['change_notes'] ?? 'Prompt atualizado.', $userId);
            $this->enforceDefault($template);

            return $template->refresh();
        });
    }

    public function restore(AiPromptTemplate $template, AiPromptVersion $version, ?int $userId): AiPromptTemplate
    {
        abort_unless($version->ai_prompt_template_id === $template->id, 404);

        return DB::transaction(function () use ($template, $version, $userId): AiPromptTemplate {
            $template->update([
                'system_prompt' => $version->system_prompt,
                'user_template' => $version->user_prompt,
                'version' => $template->version + 1,
            ]);
            $this->snapshot($template, $version->variables ?? [], "Restaurada a partir da versão {$version->version}.", $userId);

            return $template->refresh();
        });
    }

    public function placeholders(string $text): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', $text, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }

    private function validatePlaceholders(string $text): array
    {
        $variables = $this->placeholders($text);
        $invalid = array_values(array_diff($variables, self::ALLOWED_VARIABLES));
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'user_template' => 'Variáveis não permitidas: '.implode(', ', $invalid).'.',
            ]);
        }
        return $variables;
    }

    private function templateData(array $data): array
    {
        return [
            'key' => $data['key'], 'name' => $data['name'], 'purpose' => $data['purpose'],
            'system_prompt' => $data['system_prompt'], 'user_template' => $data['user_template'],
            'output_schema' => $data['output_schema'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
    }

    private function snapshot(AiPromptTemplate $template, array $variables, string $notes, ?int $userId): void
    {
        $template->versions()->create([
            'version' => $template->version, 'system_prompt' => $template->system_prompt,
            'user_prompt' => $template->user_template, 'variables' => $variables,
            'change_notes' => $notes, 'created_by' => $userId,
        ]);
    }

    private function enforceDefault(AiPromptTemplate $template): void
    {
        if ($template->is_default) {
            AiPromptTemplate::query()->where('purpose', $template->purpose)->whereKeyNot($template->id)->update(['is_default' => false]);
        }
    }
}
