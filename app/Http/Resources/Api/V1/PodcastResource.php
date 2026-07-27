<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'slug' => $this->slug, 'title' => $this->title,
            'description' => $this->description, 'episode_number' => $this->episode_number,
            'duration_seconds' => $this->duration_seconds, 'audio_mime' => $this->audio_mime,
            'audio_url' => $this->audioUrl(),
            'series' => ['slug' => $this->series->slug, 'title' => $this->series->title],
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
