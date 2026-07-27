<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EditorialCalendarEvent;
use App\Models\NewsArticle;
use App\Models\PodcastEpisode;
use App\Models\PodcastSeries;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_versioned_endpoints_only_expose_public_content_with_pagination(): void
    {
        $author = User::factory()->create();
        $category = Category::create(['name' => 'Cidade', 'slug' => 'cidade', 'is_active' => true]);
        NewsArticle::create([
            'author_id' => $author->id, 'category_id' => $category->id, 'title' => 'Publicada',
            'slug' => 'publicada', 'body' => 'Texto protegido da listagem', 'excerpt' => 'Resumo público',
            'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        NewsArticle::create([
            'author_id' => $author->id, 'title' => 'Rascunho', 'slug' => 'rascunho-api',
            'body' => 'Segredo', 'status' => 'draft',
        ]);
        Video::create([
            'title' => 'Vídeo público', 'slug' => 'video-publico', 'provider' => 'file',
            'video_path' => 'videos/publico.mp4', 'is_published' => true, 'published_at' => now(),
        ]);
        Video::create([
            'title' => 'Vídeo privado', 'slug' => 'video-privado', 'provider' => 'file',
            'video_path' => 'videos/privado.mp4', 'is_published' => false,
        ]);
        $series = PodcastSeries::create(['title' => 'Série pública', 'slug' => 'serie-publica', 'is_published' => true]);
        PodcastEpisode::create([
            'podcast_series_id' => $series->id, 'title' => 'Episódio público', 'slug' => 'episodio-publico',
            'audio_path' => 'podcasts/audio.mp3', 'is_published' => true, 'published_at' => now(),
        ]);
        EditorialCalendarEvent::create([
            'title' => 'Evento público', 'event_type' => 'local_event', 'status' => 'active',
            'starts_at' => now()->addDay(), 'is_ai_suggestion' => false,
        ]);
        EditorialCalendarEvent::create([
            'title' => 'Sugestão interna', 'event_type' => 'suggestion', 'status' => 'suggested',
            'starts_at' => now()->addDay(), 'is_ai_suggestion' => true,
        ]);

        $this->getJson('/api/v1/news?per_page=1')->assertOk()
            ->assertJsonPath('data.0.title', 'Publicada')->assertJsonPath('meta.per_page', 1)
            ->assertJsonMissing(['title' => 'Rascunho'])->assertJsonMissingPath('data.0.body');
        $this->getJson('/api/v1/categories')->assertOk()->assertJsonPath('data.0.name', 'Cidade');
        $this->getJson('/api/v1/videos')->assertOk()->assertJsonFragment(['title' => 'Vídeo público'])
            ->assertJsonMissing(['title' => 'Vídeo privado']);
        $this->getJson('/api/v1/podcasts')->assertOk()->assertJsonFragment(['title' => 'Episódio público']);
        $this->getJson('/api/v1/events')->assertOk()->assertJsonFragment(['title' => 'Evento público'])
            ->assertJsonMissing(['title' => 'Sugestão interna']);
    }

    public function test_per_page_is_capped_at_one_hundred(): void
    {
        $this->getJson('/api/v1/news?per_page=999')->assertOk()->assertJsonPath('meta.per_page', 100);
    }
}
