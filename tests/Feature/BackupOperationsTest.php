<?php

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupOperationsTest extends TestCase
{
    public function test_backup_contains_database_and_can_be_restored_safely(): void
    {
        config([
            'backup.disk' => 'backup-test',
            'backup.path' => 'backups',
            'backup.include_storage' => false,
            'filesystems.disks.backup-test' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/disks/backup-test'),
                'throw' => true,
            ],
        ]);
        Storage::fake('backup-test');

        $result = app(BackupService::class)->create();
        Storage::disk('backup-test')->assertExists($result['path']);

        $verification = app(BackupService::class)->verify($result['path']);
        $this->assertTrue($verification['valid']);
        $this->assertGreaterThanOrEqual(1, $verification['files']);
    }

    public function test_backup_command_can_create_and_verify_archive(): void
    {
        config([
            'backup.disk' => 'backup-command-test',
            'backup.include_storage' => false,
            'filesystems.disks.backup-command-test' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/disks/backup-command-test'),
                'throw' => true,
            ],
        ]);
        Storage::fake('backup-command-test');

        $this->artisan('luzicity:backup --verify')
            ->expectsOutputToContain('Backup criado')
            ->expectsOutputToContain('Restauracao verificada com sucesso')
            ->assertSuccessful();
    }

    public function test_retention_removes_only_expired_archives(): void
    {
        config([
            'backup.disk' => 'backup-retention-test',
            'backup.path' => 'backups',
            'filesystems.disks.backup-retention-test' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/disks/backup-retention-test'),
                'throw' => true,
            ],
        ]);
        Storage::fake('backup-retention-test');
        $disk = Storage::disk('backup-retention-test');
        $disk->put('backups/expired.zip', 'old');
        $disk->put('backups/current.zip', 'new');
        touch($disk->path('backups/expired.zip'), now()->subDays(31)->timestamp);

        $this->assertSame(1, app(BackupService::class)->prune(30));
        $disk->assertMissing('backups/expired.zip');
        $disk->assertExists('backups/current.zip');
    }
}
