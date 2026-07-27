<?php

namespace App\Services\AzuraCast;

use App\Contracts\RadioAutomationProvider;
use App\Exceptions\AzuraCastException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AzuraCastRadioProvider implements RadioAutomationProvider
{
    public function __construct(private readonly AzuraCastClient $client)
    {
    }

    public function enabled(): bool
    {
        return $this->client->enabled();
    }

    public function health(): array
    {
        $startedAt = microtime(true);
        $result = [
            'enabled' => $this->enabled(),
            'connected' => false,
            'configured' => $this->client->hasApiKey(),
            'base_url' => $this->safeBaseUrl(),
            'latency_ms' => null,
            'message' => null,
        ];

        if (! $this->enabled()) {
            $result['message'] = 'Integração desativada no ambiente.';

            return $result;
        }

        try {
            $payload = $this->client->hasApiKey()
                ? $this->client->get('admin/server/stats')
                : $this->client->get('nowplaying', false);
            $result['connected'] = true;
            $result['version'] = data_get($payload, 'version');
            $result['message'] = $this->client->hasApiKey()
                ? 'AzuraCast conectado.'
                : 'Servidor acessível; configure a API Key para os controles.';
        } catch (Throwable $exception) {
            $result['message'] = $exception->getMessage();
        }

        $result['latency_ms'] = (int) round((microtime(true) - $startedAt) * 1000);

        return $result;
    }

    public function nowPlaying(): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $cacheSeconds = max(1, (int) config('services.azuracast.cache_seconds', 10));

        try {
            return Cache::remember('azuracast:now-playing', $cacheSeconds, function (): ?array {
                $shortcode = trim((string) config('services.azuracast.station_shortcode'));
                $stationId = trim((string) config('services.azuracast.station_id'));
                $identifier = $shortcode !== '' ? $shortcode : $stationId;
                $path = $identifier !== '' ? 'nowplaying/'.rawurlencode($identifier) : 'nowplaying';
                $payload = $this->client->get($path, false);

                if (array_is_list($payload)) {
                    $payload = $payload[0] ?? [];
                }

                return $this->normalizeNowPlaying($payload);
            });
        } catch (Throwable) {
            return Cache::get('azuracast:now-playing:stale');
        }
    }

    public function station(): ?array
    {
        if (! $this->enabled() || ! $this->client->hasApiKey()) {
            return null;
        }

        $stationId = trim((string) config('services.azuracast.station_id'));
        if ($stationId === '') {
            return null;
        }

        try {
            return $this->client->get('station/'.rawurlencode($stationId));
        } catch (Throwable) {
            return null;
        }
    }

    public function control(string $action): array
    {
        if (! in_array($action, ['start', 'stop', 'restart'], true)) {
            throw new AzuraCastException('Comando de rádio inválido.', 422);
        }

        $stationId = trim((string) config('services.azuracast.station_id'));
        if ($stationId === '') {
            throw new AzuraCastException('Configure o ID da emissora AzuraCast.', 422);
        }

        return $this->client->post('station/'.rawurlencode($stationId).'/'.$action);
    }

    private function normalizeNowPlaying(array $payload): ?array
    {
        if ($payload === []) {
            return null;
        }

        $streamUrl = data_get($payload, 'station.listen_url')
            ?: data_get($payload, 'station.mounts.0.url')
            ?: data_get($payload, 'station.hls_url');
        $normalized = [
            'station' => data_get($payload, 'station.name'),
            'station_shortcode' => data_get($payload, 'station.shortcode'),
            'stream_url' => $streamUrl,
            'is_online' => (bool) data_get($payload, 'is_online', false),
            'is_live' => (bool) data_get($payload, 'live.is_live', false),
            'live_dj' => data_get($payload, 'live.streamer_name'),
            'listeners' => (int) data_get($payload, 'listeners.current', 0),
            'unique_listeners' => (int) data_get($payload, 'listeners.unique', 0),
            'title' => data_get($payload, 'now_playing.song.title'),
            'artist' => data_get($payload, 'now_playing.song.artist'),
            'album' => data_get($payload, 'now_playing.song.album'),
            'art' => data_get($payload, 'now_playing.song.art'),
            'duration' => (int) data_get($payload, 'now_playing.duration', 0),
            'elapsed' => (int) data_get($payload, 'now_playing.elapsed', 0),
            'played_at' => data_get($payload, 'now_playing.played_at'),
            'next_title' => data_get($payload, 'playing_next.song.title'),
            'next_artist' => data_get($payload, 'playing_next.song.artist'),
        ];

        Cache::put('azuracast:now-playing:stale', $normalized, now()->addMinutes(5));

        return $normalized;
    }

    private function safeBaseUrl(): ?string
    {
        try {
            return $this->client->baseUrl();
        } catch (Throwable) {
            return null;
        }
    }
}
