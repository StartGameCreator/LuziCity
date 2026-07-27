<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultisiteStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_resolves_site_identity_city_theme_and_settings(): void
    {
        $site = Site::create([
            'name' => 'Portal Cristalina', 'slug' => 'portal-cristalina', 'city' => 'Cristalina', 'state' => 'GO',
            'logo_path' => 'images/cristalina.png', 'favicon_path' => 'images/cristalina-icon.png',
            'theme_primary' => '#123456', 'theme_secondary' => '#654321',
            'is_active' => true, 'is_default' => false,
        ]);
        SiteDomain::create(['site_id' => $site->id, 'domain' => 'cristalina.test', 'is_primary' => true]);
        $site->settings()->create(['key' => 'share_image', 'value' => 'images/cristalina-share.png']);

        $this->get('http://cristalina.test/')->assertOk()
            ->assertSee('Portal Cristalina')->assertSee('images/cristalina.png')
            ->assertSee('--accent: #123456')->assertSee('--accent-strong: #654321');
        $this->assertSame('Cristalina', Site::current()?->city);
        $this->assertSame('images/cristalina-share.png', Site::current()?->setting('share_image'));
    }

    public function test_unknown_host_uses_active_default_site(): void
    {
        $this->get('http://desconhecido.test/')->assertOk()->assertSee('Luzicity');
        $this->assertTrue(Site::current()?->is_default);
    }

    public function test_admin_can_create_and_update_site_domains_and_configuration(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');

        $this->actingAs($admin)->post(route('admin.sites.store'), [
            'name' => 'Portal Entorno', 'slug' => 'portal-entorno', 'city' => 'Valparaíso', 'state' => 'go',
            'domains' => "entorno.test\nwww.entorno.test", 'theme_primary' => '#112233',
            'theme_secondary' => '#334455', 'settings' => "share_image=images/share.png\nlocale=pt-BR",
            'is_active' => '1',
        ])->assertRedirect();
        $site = Site::where('slug', 'portal-entorno')->firstOrFail();
        $this->assertSame('GO', $site->state);
        $this->assertSame(['entorno.test', 'www.entorno.test'], $site->domains()->orderBy('id')->pluck('domain')->all());
        $this->assertSame('pt-BR', $site->setting('locale'));

        $this->actingAs($admin)->put(route('admin.sites.update', $site), [
            'name' => 'Portal Entorno Atualizado', 'slug' => 'portal-entorno',
            'domains' => 'novo.entorno.test', 'theme_primary' => '#abcdef',
            'theme_secondary' => '#123456', 'settings' => 'locale=pt-BR',
            'is_active' => '1', 'is_default' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'name' => 'Portal Entorno Atualizado', 'is_default' => true]);
        $this->assertDatabaseHas('site_domains', ['site_id' => $site->id, 'domain' => 'novo.entorno.test']);
        $this->assertSame(1, Site::where('is_default', true)->count());
    }
}
