<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Models\AdvertiserProfile;
use App\Services\SiteStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAdCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdCampaign::query()->with('advertiserProfile');
        $query->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        $query->when($request->filled('placement'), fn ($q) => $q->where('placement', $request->string('placement')));

        return view('admin.campaigns.index', [
            'campaigns' => $query->latest()->paginate(20)->withQueryString(),
            'metrics' => [
                'total' => AdCampaign::count(),
                'active' => AdCampaign::deliverable()->count(),
                'impressions' => AdCampaign::sum('impressions_count'),
                'clicks' => AdCampaign::sum('clicks_count'),
            ],
        ]);
    }

    public function create(): View
    {
        return $this->form(new AdCampaign);
    }

    public function store(Request $request): RedirectResponse
    {
        $campaign = AdCampaign::create($this->validated($request));

        return redirect()->route('admin.campaigns.edit', $campaign)
            ->with('status', 'Campanha criada com sucesso.');
    }

    public function edit(AdCampaign $campaign): View
    {
        return $this->form($campaign);
    }

    public function update(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $campaign->update($this->validated($request, $campaign));

        return back()->with('status', 'Campanha atualizada.');
    }

    public function approve(AdCampaign $campaign): RedirectResponse
    {
        $campaign->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'status' => 'active',
            'is_active' => true,
        ]);

        return back()->with('status', 'Campanha aprovada e ativada.');
    }

    private function form(AdCampaign $campaign): View
    {
        return view('admin.campaigns.form', [
            'campaign' => $campaign,
            'advertisers' => AdvertiserProfile::query()->where('is_active', true)
                ->orderBy('company_name')->get(),
        ]);
    }

    private function validated(Request $request, ?AdCampaign $campaign = null): array
    {
        $data = $request->validate([
            'advertiser_profile_id' => ['required', 'exists:advertiser_profiles,id'],
            'name' => ['required', 'string', 'max:180'],
            'campaign_type' => ['required', 'in:banner,native,sponsored'],
            'placement' => ['required', 'string', 'max:80'],
            'status' => ['required', 'in:draft,pending,active,paused,finished,cancelled'],
            'billing_model' => ['required', 'in:fixed,cpm,cpc'],
            'budget' => ['required', 'numeric', 'min:0'],
            'daily_budget' => ['nullable', 'numeric', 'min:0'],
            'price_per_impression' => ['nullable', 'numeric', 'min:0'],
            'price_per_click' => ['nullable', 'numeric', 'min:0'],
            'impression_limit' => ['nullable', 'integer', 'min:1'],
            'click_limit' => ['nullable', 'integer', 'min:1'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'target_cities' => ['nullable', 'string', 'max:2000'],
            'target_categories' => ['nullable', 'string', 'max:2000'],
            'target_devices' => ['nullable', 'array'],
            'target_devices.*' => ['in:desktop,mobile,tablet'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:0,6'],
            'daily_start_time' => ['nullable', 'date_format:H:i'],
            'daily_end_time' => ['nullable', 'date_format:H:i'],
            'priority' => ['nullable', 'integer', 'between:0,999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['advertiser_id'] = AdvertiserProfile::findOrFail($data['advertiser_profile_id'])->user_id;
        $data['target_cities'] = $this->csv($data['target_cities'] ?? null);
        $data['target_categories'] = $this->csv($data['target_categories'] ?? null);
        $data['is_active'] = $request->boolean('is_active');
        $data['priority'] = $data['priority'] ?? 0;

        if ($request->hasFile('image')) {
            if ($campaign?->image_path) {
                Storage::disk('public')->delete($campaign->image_path);
            }
            $data['image_path'] = $request->file('image')->store(SiteStorage::directory('campaigns'), 'public');
        }
        unset($data['image']);

        return $data;
    }

    private function csv(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
