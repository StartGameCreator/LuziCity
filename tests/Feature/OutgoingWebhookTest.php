<?php

namespace Tests\Feature;

use App\Jobs\DeliverWebhookJob;
use App\Models\EditorialCalendarEvent;
use App\Models\NewsArticle;
use App\Models\PodcastEpisode;
use App\Models\PodcastSeries;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OutgoingWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_and_update_events_create_queued_deliveries(): void
    {
        Queue::fake();
        $endpoint = WebhookEndpoint::create([
            'name' => 'Integração', 'url' => 'https://integracao.example/webhooks',
            'secret' => 'segredo-compartilhado',
            'events' => ['news.published', 'news.updated', 'event.published', 'podcast.published'],
        ]);
        $author = User::factory()->create();
        $article = NewsArticle::create([
            'author_id' => $author->id, 'title' => 'Publicação', 'slug' => 'publicacao-webhook',
            'body' => 'Conteúdo', 'status' => 'published', 'workflow_status' => 'published', 'published_at' => now(),
        ]);
        $article->update(['title' => 'Publicação atualizada']);
        $article->increment('sponsored_views_count');
        EditorialCalendarEvent::create([
            'title' => 'Evento local', 'event_type' => 'local_event', 'status' => 'active',
            'starts_at' => now()->addDay(), 'is_ai_suggestion' => false,
        ]);
        $series = PodcastSeries::create(['title' => 'Série', 'slug' => 'serie-hook', 'is_published' => true]);
        PodcastEpisode::create([
            'podcast_series_id' => $series->id, 'title' => 'Episódio', 'slug' => 'episodio-hook',
            'audio_path' => 'podcasts/audio.mp3', 'is_published' => true, 'published_at' => now(),
        ]);

        $this->assertSame(
            ['event.published', 'news.published', 'news.updated', 'podcast.published'],
            $endpoint->deliveries()->orderBy('event')->pluck('event')->all()
        );
        $this->assertSame(4, WebhookDelivery::count());
        Queue::assertPushed(DeliverWebhookJob::class, 4);
    }

    public function test_delivery_is_signed_and_audited(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Destino', 'url' => 'https://1.1.1.1/hook',
            'secret' => 'segredo-forte', 'events' => ['news.published'],
        ]);
        $delivery = WebhookDelivery::create([
            'event_id' => 'db33393c-93f0-4c6f-9f05-47fd03230d0e',
            'webhook_endpoint_id' => $endpoint->id, 'event' => 'news.published',
            'payload' => ['id' => 'db33393c-93f0-4c6f-9f05-47fd03230d0e', 'event' => 'news.published', 'data' => ['id' => 10]],
        ]);
        Http::fake(['1.1.1.1/*' => Http::response(['accepted' => true], 202)]);

        (new DeliverWebhookJob($delivery->id))->handle();

        $body = json_encode($delivery->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Http::assertSent(fn ($request) => $request->hasHeader(
            'X-LuziCity-Signature', 'sha256='.hash_hmac('sha256', $body, 'segredo-forte')
        ) && $request->hasHeader('X-LuziCity-Event', 'news.published'));
        $delivery->refresh();
        $this->assertSame('delivered', $delivery->status);
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame(202, $delivery->response_status);
        $this->assertNotNull($delivery->delivered_at);
    }

    public function test_failed_delivery_is_recorded_and_rethrown_for_queue_retry(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Destino', 'url' => 'https://1.1.1.1/hook',
            'secret' => 'segredo', 'events' => ['event.published'],
        ]);
        $delivery = WebhookDelivery::create([
            'event_id' => '6c0e240e-f46e-4b52-bbea-6292b33fe718',
            'webhook_endpoint_id' => $endpoint->id, 'event' => 'event.published',
            'payload' => ['event' => 'event.published'],
        ]);
        Http::fake(['1.1.1.1/*' => Http::response('indisponível', 503)]);

        try {
            (new DeliverWebhookJob($delivery->id))->handle();
            $this->fail('A entrega deveria lançar uma exceção para permitir retry.');
        } catch (\Throwable) {
            $delivery->refresh();
            $this->assertSame('failed', $delivery->status);
            $this->assertSame(503, $delivery->response_status);
            $this->assertSame(1, $delivery->attempts);
            $this->assertNotNull($delivery->error);
        }
    }

    public function test_delivery_revalidates_destination_and_blocks_ssrf(): void
    {
        Http::fake();
        $endpoint = WebhookEndpoint::create([
            'name' => 'Destino interno', 'url' => 'http://127.0.0.1/admin',
            'secret' => 'segredo', 'events' => ['event.published'],
        ]);
        $delivery = WebhookDelivery::create([
            'event_id' => '28fa639e-e788-42d1-87d1-b6a08c1f4c88',
            'webhook_endpoint_id' => $endpoint->id, 'event' => 'event.published',
            'payload' => ['event' => 'event.published'],
        ]);

        try {
            (new DeliverWebhookJob($delivery->id))->handle();
            $this->fail('O destino privado deveria ser bloqueado.');
        } catch (\Throwable) {
            $this->assertSame('failed', $delivery->refresh()->status);
            Http::assertNothingSent();
        }
    }
}
