<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionPaymentController extends Controller
{
    public function checkout(Request $request, MercadoPagoService $gateway): RedirectResponse
    {
        $subscription = $request->user()->subscription?->load(['plan', 'user']);
        abort_unless($subscription && (float) $subscription->price > 0, 422, 'Assinatura sem cobrança configurada.');
        $checkout = $gateway->createCheckout($subscription);
        abort_unless($checkout['url'], 502, 'Gateway não retornou a URL de pagamento.');

        return redirect()->away($checkout['url']);
    }

    public function returned(Request $request): RedirectResponse
    {
        return redirect()->route('subscriber.show')->with('status', match ($request->string('status')->toString()) {
            'success' => 'Pagamento recebido. A confirmação será concluída pelo gateway.',
            'pending' => 'Pagamento pendente de confirmação.',
            default => 'Pagamento não concluído.',
        });
    }

    public function webhook(Request $request, MercadoPagoService $gateway): Response
    {
        abort_unless($gateway->validateSignature($request), 401);
        $gateway->processWebhook($request);

        return response('', 200);
    }

    public function refund(Request $request, SubscriptionPayment $payment, MercadoPagoService $gateway): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01'], 'reason' => ['required', 'string', 'min:5', 'max:2000']]);
        $gateway->refund($payment, (float) $data['amount'], $data['reason'], auth()->id());

        return back()->with('status', 'Reembolso solicitado e confirmado pelo gateway.');
    }
}
