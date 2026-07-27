<?php

namespace Tests\Feature;

use App\Models\PodcastEpisode;
use App\Models\PodcastSeries;
use App\Models\RadioHost;
use App\Models\RadioProgram;
use App\Models\RadioScheduleSlot;
use App\Models\RadioStation;
use App\Services\RadioDashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadioConsolidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_radio_dashboard_service_consolidates_modules_and_public_pages_work(): void
    {
        $this->travelTo(Carbon::parse('2026-07-26 12:00:00'));
        $station = RadioStation::create([
            'name' => 'Rádio Luzicity', 'stream_url' => 'https://example.com/live.mp3', 'is_active' => true,
        ]);
        $host = RadioHost::create(['name' => 'Locutora', 'is_active' => true]);
        $program = RadioProgram::create([
            'radio_station_id' => $station->id, 'radio_host_id' => $host->id,
            'name' => 'Ao Vivo', 'is_active' => true,
        ]);
        RadioScheduleSlot::create([
            'radio_program_id' => $program->id, 'day_of_week' => now()->dayOfWeek,
            'starts_at' => now()->subHour()->format('H:i'), 'ends_at' => now()->addHour()->format('H:i'),
            'is_live' => true, 'is_active' => true,
        ]);
        $series = PodcastSeries::create([
            'title' => 'Podcast Local', 'slug' => 'podcast-local', 'is_published' => true,
        ]);
        PodcastEpisode::create([
            'podcast_series_id' => $series->id, 'title' => 'Episódio', 'slug' => 'episodio',
            'audio_path' => 'https://example.com/a.mp3', 'is_published' => true, 'published_at' => now(),
        ]);

        $data = app(RadioDashboardService::class)->data();
        $this->assertSame('Ao Vivo', $data['onAir']->program->name);
        $this->assertSame(1, $data['stats']['published_episodes']);
        $this->get('/radio')->assertOk()->assertSee('Ao Vivo');
        $this->get('/podcasts')->assertOk()->assertSee('Podcast Local');
    }
}
