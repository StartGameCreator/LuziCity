<?php

namespace Tests\Feature;

use Tests\TestCase;

class IosIntegrationTest extends TestCase
{
    public function test_apple_site_association_uses_team_and_bundle_configuration(): void
    {
        config(['mobile.ios.team_id' => 'ABCDE12345', 'mobile.ios.bundle_id' => 'com.luzicity.test']);
        $response = $this->getJson('/.well-known/apple-app-site-association')->assertOk()
            ->assertJsonPath('applinks.details.0.appID', 'ABCDE12345.com.luzicity.test')
            ->assertJsonPath('applinks.details.0.components.0./', '/noticias/*');
        $this->assertStringContainsString('public', $response->headers->get('cache-control'));
        $this->assertStringContainsString('max-age=3600', $response->headers->get('cache-control'));
    }

    public function test_ios_project_contains_push_deep_link_keychain_and_offline_contracts(): void
    {
        $root = base_path('ios/LuziCity');
        $project = file_get_contents(base_path('ios/project.yml'));
        $entitlements = file_get_contents($root.'/LuziCity.entitlements');
        $app = file_get_contents($root.'/LuziCityApp.swift');
        $push = file_get_contents($root.'/AppDelegate.swift');
        $web = file_get_contents($root.'/WebPortalView.swift');
        $keychain = file_get_contents($root.'/KeychainStore.swift');

        $this->assertStringContainsString('FirebaseMessaging', $project);
        $this->assertStringContainsString('applinks:luzicity.com', $entitlements);
        $this->assertStringContainsString('onOpenURL', $app);
        $this->assertStringContainsString('registerForRemoteNotifications', $push);
        $this->assertStringContainsString('mobile/notifications/devices', file_get_contents($root.'/APIClient.swift'));
        $this->assertStringContainsString('returnCacheDataElseLoad', $web);
        $this->assertStringContainsString('forResource: "offline"', $web);
        $this->assertStringContainsString('withExtension: "html"', $web);
        $this->assertStringContainsString('kSecAttrAccessibleAfterFirstUnlockThisDeviceOnly', $keychain);
        $this->assertFileExists($root.'/offline.html');
    }
}
