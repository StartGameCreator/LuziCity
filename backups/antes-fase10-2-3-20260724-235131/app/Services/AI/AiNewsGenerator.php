<?php

namespace App\Services\AI;

use App\Models\AiEditorialProfile;
use App\Models\Category;
use App\Services\AiWritingAssistant;
use Illuminate\Support\Str;
use RuntimeException;

class AiNewsGenerator
{
    public function __construct(
        private readonly AiWritingAssistant $assistant,
        private readonly AiEditorialManager $manager
    ) {
    }

    public function generate(array $data): array
    {
        $profile = AiEditorialProfile::query()
            ->where('is_default', true)
            ->firstOrFail();

        $variables = [
            'briefing' => trim((string) $data['brief']),
            'source_text' => trim((string) ($data['source_text'] ?? '')),
            'source_url' => trim((string) ($data['source_url'] ?? '')),
            'language' => $profile->language,
            'tone' => $profile->tone,
            'rules' => $profile->editorial_rules,
            'max_title_length' => $profile->max_title_length,
            'max_excerpt_length' => $profile->max_excerpt_length,
        ];

        $execution = $this->manager->execute(
            feature: 'news_generation',
            templateKey: 'editorial.news.full',
            variables: $variables,
            callback: function ($provider, array $renderedPrompt) use ($variables, $data): array {
                $prompt = $this->buildPrompt($variables, $renderedPrompt);

                $result = $this->assistant->draftResult(
                    'news',
                    $prompt,
                    $data['working_title'] ?? null,
                    $provider?->slug ?? ($data['provider'] ?? null)
                );

                if (($result['source'] ?? 'local') === 'local') {
                    throw new RuntimeException(
                        ($result['message'] ?? 'O provedor não respondeu.')
                        .' A geração estruturada exige uma API configurada e ativa.'
                    );
                }

                $parsed = $this->parseJson((string) ($result['text'] ?? ''));

                if (! $parsed) {
                    throw new RuntimeException('A IA respondeu, mas não retornou JSON editorial válido.');
                }

                return $parsed + [
                    '_provider' => $result['provider'] ?? null,
                    '_source' => $result['source'] ?? null,
                    '_message' => $result['message'] ?? null,
                ];
            },
            providerSlug: $data['provider'] ?? null
        );

        $result = is_array($execution['result']) ? $execution['result'] : [];
        $result['execution_id'] = $execution['execution_id'];
        $result['slug'] = Str::slug((string) ($result['slug'] ?? $result['title'] ?? 'noticia'));
        $result['reading_time_minutes'] = max(
            1,
            (int) ceil(str_word_count(strip_tags((string) ($result['body'] ?? ''))) / 220)
        );
        $result['category_id'] = $this->resolveCategoryId($result['category'] ?? null);
        $result['review_required'] = true;

        return $result;
    }

    private function buildPrompt(array $variables, array $renderedPrompt): string
    {
        $basePrompt = trim(
            ($renderedPrompt['system'] ?? '')."\n\n".($renderedPrompt['user'] ?? '')
        );

        return <<<PROMPT
{$basePrompt}

REGRAS COMPLEMENTARES:
{$variables['rules']}

Idioma: {$variables['language']}
Tom: {$variables['tone']}
Título com no máximo {$variables['max_title_length']} caracteres.
Resumo com no máximo {$variables['max_excerpt_length']} caracteres.
Não invente dados. Não transforme alegações em fatos. Cite a fonte quando existir.

Texto-fonte:
{$variables['source_text']}

URL da fonte:
{$variables['source_url']}

Responda SOMENTE com JSON válido, sem markdown e sem comentários:
{
  "title": "string",
  "subtitle": "string",
  "excerpt": "string",
  "body": "string em parágrafos",
  "slug": "string",
  "seo_title": "string",
  "seo_description": "string",
  "category": "string",
  "tags": ["string"],
  "sources": ["string"],
  "confidence_score": 0,
  "review_notes": ["string"],
  "legal_risk": "baixo|medio|alto"
}
PROMPT;
    }

    private function parseJson(string $text): ?array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text) ?? $text;

        $decoded = json_decode($text, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private function resolveCategoryId(?string $name): ?int
    {
        if (blank($name)) {
            return null;
        }

        return Category::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower(trim($name))])
            ->value('id');
    }
}
