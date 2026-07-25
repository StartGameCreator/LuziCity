<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSocialLinkController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.social-links.edit', [
            'socialLinks' => Setting::socialLinks(),
            'shopUrl' => Setting::shopUrl(),
            'localCommerceUrl' => Setting::localCommerceUrl(),
            'googleAds' => Setting::googleAds(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $keys = array_keys(config('luzicity.social_links', []));
        $rules = [
            'shop_url' => ['nullable', 'url', 'max:2048'],
            'local_commerce_url' => ['nullable', 'url', 'max:2048'],
            'google_ads_client' => ['nullable', 'string', 'max:80'],
        ];

        foreach ($keys as $key) {
            $rules["links.$key"] = ['nullable', 'url', 'max:2048'];
        }

        foreach (array_keys(config('luzicity.google_ads.slots', [])) as $slot) {
            $rules["google_ads_slots.$slot"] = ['nullable', 'string', 'max:80'];
        }

        $data = $request->validate($rules);

        foreach ($keys as $key) {
            Setting::query()->updateOrCreate(
                ['group' => 'social_links', 'key' => $key],
                ['value' => $data['links'][$key] ?? null]
            );
        }

        Setting::query()->updateOrCreate(
            ['group' => 'general', 'key' => 'shop_url'],
            ['value' => $data['shop_url'] ?? null]
        );

        Setting::query()->updateOrCreate(
            ['group' => 'general', 'key' => 'local_commerce_url'],
            ['value' => $data['local_commerce_url'] ?? null]
        );

        Setting::query()->updateOrCreate(
            ['group' => 'google_ads', 'key' => 'client'],
            ['value' => $data['google_ads_client'] ?? null]
        );

        foreach (array_keys(config('luzicity.google_ads.slots', [])) as $slot) {
            Setting::query()->updateOrCreate(
                ['group' => 'google_ads', 'key' => "slot_{$slot}"],
                ['value' => $data['google_ads_slots'][$slot] ?? null]
            );
        }

        return back()->with('status', 'Configurações atualizadas.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
