<?php

namespace App\Http\Controllers;

use App\Models\MediaBanner;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMediaBannerController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.media-banners.index', [
            'banners' => MediaBanner::query()
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'typeLabels' => MediaBanner::typeLabels(),
            'homeLiveBroadcast' => Setting::homeLiveBroadcast(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        MediaBanner::query()->create($this->validatedData($request));

        return back()->with('status', 'Banner de mídia salvo.');
    }

    public function update(Request $request, MediaBanner $mediaBanner): RedirectResponse
    {
        $this->authorizeAdmin();

        $mediaBanner->update($this->validatedData($request));

        return back()->with('status', 'Banner de mídia atualizado.');
    }

    public function updateHomeLiveBroadcast(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'provider' => ['required', 'in:tiktok,dlive'],
            'orientation' => ['required', 'in:portrait,landscape'],
            'title' => ['nullable', 'string', 'max:160'],
            'embed_code' => ['nullable', 'string', 'max:12000'],
            'external_url' => ['nullable', 'url', 'max:500'],
        ]);

        Setting::updateHomeLiveBroadcast([
            'enabled' => $request->boolean('enabled'),
            'provider' => $data['provider'],
            'orientation' => $data['orientation'],
            'title' => $data['title'] ?: 'Transmissao especial ao vivo',
            'embed_code' => $data['embed_code'] ?? '',
            'external_url' => $data['external_url'] ?? '',
        ]);

        return back()->with('status', 'Transmissao especial da home atualizada.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:youtube,facebook_reel,vehicle_youtube'],
            'title' => ['required', 'string', 'max:160'],
            'embed_code' => ['nullable', 'string', 'max:12000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
