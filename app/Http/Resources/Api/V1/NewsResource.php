<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'slug' => $this->slug, 'title' => $this->title,
            'subtitle' => $this->subtitle, 'excerpt' => $this->excerpt,
            'is_premium' => $this->is_premium, 'is_sponsored' => $this->is_sponsored,
            'sponsor_label' => $this->when($this->is_sponsored, $this->sponsor_label ?: 'Conteúdo patrocinado'),
            'category' => $this->category ? ['id' => $this->category->id, 'slug' => $this->category->slug, 'name' => $this->category->name] : null,
            'author' => $this->author ? ['id' => $this->author->id, 'name' => $this->author->name] : null,
            'cover_image_url' => $this->cover_image_path ? asset($this->cover_image_path) : null,
            'published_at' => $this->published_at?->toIso8601String(),
            'url' => route('news.show', ['news' => $this->slug]),
            'origin' => $this->attributionSite() ? [
                'site_id' => $this->attributionSite()->id,
                'site_name' => $this->attributionSite()->name,
                'mode' => $this->origin_site_id ? 'copy' : 'reference',
            ] : null,
        ];
    }
}
