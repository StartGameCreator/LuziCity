<?php

namespace App\Jobs;

use App\Models\NewsNarration;
use App\Services\NewsNarrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateNewsNarrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;
    public bool $failOnTimeout = true;

    public function __construct(public int $narrationId)
    {
        $this->onQueue('audio');
    }

    public function handle(NewsNarrationService $service): void
    {
        $narration = NewsNarration::with('voiceProfile')->findOrFail($this->narrationId);
        try {
            $service->generate($narration);
        } catch (Throwable $exception) {
            $service->fail($narration, $exception);
            throw $exception;
        }
    }

    public function backoff(): array
    {
        return [30, 120, 600];
    }
}
