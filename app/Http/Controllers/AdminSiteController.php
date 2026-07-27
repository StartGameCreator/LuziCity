<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSiteController extends Controller
{
    public function index(): View
    {
        return view('admin.sites.index', ['sites' => Site::with(['domains', 'settings'])->orderByDesc('is_default')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($request, $data): void {
            $site = Site::create($this->siteValues($request, $data));
            $this->syncRelations($site, $data);
            $this->normalizeDefault($site);
        });

        return back()->with('status', 'Site criado.');
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $data = $this->validated($request, $site);
        DB::transaction(function () use ($request, $data, $site): void {
            $site->update($this->siteValues($request, $data, $site));
            $this->syncRelations($site, $data);
            $this->normalizeDefault($site);
        });

        return back()->with('status', 'Site atualizado.');
    }

    private function validated(Request $request, ?Site $site = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('sites')->ignore($site)],
            'city' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'string', 'size:2'],
            'domains' => ['required', 'string', 'max:4000'],
            'theme_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_secondary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'image', 'max:8192'],
            'favicon' => ['nullable', 'image', 'max:2048'],
            'theme_background' => ['nullable', 'image', 'max:8192'],
            'settings' => ['nullable', 'string', 'max:10000'],
        ]);
    }

    private function siteValues(Request $request, array $data, ?Site $site = null): array
    {
        $values = [
            'name' => $data['name'], 'slug' => $data['slug'] ?: Str::slug($data['name']),
            'city' => $data['city'] ?? null, 'state' => strtoupper($data['state'] ?? '') ?: null,
            'theme_primary' => strtolower($data['theme_primary']), 'theme_secondary' => strtolower($data['theme_secondary']),
            'is_active' => $request->boolean('is_active'), 'is_default' => $request->boolean('is_default'),
        ];
        foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path', 'theme_background' => 'theme_background_path'] as $input => $column) {
            $values[$column] = $request->hasFile($input)
                ? 'storage/'.$request->file($input)->store('sites/identity', 'public') : $site?->{$column};
        }

        return $values;
    }

    private function syncRelations(Site $site, array $data): void
    {
        $domains = collect(preg_split('/[\r\n,]+/', $data['domains']))->map(fn ($domain) => strtolower(trim($domain)))
            ->filter()->map(fn ($domain) => preg_replace('/:\d+$/', '', $domain))->unique()->values();
        abort_if($domains->contains(fn ($domain) => ! preg_match('/^(localhost|[a-z0-9.-]+\.[a-z]{2,})$/', $domain)), 422, 'Informe domínios sem protocolo ou caminho.');
        abort_if(SiteDomain::whereIn('domain', $domains)->where('site_id', '!=', $site->id)->exists(), 422, 'Um dos domínios já pertence a outro site.');
        $site->domains()->delete();
        $domains->each(fn ($domain, $index) => $site->domains()->create(['domain' => $domain, 'is_primary' => $index === 0]));

        $settings = collect(preg_split('/\r\n|\r|\n/', $data['settings'] ?? ''))->mapWithKeys(function ($line): array {
            [$key, $value] = array_pad(explode('=', $line, 2), 2, null);

            return filled(trim((string) $key)) ? [trim($key) => trim((string) $value)] : [];
        });
        $site->settings()->delete();
        $settings->each(fn ($value, $key) => $site->settings()->create(['key' => $key, 'value' => $value]));
    }

    private function normalizeDefault(Site $site): void
    {
        if ($site->is_default) {
            Site::where('id', '!=', $site->id)->update(['is_default' => false]);
        } elseif (! Site::where('is_default', true)->exists()) {
            $site->update(['is_default' => true]);
        }
    }
}
