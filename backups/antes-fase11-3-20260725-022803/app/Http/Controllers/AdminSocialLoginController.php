<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSocialLoginController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.social-login.edit', [
            'providers' => Setting::socialLoginProviders(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $providers = Setting::socialLoginProviders();
        $data = [];

        foreach ($providers as $key => $provider) {
            $validated = $request->validate([
                "{$key}.client_id" => ['nullable', 'string', 'max:2048'],
                "{$key}.client_secret" => ['nullable', 'string', 'max:4096'],
                "{$key}.redirect" => ['nullable', 'url', 'max:2048'],
                "{$key}.enabled" => ['nullable', 'boolean'],
            ]);

            $submitted = $validated[$key] ?? [];

            $data[$key] = [
                'enabled' => $request->boolean("{$key}.enabled"),
                'client_id' => $submitted['client_id'] ?? '',
                'client_secret' => filled($submitted['client_secret'] ?? null)
                    ? $submitted['client_secret']
                    : ($provider['client_secret'] ?? ''),
                'redirect' => $submitted['redirect'] ?? url("/login/{$key}/callback"),
            ];
        }

        Setting::updateSocialLoginProviders($data);

        return back()->with('status', 'Configurações de login social atualizadas.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
