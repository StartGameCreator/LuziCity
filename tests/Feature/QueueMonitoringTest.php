<?php

namespace Tests\Feature;

use App\Jobs\GenerateNewsNarrationJob;
use App\Jobs\ImportRssFeedsJob;
use App\Jobs\RenderVideoClipJob;
use App\Models\User;
use App\Services\RssImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueueMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_queue_driver_pending_dead_letter_and_activity(): void
    {
        $admin = $this->admin();
        DB::table('jobs')->insert([
            'queue' => 'rss', 'payload' => '{}', 'attempts' => 0,
            'reserved_at' => null, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp,
        ]);
        DB::table('failed_jobs')->insert($this->failedJob());
        DB::table('queue_activity_logs')->insert([
            'job_uuid' => (string) Str::uuid(), 'connection' => 'database', 'queue' => 'rss',
            'job_name' => ImportRssFeedsJob::class, 'status' => 'processed',
            'duration_ms' => 125, 'error' => null, 'occurred_at' => now(),
        ]);

        $this->actingAs($admin)->get('/admin/sistema/filas')->assertOk()
            ->assertSee(config('queue.default'))
            ->assertSee('ImportRssFeedsJob')
            ->assertSee('Dead-letter')
            ->assertSee('125 ms');
    }

    public function test_completed_job_is_recorded_by_queue_telemetry(): void
    {
        $service = $this->mock(RssImportService::class);
        $service->shouldReceive('importAll')->once()->with(5)->andReturn([
            'created' => 0, 'updated' => 0, 'failed' => 0, 'messages' => [],
        ]);

        ImportRssFeedsJob::dispatchSync(5);

        $this->assertDatabaseHas('queue_activity_logs', [
            'queue' => 'sync', 'job_name' => ImportRssFeedsJob::class, 'status' => 'processed',
        ]);
    }

    public function test_admin_can_retry_and_forget_dead_letter_jobs(): void
    {
        $admin = $this->admin();
        $retry = $this->failedJob();
        DB::table('failed_jobs')->insert($retry);

        $this->actingAs($admin)->post(route('admin.queue-monitor.retry', $retry['uuid']))->assertRedirect();
        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $retry['uuid']]);
        $this->assertDatabaseHas('jobs', ['queue' => 'rss']);

        $forget = $this->failedJob();
        DB::table('failed_jobs')->insert($forget);
        $this->actingAs($admin)->delete(route('admin.queue-monitor.forget', $forget['uuid']))->assertRedirect();
        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $forget['uuid']]);
    }

    public function test_jobs_have_bounded_retries_and_progressive_backoff(): void
    {
        $audio = new GenerateNewsNarrationJob(1);
        $video = new RenderVideoClipJob(1);
        $rss = new ImportRssFeedsJob(1);

        $this->assertSame(3, $audio->tries);
        $this->assertSame([30, 120, 600], $audio->backoff());
        $this->assertSame(3, $video->tries);
        $this->assertSame([60, 300, 900], $video->backoff());
        $this->assertSame([10, 30, 60], $rss->backoff());
        $this->assertTrue(config('queue.connections.database.after_commit'));
        $this->assertGreaterThan($video->timeout, config('queue.connections.database.retry_after'));
    }

    public function test_journalist_cannot_operate_queue_monitor(): void
    {
        Role::findOrCreate('Jornalista');
        $journalist = User::factory()->create();
        $journalist->assignRole('Jornalista');

        $this->actingAs($journalist)->get('/admin/sistema/filas')->assertForbidden();
        $this->actingAs($journalist)->post('/admin/sistema/filas/falhas/reprocessar-todas')->assertForbidden();
    }

    private function admin(): User
    {
        Role::findOrCreate('Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    }

    private function failedJob(): array
    {
        $jobId = Queue::connection('database')->push(new ImportRssFeedsJob(1), '', 'rss');
        $payload = (string) DB::table('jobs')->where('id', $jobId)->value('payload');
        DB::table('jobs')->where('id', $jobId)->delete();
        $uuid = (string) data_get(json_decode($payload, true), 'uuid');

        return [
            'uuid' => $uuid, 'connection' => 'database', 'queue' => 'rss',
            'payload' => $payload,
            'exception' => 'RuntimeException: falha simulada',
            'failed_at' => now(),
        ];
    }
}
