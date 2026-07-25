<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSiteContentController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.site-content.edit', [
            'aboutContent' => Setting::aboutContent(),
            'aiSettings' => Setting::aiSettings(),
            'visualBlocks' => Setting::visualBlocks(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'about_content' => ['required', 'string', 'max:20000'],
            'ai_provider' => ['required', 'in:chatgpt,gemini,copilot'],
            'openai_api_key' => ['nullable', 'string', 'max:4000'],
            'chatgpt_model' => ['nullable', 'string', 'max:120'],
            'gemini_api_key' => ['nullable', 'string', 'max:4000'],
            'gemini_model' => ['nullable', 'string', 'max:120'],
            'copilot_api_key' => ['nullable', 'string', 'max:4000'],
            'copilot_endpoint' => ['nullable', 'url', 'max:2048'],
            'visual_blocks' => ['nullable', 'array'],
            'visual_blocks.events.label' => ['nullable', 'string', 'max:120'],
            'visual_blocks.events.link' => ['nullable', 'string', 'max:2048'],
            'visual_blocks.events.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'visual_blocks.real_estate.label' => ['nullable', 'string', 'max:120'],
            'visual_blocks.real_estate.link' => ['nullable', 'string', 'max:2048'],
            'visual_blocks.real_estate.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'visual_blocks.vehicles.label' => ['nullable', 'string', 'max:120'],
            'visual_blocks.vehicles.link' => ['nullable', 'string', 'max:2048'],
            'visual_blocks.vehicles.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        Setting::query()->updateOrCreate(
            ['group' => 'site_content', 'key' => 'about_content'],
            ['value' => $data['about_content']]
        );

        $aiValues = [
            'provider' => $data['ai_provider'],
            'openai_api_key' => $data['openai_api_key'] ?? null,
            'chatgpt_model' => $data['chatgpt_model'] ?? null,
            'gemini_api_key' => $data['gemini_api_key'] ?? null,
            'gemini_model' => $data['gemini_model'] ?? null,
            'copilot_api_key' => $data['copilot_api_key'] ?? null,
            'copilot_endpoint' => $data['copilot_endpoint'] ?? null,
        ];

        foreach ($aiValues as $key => $value) {
            if (str_ends_with($key, '_api_key') && blank($value)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['group' => 'ai', 'key' => $key],
                ['value' => $value]
            );
        }

        foreach (Setting::visualBlocks() as $blockKey => $currentBlock) {
            $blockData = $data['visual_blocks'][$blockKey] ?? [];
            $imagePath = $currentBlock['image'] ?? '';

            if ($request->hasFile("visual_blocks.{$blockKey}.image")) {
                $imagePath = $this->storeVisualBlockImage($request, $blockKey);
            }

            Setting::updateVisualBlock($blockKey, [
                'label' => $blockData['label'] ?? $currentBlock['label'] ?? '',
                'link' => $blockData['link'] ?? $currentBlock['link'] ?? '#',
                'image' => $imagePath,
            ]);
        }

        return back()->with('status', 'Conteúdo do site atualizado.');
    }

    private function storeVisualBlockImage(Request $request, string $blockKey): string
    {
        $file = $request->file("visual_blocks.{$blockKey}.image");
        $directory = public_path('images/visual-blocks');
        File::ensureDirectoryExists($directory);

        $filename = $blockKey.'-'.Str::uuid().'.'.$file->extension();
        $file->move($directory, $filename);

        return 'images/visual-blocks/'.$filename;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
