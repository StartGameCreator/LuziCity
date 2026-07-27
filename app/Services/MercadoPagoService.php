<?php

namespace App\Services;

use App\Models\PaymentWebhookEvent;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MercadoPagoService
{
    private const API = 'https://api.mercadopago.com';

    public function createCheckout(Subscription $subscription): array
    {
        $payment = SubscriptionPayment::create([
            'subscription_id' => $subscription->id, 'user_id' => $subscription->user_id,
            'external_reference' => (string) Str::uuid(), 'amount' => $subscription->price,
        ]);
        $preferencePayload = [
            'items' => [['id' => (string) $subscription->subscription_plan_id, 'title' => 'Assinatura LuziCity - '.$subscription->plan->name, 'quantity' => 1, 'currency_id' => 'BRL', 'unit_price' => (float) $subscription->price]],
            'payer' => ['email' => $subscription->user->email], 'external_reference' => $payment->external_reference,
            'notification_url' => route('payments.webhook', ['source' => 'checkout']),
            'back_urls' => ['success' => route('subscriber.payment.return', ['status' => 'success']), 'pending' => route('subscriber.payment.return', ['status' => 'pending']), 'failure' => route('subscriber.payment.return', ['status' => 'failure'])],
            'auto_return' => 'approved',
        ];
        if ($subscription->auto_renew) {
            $frequency = $subscription->billing_cycle === 'yearly' ? 12 : 1;
            $response = Http::withToken($this->token())->acceptJson()->post(self::API.'/preapproval', [
                'reason' => 'Assinatura LuziCity - '.$subscription->plan->name,
                'external_reference' => $payment->external_reference, 'payer_email' => $subscription->user->email,
                'auto_recurring' => ['frequency' => $frequency, 'frequency_type' => 'months', 'transaction_amount' => (float) $subscription->price, 'currency_id' => 'BRL'],
                'back_url' => route('subscriber.payment.return', ['status' => 'success']),
                'notification_url' => route('payments.webhook', ['source' => 'subscription']),
                'status' => 'pending',
            ])->throw()->json();
        } else {
            $response = Http::withToken($this->token())->acceptJson()->post(self::API.'/checkout/preferences', $preferencePayload)->throw()->json();
        }
        $payment->update(['preference_id' => $response['id'] ?? null, 'provider_data' => ['preference' => $response['id'] ?? null]]);

        return ['url' => $subscription->auto_renew ? ($response['init_point'] ?? null) : (config('services.mercado_pago.sandbox') ? ($response['sandbox_init_point'] ?? null) : ($response['init_point'] ?? null)), 'payment' => $payment];
    }

    public function validateSignature(Request $request): bool
    {
        $secret = (string) config('services.mercado_pago.webhook_secret');
        if ($secret === '') {
            return false;
        }
        $parts = collect(explode(',', (string) $request->header('x-signature')))->mapWithKeys(function ($part) {
            $pair = explode('=', trim($part), 2);

            return count($pair) === 2 ? [$pair[0] => $pair[1]] : [];
        });
        $dataId = strtolower((string) ($request->query('data.id') ?? $request->input('data.id')));
        $manifest = "id:{$dataId};request-id:{$request->header('x-request-id')};ts:{$parts->get('ts')};";

        return filled($parts->get('v1')) && hash_equals(hash_hmac('sha256', $manifest, $secret), $parts->get('v1'));
    }

    public function processWebhook(Request $request): void
    {
        $resourceId = (string) ($request->query('data.id') ?? $request->input('data.id'));
        $key = $request->header('x-request-id').':'.$resourceId.':'.$request->input('action');
        $event = PaymentWebhookEvent::firstOrCreate(['event_key' => $key], ['provider' => 'mercado_pago', 'event_type' => $request->input('type'), 'payload' => $request->all()]);
        if ($event->processed_at) {
            return;
        }
        $resource = Http::withToken($this->token())->acceptJson()->get(self::API.'/v1/payments/'.$resourceId)->throw()->json();
        DB::transaction(function () use ($event, $resource, $resourceId): void {
            $payment = SubscriptionPayment::where('external_reference', $resource['external_reference'] ?? '')->firstOrFail();
            $status = match ($resource['status'] ?? '') {
                'approved' => 'paid', 'refunded' => 'refunded', 'cancelled', 'rejected' => 'failed', default => 'pending'
            };
            $payment->update(['provider_payment_id' => $resourceId, 'status' => $status, 'paid_at' => $status === 'paid' ? now() : $payment->paid_at, 'provider_data' => $resource]);
            if ($status === 'paid') {
                $payment->subscription()->update(['status' => 'active', 'starts_at' => now()]);
            }
            $event->update(['status' => 'processed', 'processed_at' => now()]);
        });
    }

    public function refund(SubscriptionPayment $payment, float $amount, string $reason, int $userId): void
    {
        if (! $payment->provider_payment_id || $amount <= 0 || $amount > ((float) $payment->amount - (float) $payment->refunded_amount)) {
            throw new RuntimeException('Valor de reembolso inválido.');
        }
        $response = Http::withToken($this->token())->withHeaders(['X-Idempotency-Key' => (string) Str::uuid()])->post(self::API.'/v1/payments/'.$payment->provider_payment_id.'/refunds', ['amount' => $amount])->throw()->json();
        $payment->refunds()->create(['requested_by' => $userId, 'provider_refund_id' => (string) ($response['id'] ?? ''), 'amount' => $amount, 'status' => 'approved', 'reason' => $reason, 'provider_data' => $response]);
        $payment->increment('refunded_amount', $amount);
        if ((float) $payment->fresh()->refunded_amount >= (float) $payment->amount) {
            $payment->update(['status' => 'refunded']);
        }
    }

    private function token(): string
    {
        return (string) config('services.mercado_pago.access_token') ?: throw new RuntimeException('Configure MERCADO_PAGO_ACCESS_TOKEN.');
    }
}
