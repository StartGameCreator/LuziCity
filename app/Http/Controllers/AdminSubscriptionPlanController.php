<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSubscriptionPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.subscription-plans.index', [
            'plans' => SubscriptionPlan::withCount('subscriptions')->orderBy('display_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SubscriptionPlan::create($this->validated($request));

        return back()->with('status', 'Plano criado.');
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));

        return back()->with('status', 'Plano atualizado.');
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'unique:subscription_plans,slug,'.$plan?->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'benefits_text' => ['nullable', 'string', 'max:5000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_ad_free' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'is_featured' => ['nullable', 'boolean'],
            'can_access_premium' => ['nullable', 'boolean'],
            'monthly_article_limit' => ['nullable', 'integer', 'min:1'],
            'preview_characters' => ['required', 'integer', 'between:100,5000'],
        ]);
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['benefits'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['benefits_text'] ?? ''))));
        $data['display_order'] = $data['display_order'] ?? 0;
        $data['is_ad_free'] = $request->boolean('is_ad_free');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['can_access_premium'] = $request->boolean('can_access_premium');
        unset($data['benefits_text']);

        return $data;
    }
}
