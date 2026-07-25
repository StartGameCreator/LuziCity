<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTrackingPixelController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.tracking-pixels.edit', [
            'trackingPixels' => Setting::trackingPixels(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'meta_pixel_id' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        foreach (['meta_pixel_id', 'tiktok_pixel_id'] as $key) {
            Setting::query()->updateOrCreate(
                ['group' => 'tracking_pixels', 'key' => $key],
                ['value' => $data[$key] ?? null]
            );
        }

        return back()->with('status', 'Pixels atualizados.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
