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
        $this->assertStringContainsString('luzicity-pwa-v10', $contents);
        $this->assertStringContainsString("'/offline'", $contents);
    }

    public function test_push_subscription_requires_a_token(): void
    {
        $this->postJson('/push/subscriptions', [])->assertUnprocessable();
    }
}
