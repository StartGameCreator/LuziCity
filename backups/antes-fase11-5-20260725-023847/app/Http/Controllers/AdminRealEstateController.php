<?php

namespace App\Http\Controllers;

use App\Models\RealEstateListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRealEstateController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.real-estate.index', [
            'properties' => RealEstateListing::query()->with('user')->latest()->take(50)->get(),
            'purposeLabels' => RealEstateListing::purposeLabels(),
            'propertyTypeLabels' => RealEstateListing::propertyTypeLabels(),
        ]);
    }

    public function update(Request $request, RealEstateListing $property): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'status' => ['required', 'in:published,paused,deal_done'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $property->update([
            'status' => $data['status'],
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $data['status'] === RealEstateListing::STATUS_PUBLISHED ? ($property->published_at ?: now()) : $property->published_at,
        ]);

        return back()->with('status', 'Anúncio de imóvel atualizado.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }
}
