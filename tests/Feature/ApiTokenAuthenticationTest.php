<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_issue_use_and_revoke_scoped_token(): void
    {
        $user = User::factory()->create(['password' => 'senha-segura', 'is_active' => true]);
        $response = $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email, 'password' => 'senha-segura', 'name' => 'Integração',
            'abilities' => ['profile:read'], 'expires_in_days' => 10,
        ])->assertCreated()->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('abilities.0', 'profile:read');
        $plainTextToken = $response->json('token');

        $this->assertStringStartsWith('lzc_', $plainTextToken);
        $this->assertDatabaseMissing('api_tokens', ['token_hash' => $plainTextToken]);
        $this->assertDatabaseHas('api_tokens', ['token_hash' => hash('sha256', $plainTextToken)]);

        $headers = ['Authorization' => 'Bearer '.$plainTextToken];
        $this->withHeaders($headers)->getJson('/api/v1/auth/me')->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.token_name', 'Integração');
        $this->assertNotNull(ApiToken::firstOrFail()->last_used_at);

        $this->withHeaders($headers)->deleteJson('/api/v1/auth/tokens/current')
            ->assertOk()->assertJsonPath('message', 'Token revogado.');
        $this->withHeaders($headers)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_scope_expiration_credentials_and_inactive_user_are_enforced(): void
    {
        $user = User::factory()->create(['password' => 'senha-correta', 'is_active' => true]);
        $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email, 'password' => 'incorreta', 'name' => 'Inválido',
            'abilities' => ['profile:read'],
        ])->assertUnprocessable();

        $plainTextToken = 'lzc_token_sem_escopo';
        ApiToken::create([
            'user_id' => $user->id, 'name' => 'Sem perfil',
            'token_hash' => hash('sha256', $plainTextToken), 'abilities' => ['content:read'],
            'expires_at' => now()->addDay(),
        ]);
        $this->withToken($plainTextToken)->getJson('/api/v1/auth/me')->assertForbidden();

        $expired = 'lzc_token_expirado';
        ApiToken::create([
            'user_id' => $user->id, 'name' => 'Expirado',
            'token_hash' => hash('sha256', $expired), 'abilities' => ['profile:read'],
            'expires_at' => now()->subMinute(),
        ]);
        $this->withToken($expired)->getJson('/api/v1/auth/me')->assertUnauthorized();

        $user->update(['is_active' => false]);
        $this->withToken($plainTextToken)->deleteJson('/api/v1/auth/tokens/current')->assertUnauthorized();
    }

    public function test_token_issuer_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/tokens', [
                'email' => 'nobody@example.com', 'password' => 'wrong-password',
                'name' => 'Tentativa', 'abilities' => ['profile:read'],
            ])->assertUnprocessable();
        }
        $this->postJson('/api/v1/auth/tokens', [
            'email' => 'nobody@example.com', 'password' => 'wrong-password',
            'name' => 'Tentativa', 'abilities' => ['profile:read'],
        ])->assertTooManyRequests();
    }
}
