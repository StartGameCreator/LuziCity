<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\Push\FirebaseCloudMessaging;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminPushNotificationController extends Controller
{
    public function index(FirebaseCloudMessaging $firebase): View
    {
        return view('admin.push-notifications.index', [
            'subscriptions' => PushSubscription::query()->latest('last_seen_at')->paginate(30),
            'firebaseConfigured' => $firebase->configured(),
        ]);
    }

    public function send(Request $request, FirebaseCloudMessaging $firebase): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:1000'],
        ]);
        abort_unless($firebase->configured(), 422, 'Firebase ainda não configurado.');

        $sent = 0;
        $failed = 0;
        PushSubscription::query()->orderBy('id')->chunkById(100, function ($items) use ($firebase, $validated, &$sent, &$failed): void {
            foreach ($items as $subscription) {
                try {
                    $firebase->send($subscription->token, $validated['title'], $validated['body'], $validated['url'] ?: '/');
                    $sent++;
                } catch (Throwable) {
                    $failed++;
                }
            }
        });

        return back()->with('status', "Notificação enviada para {$sent} dispositivo(s). Falhas: {$failed}.");
    }
}
