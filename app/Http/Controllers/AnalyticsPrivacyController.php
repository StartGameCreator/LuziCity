<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsConsent;
use App\Models\AnalyticsPageview;
use App\Models\PrivacyDataRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsPrivacyController extends Controller
{
    public function show(): View
    {
        return view('privacy.analytics');
    }

    public function consent(Request $request): RedirectResponse
    {
        $data = $request->validate(['choice' => ['required', 'in:accepted,denied']]);
        $hash = $this->sessionHash($request);
        AnalyticsConsent::create([
            'user_id' => $request->user()?->id, 'session_hash' => $hash, 'choice' => $data['choice'],
            'policy_version' => config('analytics.policy_version'), 'consented_at' => now(),
        ]);

        return back()->withCookie(cookie('luzicity_analytics_consent', $data['choice'], 60 * 24 * 180, '/', null, $request->isSecure(), false, false, 'Lax'))
            ->with('status', $data['choice'] === 'accepted' ? 'Preferência de analytics salva.' : 'Coleta opcional desativada.');
    }

    public function optOut(Request $request): RedirectResponse
    {
        $hash = $this->sessionHash($request);
        AnalyticsPageview::where('session_hash', $hash)->orWhere(fn ($q) => $request->user() ? $q->where('user_id', $request->user()->id) : $q->whereRaw('1 = 0'))->delete();
        PrivacyDataRequest::create(['user_id' => $request->user()?->id, 'session_hash' => $hash, 'type' => 'analytics_opt_out', 'status' => 'completed', 'completed_at' => now()]);
        AnalyticsConsent::create(['user_id' => $request->user()?->id, 'session_hash' => $hash, 'choice' => 'denied', 'policy_version' => config('analytics.policy_version'), 'consented_at' => now()]);

        return back()->withCookie(cookie('luzicity_analytics_consent', 'denied', 60 * 24 * 180, '/', null, $request->isSecure(), false, false, 'Lax'))
            ->with('status', 'Opt-out concluído e dados de analytics associados removidos.');
    }

    private function sessionHash(Request $request): string
    {
        return hash_hmac('sha256', $request->session()->getId(), config('app.key'));
    }
}
