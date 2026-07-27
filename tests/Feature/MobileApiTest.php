<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\NewsArticle;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_feed_search_favorites_notifications_and_profile(): void
    {
        $user = User::factory()->create(['name' => 'Leitor Mobile']);
        $article = NewsArticle::create([
            'author_id' => $user->id, 'title' => 'Notícia para celular', 'slug' => 'noticia-para-celular',
            'excerpt' => 'Conteúdo pesquisável', 'body' => 'Texto', 'status' => 'published',
            'workflow_status' => 'published', 'published_at' => now(),
        ]);
        $token = $this->token($user, ['mobile:read', 'mobile:write']);

        $this->withToken($token)->getJson('/api/v1/mobile/feed')->assertOk()
            ->assertJsonPath('data.0.title', 'Notícia para celular');
        $this->withToken($token)->getJson('/api/v1/mobile/search?q=pesquisável')->assertOk()
            ->assertJsonPath('data.0.id', $article->id);

        $this->withToken($token)->postJson('/api/v1/mobile/favorites/noticia-para-celular')->assertCreated();
        $this->withToken($token)->postJson('/api/v1/mobile/favorites/noticia-para-celular')->assertOk();
        $this->assertDatabaseCount('news_favorites', 1);
        $this->withToken($token)->getJson('/api/v1/mobile/favorites')->assertOk()
            ->assertJsonPath('data.0.slug', 'noticia-para-celular');

        $this->withToken($token)->postJson('/api/v1/mobile/notifications/devices', [
            'token' => 'firebase-mobile-token', 'device_name' => 'Celular do leitor', 'platform' => 'android',
        ])->assertOk();
        $this->assertSame($user->id, PushSubscription::firstOrFail()->user_id);

        $this->withToken($token)->patchJson('/api/v1/mobile/profile', ['name' => 'Leitor Atualizado'])->assertOk()
            ->assertJsonPath('data.name', 'Leitor Atualizado')->assertJsonPath('data.favorites_count', 1);
        $this->withToken($token)->deleteJson('/api/v1/mobile/notifications/devices', [
            'token' => 'firebase-mobile-token',
        ])->assertOk();
        $this->withToken($token)->deleteJson('/api/v1/mobile/favorites/noticia-para-celular')->assertOk();
        $this->assertDatabaseCount('news_favorites', 0);
    }

    public function test_mobile_endpoints_require_token_and_correct_scope(): void
    {
        $user = User::factory()->create();
        $readToken = $this->token($user, ['mobile:read']);

        $this->getJson('/api/v1/mobile/feed')->assertUnauthorized();
        $this->withToken($readToken)->getJson('/api/v1/mobile/feed')->assertOk();
        $this->withToken($readToken)->patchJson('/api/v1/mobile/profile', ['name' => 'Sem escopo'])->assertForbidden();
    }

    public function test_mobile_token_can_be_issued_with_mobile_scopes(): void
    {
        $user = User::factory()->create(['password' => 'senha-mobile']);
        $this->postJson('/api/v1/mobile/auth/tokens', [
            'email' => $user->email, 'password' => 'senha-mobile', 'name' => 'App Android',
            'abilities' => ['mobile:read', 'mobile:write'], 'expires_in_days' => 30,
        ])->assertCreated()->assertJsonPath('abilities.0', 'mobile:read')
            ->assertJsonPath('abilities.1', 'mobile:write');
    }

    private function token(User $user, array $abilities): string
    {
        $plain = 'lzc_'.bin2hex(random_bytes(24));
        ApiToken::create([
            'user_id' => $user->id, 'name' => 'Teste Mobile',
            'token_hash' => hash('sha256', $plain), 'abilities' => $abilities, 'expires_at' => now()->addDay(),
        ]);

        return $plain;
    }
}
