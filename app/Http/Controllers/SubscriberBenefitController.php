<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionBenefit;
use App\Services\SubscriptionBenefitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriberBenefitController extends Controller
{
    public function index(SubscriptionBenefitService $service): View
    {
        return view('subscriptions.benefits', ['benefits' => $service->eligibleFor(auth()->user())]);
    }

    public function redeem(SubscriptionBenefit $benefit, SubscriptionBenefitService $service): RedirectResponse
    {
        $service->redeem($benefit, auth()->user());

        return back()->with('status', 'Benefício resgatado.');
    }
}
