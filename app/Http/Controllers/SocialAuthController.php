<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless($this->isSupported($provider), 404);
        $this->applyProviderConfig($provider);

        if (! $this->isConfigured($provider)) {
            return back()->withErrors([
                'social' => 'Configure as credenciais de '.$this->label($provider).' antes de usar este login.',
            ]);
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Throwable) {
            return back()->withErrors([
                'social' => 'O provedor '.$this->label($provider).' precisa de um adaptador Socialite especifico.',
            ]);
        }
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless($this->isSupported($provider), 404);
        $this->applyProviderConfig($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'social' => 'Nao foi possivel concluir o login social.',
            ]);
        }

        $account = SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $socialUser->getId())
            ->first();

        $user = $account?->user ?: User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: $this->label($provider).' User',
                'password' => Hash::make(str()->random(40)),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('Usuario');

        $user->socialAccounts()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_user_id' => $socialUser->getId(),
            ],
            [
                'provider_email' => $socialUser->getEmail(),
                'provider_name' => $socialUser->getName(),
                'avatar_url' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken ?? null,
                'last_used_at' => now(),
            ]
        );

        $user->forceFill(['last_login_at' => now()])->save();

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    private function isSupported(string $provider): bool
    {
        return array_key_exists($provider, config('luzicity.social_providers'));
    }

    private function isConfigured(string $provider): bool
    {
        $settings = Setting::socialLoginProvider($provider);

        return ! empty($settings['enabled'])
            && filled(config("services.$provider.client_id"))
            && filled(config("services.$provider.client_secret"))
            && filled(config("services.$provider.redirect"));
    }

    private function applyProviderConfig(string $provider): void
    {
        $settings = Setting::socialLoginProvider($provider);

        if (! $settings) {
            return;
        }

        config([
            "services.$provider.client_id" => $settings['client_id'] ?? '',
            "services.$provider.client_secret" => $settings['client_secret'] ?? '',
            "services.$provider.redirect" => $settings['redirect'] ?? url("/login/{$provider}/callback"),
        ]);
    }

    private function label(string $provider): string
    {
        return config("luzicity.social_providers.$provider.label", ucfirst($provider));
    }
}
