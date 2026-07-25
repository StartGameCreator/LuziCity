<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseCloudMessaging
{
    public function configured(): bool
    {
        return filled(config('services.firebase.project_id'))
            && filled(config('services.firebase.service_account'));
    }

    public function send(string $token, string $title, string $body, string $url = '/'): void
    {
        $projectId = (string) config('services.firebase.project_id');
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => ['title' => $title, 'body' => $body],
                    'webpush' => [
                        'fcm_options' => ['link' => url($url)],
                        'notification' => [
                            'icon' => asset('pwa/icon.svg'),
                            'badge' => asset('pwa/icon.svg'),
                        ],
                    ],
                    'data' => ['url' => url($url)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha FCM: '.$response->status().' '.$response->body());
        }
    }

    private function accessToken(): string
    {
        $account = $this->serviceAccount();
        $now = time();
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64Url(json_encode([
            'iss' => $account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsigned = $header.'.'.$claims;
        $signature = '';

        if (! openssl_sign($unsigned, $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Não foi possível assinar o JWT do Firebase.');
        }

        $jwt = $unsigned.'.'.$this->base64Url($signature);
        return (string) Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ])->throw()->json('access_token');
    }

    private function serviceAccount(): array
    {
        $value = (string) config('services.firebase.service_account');
        $path = base_path($value);
        $json = is_file($path) ? file_get_contents($path) : base64_decode($value, true);
        $account = is_string($json) ? json_decode($json, true) : null;

        if (! is_array($account) || empty($account['client_email']) || empty($account['private_key'])) {
            throw new RuntimeException('Credencial FIREBASE_SERVICE_ACCOUNT inválida.');
        }
        return $account;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
