<?php

namespace App\Jobs;

use App\Models\VideoClip;
use App\Services\VideoClipRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class RenderVideoClipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 660;
    public bool $failOnTimeout = true;

    public function __construct(public int $clipId)
    {
        $this->onQueue((string) config('media.video_render_queue', 'video-render'));
    }

    public function handle(VideoClipRenderer $renderer): void
    {
        $clip = VideoClip::with('video')->findOrFail($this->clipId);
        $clip->update(['status' => 'rendering', 'error_message' => null]);
        try {
            $renderer->render($clip);
        } catch (Throwable $exception) {
            $clip->update(['status' => 'failed', 'error_message' => Str::limit($exception->getMessage(), 2000, '')]);
            throw $exception;
        }
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }
}
