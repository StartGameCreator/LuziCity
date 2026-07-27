<?php

namespace App\Services;

use App\Contracts\RadioAutomationProvider;
use App\Models\RadioStation;
use App\Models\Setting;

class RadioPlaybackService
{
    public function __construct(private readonly RadioAutomationProvider $provider)
    {
    }

    public function state(): array
    {
        $settings = Setting::radioSettings();
        $station = RadioStation::query()->where('is_active', true)->first();
        $azuraCast = $this->provider->nowPlaying();
        $fallbackStream = trim((string) ($station?->stream_url
            ?: data_get($settings, 'audio_stream_url', '')));
        $azuraStream = trim((string) data_get($azuraCast, 'stream_url', ''));
        $useAzuraCast = filled($azuraStream);

        return [
            'source' => $useAzuraCast ? 'azuracast' : 'fallback',
            'stream_url' => $useAzuraCast ? $azuraStream : $fallbackStream,
            'is_online' => $useAzuraCast
                ? (bool) data_get($azuraCast, 'is_online', false)
                : filled($fallbackStream),
            'station' => data_get($azuraCast, 'station') ?: $station?->name ?: 'Rádio Web Luzicity',
            'title' => data_get($azuraCast, 'title'),
            'artist' => data_get($azuraCast, 'artist'),
            'album' => data_get($azuraCast, 'album'),
            'art' => data_get($azuraCast, 'art'),
            'listeners' => (int) data_get($azuraCast, 'listeners', 0),
            'unique_listeners' => (int) data_get($azuraCast, 'unique_listeners', 0),
            'is_live' => (bool) data_get($azuraCast, 'is_live', false),
            'live_dj' => data_get($azuraCast, 'live_dj'),
            'duration' => (int) data_get($azuraCast, 'duration', 0),
            'elapsed' => (int) data_get($azuraCast, 'elapsed', 0),
            'next_title' => data_get($azuraCast, 'next_title'),
            'next_artist' => data_get($azuraCast, 'next_artist'),
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
