<?php

namespace Tests\Feature;

use App\Contracts\RadioAutomationProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AzuraCastNativeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_radio_state_uses_normalized_azuracast_data(): void
    {
        config()->set('services.azuracast', [
            'enabled' => true,
            'base_url' => 'http://127.0.0.1:8080',
            'api_key' => null,
            'station_id' => null,
            'station_shortcode' => 'luzicity',
            'timeout' => 2,
            'verify_ssl' => false,
            'cache_seconds' => 1,
        ]);

        Http::fake([
            'http://127.0.0.1:8080/api/nowplaying/luzicity' => Http::response([
                'station' => [
                    'name' => 'Rádio Luzicity',
                    'shortcode' => 'luzicity',
                    'listen_url' => 'http://127.0.0.1:9100/radio.mp3',
                ],
                'is_online' => true,
                'listeners' => ['current' => 12, 'unique' => 9],
                'live' => ['is_live' => false, 'streamer_name' => null],
                'now_playing' => [
                    'song' => ['title' => 'Faixa atual', 'artist' => 'Artista', 'album' => 'Álbum', 'art' => null],
                    'duration' => 180,
                    'elapsed' => 30,
                ],
            ]),
        ]);

        $this->getJson(route('radio.state'))
            ->assertOk()
            ->assertJson([
                'source' => 'azuracast',
                'stream_url' => 'http://127.0.0.1:9100/radio.mp3',
                'station' => 'Rádio Luzicity',
                'title' => 'Faixa atual',
                'artist' => 'Artista',
                'listeners' => 12,
            ])
            ->assertJsonMissingPath('api_key');
    }

    public function test_disabled_integration_returns_safe_fallback_state(): void
    {
        config()->set('services.azuracast.enabled', false);

        $this->getJson(route('radio.state'))
            ->assertOk()
            ->assertJson([
                'source' => 'fallback',
                'is_online' => false,
                'listeners' => 0,
            ]);

        Http::assertNothingSent();
    }

    public function test_authenticated_requests_keep_token_on_backend(): void
    {
        config()->set('services.azuracast', [
            'enabled' => true,
            'base_url' => 'https://azuracast.luzicity.com.br',
            'api_key' => 'segredo-backend',
            'station_id' => '1',
            'station_shortcode' => 'luzicity',
            'timeout' => 2,
            'verify_ssl' => true,
            'cache_seconds' => 1,
        ]);

        Http::fake([
            'https://azuracast.luzicity.com.br/api/admin/server/stats' => Http::response(['version' => 'stable']),
        ]);

        $health = app(RadioAutomationProvider::class)->health();

        $this->assertTrue($health['connected']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer segredo-backend'));
    }
}
