<?php

namespace App\Http\Controllers;

use App\Models\MediaBanner;
use App\Models\Setting;
use App\Models\VehicleListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VehicleClassifiedController extends Controller
{
    public function index(Request $request): View
    {
        $selectedVehicleType = Setting::normalizeVehicleType($request->query('vehicle_type'));

        $query = VehicleListing::query()
            ->with('user')
            ->published()
            ->where('vehicle_type', $selectedVehicleType)
            ->latest('published_at')
            ->latest();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        foreach (['brand', 'city', 'state'] as $field) {
            if ($value = trim((string) $request->query($field))) {
                $query->where($field, $value);
            }
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        $listings = $query->paginate(12)->withQueryString();
        $hasSearchIntent = collect(['q', 'brand', 'city', 'state', 'max_price', 'vehicle_type'])
            ->contains(fn (string $field) => filled($request->query($field)));

        if ($hasSearchIntent && $listings->isNotEmpty()) {
            VehicleListing::query()
                ->whereIn('id', $listings->pluck('id'))
                ->increment('search_hits');
        }

        $featuredListings = VehicleListing::query()
            ->published()
            ->where('vehicle_type', $selectedVehicleType)
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(4)
            ->get();

        $popularListings = VehicleListing::query()
            ->published()
            ->where('vehicle_type', $selectedVehicleType)
            ->orderByRaw('(views_count + search_hits) desc')
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('vehicles.index', [
            'listings' => $listings,
            'featuredListings' => $featuredListings,
            'popularListings' => $popularListings,
            'vehicleVideoBanners' => $this->vehicleVideoBanners(),
            'vehicleTypes' => Setting::vehicleTypeOptions(),
            'selectedVehicleType' => $selectedVehicleType,
            'visualBlock' => Setting::visualBlock('vehicles'),
            'showAds' => ! auth()->user()?->hasAdFreeAccess(),
        ]);
    }

    public function show(VehicleListing $vehicle): View
    {
        abort_unless($vehicle->status === VehicleListing::STATUS_PUBLISHED || auth()->id() === $vehicle->user_id, 404);

        if ($vehicle->status === VehicleListing::STATUS_PUBLISHED) {
            $vehicle->increment('views_count');
        }

        $shareImage = $vehicle->primaryPhotoUrl() ?: data_get(Setting::siteIdentity(), 'share_image');

        return view('vehicles.show', [
            'vehicle' => $vehicle->load('user'),
            'showAds' => ! auth()->user()?->hasAdFreeAccess(),
            'meta' => [
                'title' => $vehicle->title,
                'description' => $vehicle->description ?: 'Anuncio de veiculo publicado na Luzicity.',
                'image' => $shareImage,
                'type' => 'article',
                'url' => route('vehicles.show', $vehicle),
            ],
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create', [
            'settings' => Setting::vehicleClassifiedSettings(),
            'vehicleTypes' => Setting::vehicleTypeOptions(),
            'activeListingsCount' => auth()->user()
                ->vehicleListings()
                ->whereIn('status', [VehicleListing::STATUS_PUBLISHED, VehicleListing::STATUS_PAUSED])
                ->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $settings = Setting::vehicleClassifiedSettings();
        $activeListings = $request->user()
            ->vehicleListings()
            ->whereIn('status', [VehicleListing::STATUS_PUBLISHED, VehicleListing::STATUS_PAUSED])
            ->count();

        if ($settings['limit_enabled'] && $activeListings >= $settings['max_active_listings']) {
            return back()
                ->withInput()
                ->withErrors(['limit' => 'Seu limite de anúncios de veículos foi atingido.']);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'vehicle_type' => ['required', 'in:car,motorcycle,nautical'],
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'year' => ['required', 'integer', 'min:1950', 'max:'.((int) date('Y') + 1)],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'mileage' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'fuel' => ['nullable', 'string', 'max:40'],
            'transmission' => ['nullable', 'string', 'max:40'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:5000'],
            'video_platform' => ['nullable', 'in:youtube,facebook'],
            'video_orientation' => ['nullable', 'in:landscape,portrait'],
            'video_embed_code' => ['nullable', 'string', 'max:12000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            $directory = public_path('images/vehicles');
            File::ensureDirectoryExists($directory);

            foreach ($request->file('photos') as $photo) {
                $filename = Str::uuid().'.'.$photo->extension();
                $photo->move($directory, $filename);
                $photos[] = 'images/vehicles/'.$filename;
            }
        }

        $listing = $request->user()->vehicleListings()->create([
            ...$data,
            'vehicle_type' => Setting::normalizeVehicleType($data['vehicle_type']),
            'state' => Str::upper($data['state']),
            'video_platform' => $data['video_platform'] ?? null,
            'video_orientation' => $data['video_orientation'] ?? 'landscape',
            'video_embed_code' => $data['video_embed_code'] ?? null,
            'photos' => $photos,
            'status' => VehicleListing::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        Setting::appendVehicleBrandLogoIfMissing($listing->brand, $listing->vehicle_type);

        return redirect()->route('vehicles.show', $listing)->with('status', 'Anúncio de veículo publicado.');
    }

    private function vehicleVideoBanners()
    {
        $banners = MediaBanner::query()
            ->where('type', MediaBanner::TYPE_VEHICLE_YOUTUBE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        if ($banners->isNotEmpty()) {
            return $banners;
        }

        return collect([
            ['title' => null, 'embed_code' => null],
        ]);
    }
}
