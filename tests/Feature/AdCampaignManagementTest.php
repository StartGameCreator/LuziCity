<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use App\Models\AdvertiserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdCampaignManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_approve_campaign(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $advertiserUser = User::factory()->create();
        $advertiser = AdvertiserProfile::create([
            'user_id' => $advertiserUser->id,
            'company_name' => 'Empresa Teste',
            'legal_name' => 'Empresa Teste Ltda',
            'email' => 'comercial@teste.local',
            'commercial_status' => 'active',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.campaigns.store'), [
            'advertiser_profile_id' => $advertiser->id,
            'name' => 'Campanha de lançamento',
            'campaign_type' => 'banner',
            'placement' => 'home_top',
            'status' => 'pending',
            'billing_model' => 'cpc',
            'budget' => 1000,
            'target_url' => 'https://example.com/oferta',
            'target_cities' => 'Luziânia, Cidade Ocidental',
            'target_devices' => ['mobile'],
            'is_active' => '1',
        ]);

        $campaign = AdCampaign::firstOrFail();
        $response->assertRedirect(route('admin.campaigns.edit', $campaign));
        $this->actingAs($admin)->post(route('admin.campaigns.approve', $campaign))->assertRedirect();
        $this->assertNotNull($campaign->fresh()->approved_at);
        $this->assertSame('active', $campaign->fresh()->status);
    }

    public function test_active_approved_campaign_tracks_delivery_with_limits(): void
    {
        $campaign = AdCampaign::create([
            'name' => 'Rastreável',
            'placement' => 'home_top',
            'status' => 'active',
            'target_url' => 'https://example.com',
            'impression_limit' => 1,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        $this->get(route('campaigns.impression', $campaign))->assertOk();
        $this->get(route('campaigns.impression', $campaign))->assertOk();
        $this->assertSame(1, $campaign->fresh()->impressions_count);
        $this->get(route('campaigns.click', $campaign))->assertRedirect('https://example.com');
        $this->assertSame(1, $campaign->fresh()->clicks_count);
    }
}
