<?php

namespace Tests\Feature\Pwa;

use Tests\TestCase;

class PwaAdvancedTest extends TestCase
{
    public function test_offline_page_is_public(): void
    {
        $this->get('/offline')->assertOk()->assertSee('Você está offline');
    }

    public function test_service_worker_has_versioned_cache(): void
    {
        $contents = file_get_contents(public_path('service-worker.js'));
        $this->assertStringContainsString('luzicity-pwa-v11', $contents);
        $this->assertStringContainsString("'/offline'", $contents);
        $this->assertStringContainsString('networkFirstPage', $contents);
        $this->assertStringContainsString('staleWhileRevalidate', $contents);
        $this->assertStringContainsString("'SKIP_WAITING'", $contents);
        $this->assertStringContainsString("'CLEAR_CACHES'", $contents);
        $this->assertStringNotContainsString('.then(() => self.skipWaiting())', $contents);
        $this->assertStringContainsString("'/admin'", $contents);
    }

    public function test_manifest_exposes_an_installable_desktop_experience(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('/', $manifest['id']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotEmpty($manifest['icons']);
        $this->assertNotEmpty($manifest['shortcuts']);
    }

    public function test_push_subscription_requires_a_token(): void
    {
        $this->postJson('/push/subscriptions', [])->assertUnprocessable();
    }
}
