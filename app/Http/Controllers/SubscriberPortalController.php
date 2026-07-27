<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberPortalController extends Controller
{
    public function show(): View
    {
        return view('subscriptions.account', [
            'subscription' => auth()->user()->subscription?->load(['plan', 'histories.fromPlan', 'histories.toPlan', 'payments.refunds']),
        ]);
    }

    public function cancel(Request $request, SubscriptionService $subscriptions): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:2000']]);
        $subscription = $request->user()->subscription;
        abort_unless($subscription && ! in_array($subscription->status, ['cancelled', 'inactive'], true), 422, 'Não existe assinatura cancelável.');
        $subscriptions->cancel($subscription, $request->user(), $data['reason']);

        return back()->with('status', 'Assinatura cancelada.');
    }
}
