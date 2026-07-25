<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRealEstateListingRequest;
use App\Models\RealEstateListing;
use App\Models\Setting;
use App\Services\Media\PublicImageUploadService;
use App\Services\Security\EmbedCodeSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RealEstateController extends Controller
{
    public function index(Request $request): View
    {
        $query = RealEstateListing::query()->with('user')->published()->latest('published_at')->latest();

        foreach (['purpose', 'property_type', 'city', 'state'] as $field) {
            if ($value = trim((string) $request->query($field))) {
                $query->where($field, $value);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('neighborhood', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_bedrooms')) {
            $query->where('bedrooms', '>=', (int) $request->query('min_bedrooms'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->query('max_price'));
        }

        return view('real-estate.index', [
            'listings' => $query->paginate(12)->withQueryString(),
            'featuredListings' => RealEstateListing::query()->published()->where('is_featured', true)->latest('published_at')->take(4)->get(),
            'purposeLabels' => RealEstateListing::purposeLabels(),
            'propertyTypeLabels' => RealEstateListing::propertyTypeLabels(),
            'visualBlock' => Setting::visualBlock('real_estate'),
            'showAds' => ! auth()->user()?->hasAdFreeAccess(),
        ]);
    }

    public function show(RealEstateListing $property): View
    {
        abort_unless($property->status === RealEstateListing::STATUS_PUBLISHED || auth()->id() === $property->user_id, 404);

        if ($property->status === RealEstateListing::STATUS_PUBLISHED) {
            $property->increment('views_count');
        }

        $shareImage = $property->primaryPhotoUrl() ?: data_get(Setting::siteIdentity(), 'share_image');

        return view('real-estate.show', [
            'property' => $property->load('user'),
            'purposeLabels' => RealEstateListing::purposeLabels(),
            'propertyTypeLabels' => RealEstateListing::propertyTypeLabels(),
            'showAds' => ! auth()->user()?->hasAdFreeAccess(),
            'meta' => [
                'title' => $property->title,
                'description' => $property->description ?: 'Anuncio de imovel publicado na Luzicity.',
                'image' => $shareImage,
                'type' => 'article',
                'url' => route('real-estate.show', $property),
            ],
        ]);
    }

    public function create(): View
    {
        return view('real-estate.create', [
            'purposeLabels' => RealEstateListing::purposeLabels(),
            'propertyTypeLabels' => RealEstateListing::propertyTypeLabels(),
        ]);
    }

    public function store(StoreRealEstateListingRequest $request, PublicImageUploadService $imageUploads): RedirectResponse
    {
        $data = $request->validated();
        $photos = $imageUploads->storeMany(
            $request->file('photos', []),
            'images/real-estate',
        );

        $property = $request->user()->realEstateListings()->create([
            ...$data,
            'state' => Str::upper($data['state']),
            'video_platform' => $data['video_platform'] ?? null,
            'video_orientation' => $data['video_orientation'] ?? 'landscape',
            'video_embed_code' => EmbedCodeSanitizer::sanitize($data['video_embed_code'] ?? null) ?: null,
            'photos' => $photos,
            'status' => RealEstateListing::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return redirect()->route('real-estate.show', $property)->with('status', 'Anúncio de imóvel publicado.');
    }
}
