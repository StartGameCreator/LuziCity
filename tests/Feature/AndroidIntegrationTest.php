<?php

namespace Tests\Feature;

use Tests\TestCase;

class AndroidIntegrationTest extends TestCase
{
    public function test_android_asset_links_endpoint_uses_configured_package_and_fingerprints(): void
    {
        config([
            'mobile.android.package' => 'com.luzicity.test',
            'mobile.android.sha256_fingerprints' => ['AA:BB:CC', 'DD:EE:FF'],
        ]);
        $response = $this->getJson('/.well-known/assetlinks.json')->assertOk()
            ->assertJsonPath('0.target.namespace', 'android_app')
            ->assertJsonPath('0.target.package_name', 'com.luzicity.test')
            ->assertJsonPath('0.target.sha256_cert_fingerprints.1', 'DD:EE:FF');
        $this->assertStringContainsString('public', $response->headers->get('cache-control'));
        $this->assertStringContainsString('max-age=3600', $response->headers->get('cache-control'));
    }

    public function test_android_project_contains_push_deep_link_and_offline_contracts(): void
    {
        $root = base_path('android/app/src/main');
        $manifest = file_get_contents($root.'/AndroidManifest.xml');
        $main = file_get_contents($root.'/java/com/luzicity/app/MainActivity.kt');
        $push = file_get_contents($root.'/java/com/luzicity/app/LuziCityMessagingService.kt');

        $this->assertStringContainsString('android:autoVerify="true"', $manifest);
        $this->assertStringContainsString('android:scheme="luzicity"', $manifest);
        $this->assertStringContainsString('com.google.firebase.MESSAGING_EVENT', $manifest);
        $this->assertStringContainsString('LOAD_CACHE_ELSE_NETWORK', $main);
        $this->assertStringContainsString('offline.html', $main);
        $this->assertStringContainsString('/mobile/notifications/devices', $push);
        $this->assertFileExists($root.'/assets/offline.html');
    }
}
