<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:80'],
        ]);

        $subscription = PushSubscription::query()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()?->id,
                'site_id' => Site::current()?->id,
                'device_name' => $validated['device_name'] ?? null,
                'platform' => $validated['platform'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['saved' => true, 'id' => $subscription->id]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate(['token' => ['required', 'string', 'max:4096']]);
        PushSubscription::query()->where('token', $validated['token'])->delete();

        return response()->json(['removed' => true]);
    }
}
