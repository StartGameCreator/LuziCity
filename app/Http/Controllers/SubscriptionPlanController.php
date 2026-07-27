<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        return view('subscriptions.plans', [
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }
}
