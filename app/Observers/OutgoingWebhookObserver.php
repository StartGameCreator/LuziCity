<?php

namespace App\Observers;

use App\Models\EditorialCalendarEvent;
use App\Models\NewsArticle;
use App\Models\PodcastEpisode;
use App\Services\WebhookDispatcher;
use Illuminate\Database\Eloquent\Model;

class OutgoingWebhookObserver
{
    public function created(Model $model): void
    {
        if ($event = $this->publishedEvent($model)) {
            app(WebhookDispatcher::class)->dispatch($event, $model);
        }
    }

    public function updated(Model $model): void
    {
        if (! $event = $this->publishedEvent($model)) {
            return;
        }
        if ($model instanceof NewsArticle && $model->getOriginal('status') === NewsArticle::STATUS_PUBLISHED
            && $model->wasChanged(['title', 'subtitle', 'excerpt', 'body', 'category_id', 'cover_image_path', 'published_at'])) {
            app(WebhookDispatcher::class)->dispatch('news.updated', $model);

            return;
        }
        if ($model->wasChanged(['status', 'is_published', 'published_at'])) {
            app(WebhookDispatcher::class)->dispatch($event, $model);
        }
    }

    private function publishedEvent(Model $model): ?string
    {
        if ($model instanceof NewsArticle && $model->status === NewsArticle::STATUS_PUBLISHED
            && $model->published_at?->lte(now()) && $model->sponsoredIsVisible()) {
            return 'news.published';
        }
        if ($model instanceof PodcastEpisode && $model->is_published && $model->published_at?->lte(now())) {
            return 'podcast.published';
        }
        if ($model instanceof EditorialCalendarEvent && $model->status === 'active'
            && $model->event_type === 'local_event' && ! $model->is_ai_suggestion) {
            return 'event.published';
        }

        return null;
    }
}
