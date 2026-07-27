<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'slug' => $this->slug, 'title' => $this->title,
            'description' => $this->description, 'provider' => $this->provider,
            'duration_seconds' => $this->duration_seconds,
            'category' => $this->category?->name, 'series' => $this->series?->title,
            'thumbnail_url' => $this->thumbnail_path ? asset('storage/'.$this->thumbnail_path) : null,
            'video_url' => $this->publicUrl(), 'published_at' => $this->published_at?->toIso8601String(),
            'url' => route('videos.show', $this->resource),
        ];
    }
}
