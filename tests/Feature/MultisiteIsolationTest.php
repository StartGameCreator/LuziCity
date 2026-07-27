<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use App\Models\MediaBanner;
use App\Models\NewsArticle;
use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\User;
use App\Services\SiteStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultisiteIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_media_and_ads_are_scoped_to_current_site(): void
    {
        $author = User::factory()->create();
        [$siteA, $siteB] = $this->sites();
        app()->instance('currentSite', $siteA);
        NewsArticle::create(['author_id' => $author->id, 'title' => 'Notícia A', 'slug' => 'noticia-a', 'body' => 'A']);
        MediaBanner::create(['type' => 'youtube', 'title' => 'Mídia A']);
        AdCampaign::create(['name' => 'Anúncio A', 'placement' => 'home_top']);

        app()->instance('currentSite', $siteB);
        NewsArticle::create(['author_id' => $author->id, 'title' => 'Notícia B', 'slug' => 'noticia-b', 'body' => 'B']);
        MediaBanner::create(['type' => 'youtube', 'title' => 'Mídia B']);
        AdCampaign::create(['name' => 'Anúncio B', 'placement' => 'home_top']);

        $this->assertSame(['Notícia B'], NewsArticle::pluck('title')->all());
        $this->assertSame(['Mídia B'], MediaBanner::pluck('title')->all());
        $this->assertSame(['Anúncio B'], AdCampaign::pluck('name')->all());
        $this->assertSame(2, NewsArticle::forAllSites()->count());
        $this->assertSame('sites/site-b/news-covers', SiteStorage::directory('news-covers'));
    }

    public function test_admin_membership_is_enforced_by_domain_and_permissions_are_site_specific(): void
    {
        [$siteA, $siteB] = $this->sites();
        app()->instance('currentSite', $siteA);
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $admin->sites()->updateExistingPivot($siteA->id, ['permissions' => json_encode(['manage_content'])]);

        $this->assertTrue($admin->canAccessSite($siteA));
        $this->assertTrue($admin->hasSitePermission($siteA, 'manage_content'));
        $this->assertFalse($admin->hasSitePermission($siteA, 'manage_ads'));
        $this->assertFalse($admin->canAccessSite($siteB));

        $this->actingAs($admin)->get('http://site-a.test/admin')->assertOk();
        $this->actingAs($admin)->get('http://site-b.test/admin')->assertForbidden();
    }

    private function sites(): array
    {
        $default = Site::where('is_default', true)->firstOrFail();
        $default->update(['name' => 'Site A', 'slug' => 'site-a']);
        $default->domains()->delete();
        SiteDomain::create(['site_id' => $default->id, 'domain' => 'site-a.test', 'is_primary' => true]);
        $other = Site::create([
            'name' => 'Site B', 'slug' => 'site-b', 'theme_primary' => '#0067c0',
            'theme_secondary' => '#004e8c', 'is_active' => true, 'is_default' => false,
        ]);
        SiteDomain::create(['site_id' => $other->id, 'domain' => 'site-b.test', 'is_primary' => true]);

        return [$default, $other];
    }
}
