<?php

namespace App\Http\Controllers;

use App\Models\RadioRequest;
use App\Models\Setting;
use App\Services\Security\EmbedCodeSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRadioController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.radio.edit', [
            'radioSettings' => Setting::radioSettings(),
            'requests' => RadioRequest::query()
                ->latest()
                ->take(30)
                ->get(),
            'chatCategories' => RadioRequest::categoryOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'tiktok_embed_code' => ['nullable', 'string', 'max:12000'],
            'tiktok_url' => ['nullable', 'url', 'max:2048'],
            'tiktok_orientation' => ['required', 'in:portrait,landscape'],
            'audio_stream_url' => ['nullable', 'url', 'max:2048'],
            'schedule_text' => ['nullable', 'string', 'max:2000'],
            'field_live_enabled' => ['nullable', 'boolean'],
            'field_live_title' => ['nullable', 'string', 'max:160'],
            'field_live_description' => ['nullable', 'string', 'max:1000'],
            'field_video_embed_code' => ['nullable', 'string', 'max:12000'],
            'field_video_url' => ['nullable', 'url', 'max:2048'],
            'field_audio_stream_url' => ['nullable', 'url', 'max:2048'],
            'field_rtmp_server' => ['nullable', 'string', 'max:2048'],
            'field_rtmp_key' => ['nullable', 'string', 'max:2048'],
            'field_reporter_whatsapp' => ['nullable', 'string', 'max:40'],
            'field_return_link' => ['nullable', 'url', 'max:2048'],
            'field_team_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['field_live_enabled'] = $request->boolean('field_live_enabled') ? '1' : '0';
        $data['tiktok_embed_code'] = EmbedCodeSanitizer::sanitize($data['tiktok_embed_code'] ?? null);
        $data['field_video_embed_code'] = EmbedCodeSanitizer::sanitize($data['field_video_embed_code'] ?? null);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(
                ['group' => 'radio', 'key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('status', 'Configuração da rádio atualizada.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
