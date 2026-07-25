<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AiWritingAssistant
{
    private string $lastSource = 'api';
    private ?string $lastMessage = null;

    public function draft(string $context, string $brief, ?string $title = null, ?string $provider = null): string
    {
        return $this->draftResult($context, $brief, $title, $provider)['text'];
    }

    public function draftResult(string $context, string $brief, ?string $title = null, ?string $provider = null): array
    {
        $settings = Setting::aiSettings();
        $provider = $provider ?: $settings['provider'];
        $provider = in_array($provider, ['chatgpt', 'gemini', 'copilot'], true) ? $provider : 'chatgpt';
        $prompt = $this->buildPrompt($context, $brief, $title);
        $this->lastSource = 'api';
        $this->lastMessage = null;

        try {
            $text = match ($provider) {
                'gemini' => $this->draftWithGemini($prompt, $settings),
                'copilot' => $this->draftWithCopilot($prompt, $settings),
                default => $this->draftWithChatGpt($prompt, $settings),
            };

            return [
                'text' => $text,
                'source' => $this->lastSource,
                'provider' => $provider,
                'message' => $this->lastMessage ?: 'Texto gerado pela API.',
            ];
        } catch (Throwable $exception) {
            if (! $this->lastMessage) {
                $this->lastMessage = $this->friendlyExceptionMessage($provider, $exception);
            }

            return [
                'text' => $this->localDraft($context, $brief, $title),
                'source' => 'local',
                'provider' => $provider,
                'message' => $this->lastMessage ?: 'A API nao respondeu. Foi gerado um rascunho local.',
            ];
        }
    }

    private function draftWithChatGpt(string $prompt, array $settings): string
    {
        $key = $settings['openai_api_key'] ?: env('OPENAI_API_KEY');

        if (! $key) {
            $this->lastMessage = 'Chave do ChatGPT/OpenAI nao configurada.';
            throw new RuntimeException('ChatGPT API key missing.');
        }

        $response = Http::acceptJson()
            ->withToken($key)
            ->timeout(60)
            ->retry(2, 700)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $settings['chatgpt_model'] ?: 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Voce e um assistente editorial brasileiro. Escreva com clareza, precisao e linguagem acessivel.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            $this->lastMessage = 'ChatGPT/OpenAI respondeu com erro HTTP '.$response->status().'.';
            throw new RuntimeException('ChatGPT request failed.');
        }

        $this->lastSource = 'chatgpt';
        return $this->ensureText(
            trim((string) data_get($response->json(), 'choices.0.message.content')),
            'ChatGPT/OpenAI nao retornou texto.'
        );
    }

    private function draftWithGemini(string $prompt, array $settings): string
    {
        $key = $settings['gemini_api_key'] ?: env('GEMINI_API_KEY');

        if (! $key) {
            $this->lastMessage = 'Chave do Gemini nao configurada.';
            throw new RuntimeException('Gemini API key missing.');
        }

        $model = $settings['gemini_model'] ?: 'gemini-1.5-flash';
        $response = Http::acceptJson()
            ->timeout(60)
            ->retry(2, 700)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

        if (! $response->successful()) {
            $this->lastMessage = 'Gemini respondeu com erro HTTP '.$response->status().'.';
            throw new RuntimeException('Gemini request failed.');
        }

        $this->lastSource = 'gemini';
        return $this->ensureText(
            trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text')),
            'Gemini nao retornou texto.'
        );
    }

    private function draftWithCopilot(string $prompt, array $settings): string
    {
        $key = $settings['copilot_api_key'] ?: env('COPILOT_API_KEY');
        $endpoint = $settings['copilot_endpoint'] ?? null;

        if (! $key || ! $endpoint) {
            $this->lastMessage = 'Chave ou endpoint do Copilot nao configurado.';
            throw new RuntimeException('Copilot endpoint missing.');
        }

        $response = Http::acceptJson()
            ->withToken($key)
            ->timeout(60)
            ->retry(2, 700)
            ->post($endpoint, ['prompt' => $prompt]);

        if (! $response->successful()) {
            $this->lastMessage = 'Copilot respondeu com erro HTTP '.$response->status().'.';
            throw new RuntimeException('Copilot request failed.');
        }

        $this->lastSource = 'copilot';
        return $this->ensureText(
            trim((string) (data_get($response->json(), 'text') ?: data_get($response->json(), 'content'))),
            'Copilot nao retornou texto.'
        );
    }

    private function ensureText(string $text, string $message): string
    {
        if (filled($text)) {
            return $text;
        }

        $this->lastMessage = $message;
        throw new RuntimeException($message);
    }

    private function friendlyExceptionMessage(string $provider, Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'cURL error 7') || str_contains($message, 'Could not connect to server')) {
            return strtoupper($provider).' nao conectou ao servidor da API. Verifique internet, firewall, antivirus, proxy ou bloqueio da porta 443.';
        }

        if (str_contains($message, 'cURL error 28') || str_contains($message, 'timed out')) {
            return strtoupper($provider).' demorou demais para responder. Verifique a internet ou tente novamente.';
        }

        if (str_contains($message, 'SSL') || str_contains($message, 'certificate')) {
            return strtoupper($provider).' encontrou erro de certificado SSL no PHP. Verifique certificados/antivirus/proxy HTTPS.';
        }

        return strtoupper($provider).' nao respondeu: '.$message;
    }

    private function buildPrompt(string $context, string $brief, ?string $title): string
    {
        $kind = match ($context) {
            'about' => 'texto institucional Quem somos',
            'news_summary' => 'chamada curta e atrativa para card de noticia',
            'vehicle_ad' => 'copy persuasiva para anuncio de veiculo',
            'real_estate_ad' => 'copy persuasiva para anuncio de imovel',
            default => 'noticia jornalistica',
        };

        return trim("Produza uma redacao em portugues do Brasil para {$kind}.\nTitulo/tema: {$title}\nInformacoes fornecidas: {$brief}\nUse paragrafos curtos, leitura confortavel e tom profissional. Nao invente fatos especificos que nao estejam no briefing.");
    }

    private function localDraft(string $context, string $brief, ?string $title): string
    {
        $heading = $title ?: match ($context) {
            'about' => 'Quem somos',
            'news_summary' => 'Chamada da noticia',
            'vehicle_ad' => 'Anuncio de veiculo',
            'real_estate_ad' => 'Anuncio de imovel',
            default => 'Nova noticia',
        };

        return trim("{$heading}\n\n{$brief}\n\nEste texto e um rascunho inicial para revisao humana. Complete com dados confirmados, diferenciais, contatos e informacoes importantes antes da publicacao.");
    }
}
