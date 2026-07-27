<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PaywallAccess;
use App\Models\PaywallCategoryRule;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaywallController extends Controller
{
    public function index(): View
    {
        return view('admin.paywall.index', [
            'categories' => Category::with('paywallRule')->orderBy('name')->get(),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('display_order')->get(),
            'metrics' => [
                'protected_categories' => PaywallCategoryRule::where('is_enabled', true)->count(),
                'monthly_accesses' => PaywallAccess::whereDate('period_month', today()->startOfMonth())->count(),
                'readers' => PaywallAccess::whereDate('period_month', today()->startOfMonth())->distinct()->count('user_id'),
            ],
        ]);
    }

    public function updateRule(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'minimum_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'preview_characters' => ['required', 'integer', 'between:100,5000'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');
        PaywallCategoryRule::updateOrCreate(['category_id' => $category->id], $data);

        return back()->with('status', 'Regra de paywall atualizada.');
    }
}
