<?php

namespace App\Http\Controllers;

use App\Models\AiProvider;
use App\Models\User;
use App\Services\AI\AiEditorialMetricsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiDashboardController extends Controller
{
    public function __invoke(Request $request, AiEditorialMetricsService $metrics): View
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:today,week,month,custom'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'provider_id' => ['nullable', 'integer', 'exists:ai_providers,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $restrictedUserId = $user->hasAnyRole(['Super Admin', 'Admin']) ? null : $user->id;

        return view('admin.ai.dashboard', [
            'metrics' => $metrics->dashboard($filters, $restrictedUserId),
            'filters' => $filters,
            'providers' => AiProvider::query()->orderBy('name')->get(['id', 'name']),
            'users' => $restrictedUserId ? collect() : User::query()->whereHas('roles', fn ($query) => $query->whereIn('name', ['Super Admin', 'Admin', 'Jornalista']))->orderBy('name')->get(['id', 'name']),
            'restricted' => $restrictedUserId !== null,
        ]);
    }
}
