<?php

namespace Tests\Feature;

use App\Models\PaymentWebhookEvent;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\MercadoPagoService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function subscriber(): User
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('slug', 'premium')->firstOrFail();
        app(SubscriptionService::class)->update($user, [
            'subscription_plan_id' => $plan->id, 'status' => 'inactive', 'billing_cycle' => 'monthly',
            'price' => $plan->monthly_price, 'auto_renew' => false,
        ], $user);

        return $user;
    }

    public function test_checkout_and_signed_webhook_are_idempotent(): void
    {
        config(['services.mercado_pago.access_token' => 'TEST', 'services.mercado_pago.webhook_secret' => 'secret', 'services.mercado_pago.sandbox' => true]);
        Http::fake([
            'api.mercadopago.com/checkout/preferences' => Http::response(['id' => 'PREF-1', 'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout'], 201),
            'api.mercadopago.com/v1/payments/987' => function () {
                $payment = SubscriptionPayment::firstOrFail();

                return Http::response(['id' => 987, 'status' => 'approved', 'external_reference' => $payment->external_reference, 'transaction_amount' => (float) $payment->amount], 200);
            },
        ]);
        $user = $this->subscriber();
        $this->actingAs($user)->post(route('subscriber.payment.checkout'))->assertRedirect('https://sandbox.mercadopago.com/checkout');
        $requestId = 'request-1';
        $ts = '1704908010';
        $dataId = '987';
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $signature = 'ts='.$ts.',v1='.hash_hmac('sha256', $manifest, 'secret');
        $headers = ['x-signature' => $signature, 'x-request-id' => $requestId];
        $url = route('payments.webhook', ['data.id' => $dataId]);
        $payload = ['type' => 'payment', 'action' => 'payment.updated', 'data' => ['id' => $dataId]];
        $this->withHeaders($headers)->postJson($url, $payload)->assertOk();
        $this->withHeaders($headers)->postJson($url, $payload)->assertOk();
        $this->assertSame('paid', SubscriptionPayment::first()->status);
        $this->assertSame('active', $user->fresh()->subscription->status);
        $this->assertSame(1, PaymentWebhookEvent::count());
    }

    public function test_partial_and_full_refunds_are_recorded(): void
    {
        config(['services.mercado_pago.access_token' => 'TEST']);
        Http::fake(['api.mercadopago.com/v1/payments/123/refunds' => Http::sequence()
            ->push(['id' => 'R1'], 201)->push(['id' => 'R2'], 201)]);
        $user = $this->subscriber();
        $payment = SubscriptionPayment::create([
            'subscription_id' => $user->subscription->id, 'user_id' => $user->id, 'external_reference' => 'REF-1',
            'provider_payment_id' => '123', 'status' => 'paid', 'amount' => 100,
        ]);
        $gateway = app(MercadoPagoService::class);
        $gateway->refund($payment, 40, 'Solicitação do cliente', $user->id);
        $gateway->refund($payment->fresh(), 60, 'Saldo restante', $user->id);
        $this->assertSame('100.00', $payment->fresh()->refunded_amount);
        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertDatabaseCount('subscription_payment_refunds',2);
    }
}
