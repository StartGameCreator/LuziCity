<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class ReadinessController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::select('select 1')),
            'cache' => $this->check(function (): void {
                Cache::put('health:ready', true, 30);
                throw_unless(Cache::get('health:ready') === true);
            }),
            'queue' => $this->check(fn () => Queue::connection()->size()),
            'storage' => is_writable(storage_path()) ? 'ok' : 'error',
        ];
        $ready = ! in_array('error', $checks, true);

        return response()->json([
            'status' => $ready ? 'ready' : 'unavailable',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $ready ? 200 : 503);
    }

    private function check(callable $callback): string
    {
        try {
            $callback();

            return 'ok';
        } catch (Throwable) {
            return 'error';
        }
    }
}
