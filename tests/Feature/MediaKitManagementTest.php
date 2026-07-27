<?php

namespace Tests\Feature;

use App\Models\AdvertiserProfile;
use App\Models\CommercialProposal;
use App\Models\MediaKitFormat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MediaKitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_and_approves_proposal_and_downloads_pdf(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $advertiserUser = User::factory()->create();
        $advertiser = AdvertiserProfile::create([
            'user_id' => $advertiserUser->id, 'company_name' => 'Cliente',
            'legal_name' => 'Cliente Ltda', 'email' => 'cliente@example.com',
            'commercial_status' => 'active', 'is_active' => true,
        ]);
        $format = MediaKitFormat::create([
            'name' => 'Super Banner', 'placement' => 'home_top',
            'price' => 500, 'billing_model' => 'fixed', 'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.media-kit.proposals.store'), [
            'advertiser_profile_id' => $advertiser->id, 'title' => 'Plano mensal',
            'discount' => 50, 'format_ids' => [$format->id], 'quantities' => [$format->id => 2],
        ])->assertRedirect();

        $proposal = CommercialProposal::firstOrFail();
        $this->assertSame('950.00', $proposal->total);
        $this->actingAs($admin)->post(route('admin.media-kit.proposals.approve', $proposal))->assertRedirect();
        $this->assertNotNull($proposal->fresh()->approved_at);
        $this->actingAs($admin)->get(route('admin.media-kit.proposals.pdf', $proposal))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_public_media_kit_pdf_is_valid(): void
    {
        MediaKitFormat::create([
            'name' => 'Banner', 'placement' => 'sidebar', 'price' => 200,
            'billing_model' => 'fixed', 'is_active' => true,
        ]);

        $response = $this->get(route('media-kit.pdf'))->assertOk();
        $this->assertStringStartsWith('%PDF-1.4', $response->getContent());
    }
}
