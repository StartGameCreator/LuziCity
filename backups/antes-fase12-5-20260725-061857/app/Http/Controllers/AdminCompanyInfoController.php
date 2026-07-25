<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCompanyInfoController extends Controller
{
    public function edit(): View
    {
        $this->authorizeAdmin();

        return view('admin.company-info.edit', [
            'companyInfo' => Setting::companyInfo(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:80'],
            'site_logo_data' => ['nullable', 'string'],
            'site_favicon_data' => ['nullable', 'string'],
            'default_share_image_data' => ['nullable', 'string'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'whatsapp_secondary' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'email_secondary' => ['nullable', 'email', 'max:255'],
            'email_tertiary' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ([
            'site_logo' => ['label' => 'A logo do topo', 'max' => 64 * 1024 * 1024],
            'site_favicon' => ['label' => 'O favicon', 'max' => 16 * 1024 * 1024],
            'default_share_image' => ['label' => 'A imagem de compartilhamento', 'max' => 64 * 1024 * 1024],
        ] as $field => $rules) {
            $file = $request->file($field);

            if (! $file) {
                continue;
            }

            if (! $file->isValid()) {
                return back()
                    ->withErrors([$field => "{$rules['label']} nao foi recebida corretamente pelo PHP. Erro: ".$file->getErrorMessage()])
                    ->withInput();
            }

            if (strtolower($file->getClientOriginalExtension()) !== 'png') {
                return back()
                    ->withErrors([$field => "{$rules['label']} precisa estar em PNG."])
                    ->withInput();
            }

            if ($file->getSize() > $rules['max']) {
                return back()
                    ->withErrors([$field => "{$rules['label']} esta maior que o limite permitido."])
                    ->withInput();
            }
        }

        $current = Setting::companyInfo();

        foreach (['site_logo', 'site_favicon', 'default_share_image'] as $field) {
            if (filled($data["{$field}_data"] ?? null)) {
                $data[$field] = $this->storeIdentityImageData($data["{$field}_data"], $field);
            } elseif ($request->hasFile($field)) {
                $data[$field] = $this->storeIdentityImage($request, $field);
            } else {
                $data[$field] = $current[$field] ?? '';
            }
        }

        foreach (array_keys(Setting::companyInfo()) as $key) {
            Setting::query()->updateOrCreate(
                ['group' => 'company_info', 'key' => $key],
                ['value' => $data[$key] ?? null]
            );
        }

        return back()->with('status', 'Dados da empresa atualizados.');
    }

    private function storeIdentityImage(Request $request, string $field): string
    {
        $file = $request->file($field);
        $directory = public_path('images/identity');
        File::ensureDirectoryExists($directory);

        $filename = $field.'-'.Str::uuid().'.png';
        $file->move($directory, $filename);

        return 'images/identity/'.$filename;
    }

    private function storeIdentityImageData(string $imageData, string $field): string
    {
        if (! str_starts_with($imageData, 'data:image/png;base64,')) {
            abort(422, 'A imagem precisa estar em PNG.');
        }

        $decoded = base64_decode(substr($imageData, strlen('data:image/png;base64,')), true);

        if ($decoded === false) {
            abort(422, 'A imagem PNG nao pode ser lida.');
        }

        $directory = public_path('images/identity');
        File::ensureDirectoryExists($directory);

        $filename = $field.'-'.Str::uuid().'.png';
        File::put($directory.DIRECTORY_SEPARATOR.$filename, $decoded);

        return 'images/identity/'.$filename;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
