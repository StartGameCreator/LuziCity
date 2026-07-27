<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeployReadinessTest extends TestCase
{
    public function test_deploy_preflight_passes_with_safe_configuration(): void
    {
        config([
            'app.env' => 'testing',
            'app.debug' => false,
            'app.url' => 'https://staging.example.com',
        ]);

        $this->artisan('luzicity:deploy-check --environment=testing')
            ->expectsOutputToContain('Preflight concluido')
            ->assertSuccessful();
    }

    public function test_deploy_preflight_rejects_wrong_environment(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://staging.example.com',
        ]);

        $this->artisan('luzicity:deploy-check --environment=production')
            ->assertFailed();
    }

    public function test_deploy_scripts_use_safe_migration_and_code_only_rollback(): void
    {
        $deploy = file_get_contents(base_path('scripts/deploy.sh'));
        $rollback = file_get_contents(base_path('scripts/rollback.sh'));
        $release = file_get_contents(base_path('scripts/release.sh'));

        $this->assertStringContainsString('migrate --force --isolated', $deploy);
        $this->assertStringContainsString('luzicity:backup --verify', $deploy);
        $this->assertStringNotContainsString('migrate:rollback', $rollback);
        $this->assertStringContainsString('ln -sfn', $rollback);
        $this->assertStringContainsString('current.next', $release);
        $this->assertStringContainsString('scripts/deploy.sh', $release);
    }
}
