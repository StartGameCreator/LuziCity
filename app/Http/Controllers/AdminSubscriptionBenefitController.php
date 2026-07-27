<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriptionBenefitController extends Controller
{
    public function index(): View
    {
        return view('admin.subscription-benefits.index', [
            'benefits' => SubscriptionBenefit::with('plans')->latest()->get(),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $plans = $data['plan_ids'];
        unset($data['plan_ids']);
        $benefit = SubscriptionBenefit::create($data);
        $benefit->plans()->sync($plans);

        return back()->with('status', 'Benefício criado.');
    }

    public function update(Request $request, SubscriptionBenefit $benefit): RedirectResponse
    {
        $data = $this->validated($request, $benefit);
        $plans = $data['plan_ids'];
        unset($data['plan_ids']);
        $benefit->update($data);
        $benefit->plans()->sync($plans);

        return back()->with('status', 'Benefício atualizado.');
    }

    private function validated(Request $request, ?SubscriptionBenefit $benefit = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'], 'type' => ['required', 'in:content,event,coupon,newsletter,podcast'],
            'description' => ['nullable', 'string', 'max:5000'], 'code' => ['nullable', 'string', 'max:80', 'unique:subscription_benefits,code,'.$benefit?->id],
            'destination_url' => ['nullable', 'url', 'max:2048'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'], 'is_active' => ['nullable', 'boolean'],
            'plan_ids' => ['required', 'array', 'min:1'], 'plan_ids.*' => ['exists:subscription_plans,id'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
