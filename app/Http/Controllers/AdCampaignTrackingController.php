<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdCampaignTrackingController extends Controller
{
    public function impression(AdCampaign $campaign): Response
    {
        if ($this->canTrack($campaign, 'impression')) {
            $campaign->increment('impressions_count');
        }

        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function click(AdCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->target_url, 404);
        if ($this->canTrack($campaign, 'click')) {
            $campaign->increment('clicks_count');
        }

        return redirect()->away($campaign->target_url);
    }

    private function canTrack(AdCampaign $campaign, string $event): bool
    {
        if (! AdCampaign::approvedActive()->whereKey($campaign)->exists()) {
            return false;
        }

        if ($event === 'click') {
            return $campaign->click_limit === null || $campaign->clicks_count < $campaign->click_limit;
        }

        return $campaign->impression_limit === null
            || $campaign->impressions_count < $campaign->impression_limit;
    }
}
