<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminQueueMonitorController extends Controller
{
    private const QUEUES = ['default', 'rss', 'webhooks', 'audio', 'video-render'];

    public function index(): View
    {
        $queues = collect(self::QUEUES)->map(fn (string $name) => [
            'name' => $name,
            'pending' => $this->queueSize($name),
            'processed_24h' => $this->activityCount($name, 'processed'),
            'failed_24h' => $this->activityCount($name, 'failed'),
        ]);
        $failed = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->latest('failed_at')->paginate(30)
            : collect();
        $recent = Schema::hasTable('queue_activity_logs')
            ? DB::table('queue_activity_logs')->latest('occurred_at')->limit(50)->get()
            : collect();

        return view('admin.queue-monitor.index', [
            'driver' => config('queue.default'),
            'isAsync' => config('queue.default') !== 'sync',
            'queues' => $queues,
            'failed' => $failed,
            'recent' => $recent,
        ]);
    }

    public function retry(Request $request, string $uuid): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('status', 'Job devolvido à fila para nova tentativa.');
    }

    public function forget(Request $request, string $uuid): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('status', 'Job removido do dead-letter.');
    }

    public function retryAll(): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('status', 'Todos os jobs com falha foram devolvidos às filas.');
    }

    public function prune(Request $request): RedirectResponse
    {
        $data = $request->validate(['hours' => ['required', 'integer', 'min:24', 'max:8760']]);
        Artisan::call('queue:prune-failed', ['--hours' => $data['hours']]);

        return back()->with('status', 'Histórico antigo do dead-letter removido.');
    }

    public static function displayName(object $failedJob): string
    {
        $payload = json_decode((string) $failedJob->payload, true);

        return Str::afterLast((string) data_get($payload, 'displayName', 'Job desconhecido'), '\\');
    }

    private function queueSize(string $queue): ?int
    {
        try {
            return Queue::connection()->size($queue);
        } catch (\Throwable) {
            return null;
        }
    }

    private function activityCount(string $queue, string $status): int
    {
        return Schema::hasTable('queue_activity_logs')
            ? DB::table('queue_activity_logs')->where('queue', $queue)->where('status', $status)
                ->where('occurred_at', '>=', now()->subDay())->count()
            : 0;
    }
}
