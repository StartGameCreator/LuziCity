<?php

namespace App\Services;

use App\Jobs\DeliverWebhookJob;
use App\Models\EditorialCalendarEvent;
use App\Models\NewsArticle;
use App\Models\PodcastEpisode;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    public function dispatch(string $event, Model $model): void
    {
        WebhookEndpoint::query()->where('is_active', true)->get()
            ->filter(fn (WebhookEndpoint $endpoint) => in_array('*', $endpoint->events, true)
                || in_array($event, $endpoint->events, true))
            ->each(function (WebhookEndpoint $endpoint) use ($event, $model): void {
                $eventId = (string) Str::uuid();
                $delivery = WebhookDelivery::create([
                    'event_id' => $eventId, 'webhook_endpoint_id' => $endpoint->id,
                    'event' => $event, 'payload' => [
                        'id' => $eventId, 'event' => $event, 'occurred_at' => now()->toIso8601String(),
                        'data' => $this->data($model),
                    ],
                ]);
                DeliverWebhookJob::dispatch($delivery->id);
            });
    }

    private function data(Model $model): array
    {
        return match (true) {
            $model instanceof NewsArticle => [
                'id' => $model->id, 'slug' => $model->slug, 'title' => $model->title,
                'excerpt' => $model->excerpt, 'status' => $model->status,
                'published_at' => $model->published_at?->toIso8601String(),
                'url' => route('news.show', ['news' => $model->slug]),
            ],
            $model instanceof PodcastEpisode => [
                'id' => $model->id, 'series_id' => $model->podcast_series_id,
                'slug' => $model->slug, 'title' => $model->title,
                'published_at' => $model->published_at?->toIso8601String(),
            ],
            $model instanceof EditorialCalendarEvent => [
                'id' => $model->id, 'title' => $model->title, 'description' => $model->description,
                'starts_at' => $model->starts_at?->toIso8601String(),
                'ends_at' => $model->ends_at?->toIso8601String(),
            ],
            default => ['id' => $model->getKey()],
        };
    }
}
