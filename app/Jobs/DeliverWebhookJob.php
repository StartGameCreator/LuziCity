<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\Security\PublicUrlGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $timeout = 30;

    public function __construct(public int $deliveryId)
    {
        $this->onQueue('webhooks');
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(?PublicUrlGuard $urlGuard = null): void
    {
        $delivery = WebhookDelivery::with('endpoint')->findOrFail($this->deliveryId);
        if ($delivery->status === 'delivered' || ! $delivery->endpoint->is_active) {
            return;
        }
        $body = json_encode($delivery->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $delivery->increment('attempts');

        try {
            $url = ($urlGuard ?? app(PublicUrlGuard::class))->validate($delivery->endpoint->url);
            $response = Http::timeout(15)->connectTimeout(5)->withOptions(['allow_redirects' => false])->withHeaders([
                'User-Agent' => 'LuziCity-Webhooks/1.0',
                'X-LuziCity-Event' => $delivery->event,
                'X-LuziCity-Delivery' => $delivery->event_id,
                'X-LuziCity-Signature' => 'sha256='.hash_hmac('sha256', $body, $delivery->endpoint->secret),
            ])->withBody($body, 'application/json')->post($url);
            $delivery->update([
                'response_status' => $response->status(),
                'response_body' => Str::limit($response->body(), 4000, ''),
            ]);
            $response->throw();
            $delivery->update(['status' => 'delivered', 'delivered_at' => now(), 'error' => null]);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'error' => Str::limit($exception->getMessage(), 4000, '')]);
            throw $exception;
        }
    }
}
