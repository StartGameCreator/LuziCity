<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\VehicleListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminVehicleClassifiedController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.vehicle-classifieds.index', [
            'settings' => Setting::vehicleClassifiedSettings(),
            'vehicleTypes' => Setting::vehicleTypeOptions(),
            'brandLogosText' => collect(Setting::vehicleTypeOptions())
                ->keys()
                ->mapWithKeys(fn (string $type) => [$type => Setting::vehicleBrandLogosText($type)])
                ->all(),
            'vehicles' => VehicleListing::query()
                ->with('user')
                ->latest()
                ->take(40)
                ->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'limit_enabled' => ['nullable', 'boolean'],
            'max_active_listings' => ['required', 'integer', 'min:1', 'max:999'],
            'brand_logos' => ['nullable', 'array'],
            'brand_logos.*' => ['nullable', 'string', 'max:12000'],
        ]);

        Setting::query()->updateOrCreate(
            ['group' => 'vehicle_classifieds', 'key' => 'limit_enabled'],
            ['value' => $request->boolean('limit_enabled') ? '1' : '0']
        );

        Setting::query()->updateOrCreate(
            ['group' => 'vehicle_classifieds', 'key' => 'max_active_listings'],
            ['value' => (string) $data['max_active_listings']]
        );

        foreach (array_keys(Setting::vehicleTypeOptions()) as $type) {
            Setting::query()->updateOrCreate(
                ['group' => 'vehicle_classifieds', 'key' => 'brand_logos_'.$type],
                ['value' => Setting::normalizeVehicleBrandLogosText($data['brand_logos'][$type] ?? '')]
            );
        }

        return back()->with('status', 'Configuração dos classificados atualizada.');
    }

    public function uploadBrandLogo(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'vehicle_type' => ['required', 'in:car,motorcycle,nautical'],
            'brand_name' => ['required', 'string', 'max:80'],
            'brand_logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $vehicleType = Setting::normalizeVehicleType($data['vehicle_type']);
        $directory = public_path('images/vehicle-brands/'.$vehicleType);
        File::ensureDirectoryExists($directory);

        $slug = Str::slug($data['brand_name']) ?: Str::uuid()->toString();
        $filename = $slug.'.'.$request->file('brand_logo')->extension();
        $request->file('brand_logo')->move($directory, $filename);

        Setting::setVehicleBrandLogo(
            $data['brand_name'],
            '/images/vehicle-brands/'.$vehicleType.'/'.$filename,
            $vehicleType
        );

        return back()->with('status', 'Logo da marca enviada e vinculada.');
    }

    public function updateListing(Request $request, VehicleListing $vehicle): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:published,paused,sold'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $vehicle->update([
            'status' => $data['status'],
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $data['status'] === VehicleListing::STATUS_PUBLISHED
                ? ($vehicle->published_at ?: now())
                : $vehicle->published_at,
        ]);

        return back()->with('status', 'Anúncio atualizado.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
