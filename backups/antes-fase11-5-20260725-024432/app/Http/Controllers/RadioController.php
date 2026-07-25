<?php

namespace App\Http\Controllers;

use App\Models\RadioRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadioController extends Controller
{
    public function index(Request $request): View
    {
        $chatCategories = RadioRequest::categoryOptions();
        $chatNickname = trim((string) $request->query('apelido', ''));
        $hasEnteredChatRoom = filled($chatNickname) && array_key_exists($request->query('sala'), $chatCategories);
        $selectedChatRoom = $hasEnteredChatRoom
            ? $request->query('sala')
            : null;
        $roomCounts = RadioRequest::query()
            ->selectRaw('category, count(distinct name) as total')
            ->where('is_private', false)
            ->whereNotNull('name')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn ($total) => (int) $total + 1);
        $roomParticipants = $selectedChatRoom
            ? RadioRequest::query()
                ->where('category', $selectedChatRoom)
                ->whereNotNull('name')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->latest()
                ->pluck('name')
                ->push($chatNickname)
                ->filter()
                ->map(fn (string $name) => trim($name))
                ->unique()
                ->reject(fn (string $name) => strcasecmp($name, $chatNickname) === 0)
                ->values()
            : collect();

        return view('radio.index', [
            'radioSettings' => Setting::radioSettings(),
            'audioCampaign' => app(\App\Services\AudioAdvertisingService::class)->current(),
            'station' => $station = \App\Models\RadioStation::query()->where('is_active', true)->first(),
            'onAir' => $station ? app(\App\Services\RadioOnAirService::class)->current($station) : null,
            'todaySchedule' => $station ? \App\Models\RadioScheduleSlot::with(['program.host'])->whereHas('program', fn ($q) => $q->where('radio_station_id', $station->id))->where('day_of_week', now()->dayOfWeek)->where('is_active', true)->orderBy('starts_at')->get() : collect(),
            'chatMessages' => RadioRequest::query()
                ->when($selectedChatRoom, fn ($query) => $query->where('category', $selectedChatRoom))
                ->where('is_private', false)
                ->latest()
                ->take(30)
                ->get()
                ->reverse()
                ->values(),
            'chatCategories' => $chatCategories,
            'selectedChatRoom' => $selectedChatRoom,
            'hasEnteredChatRoom' => $hasEnteredChatRoom,
            'chatNickname' => $chatNickname,
            'roomCounts' => collect($chatCategories)
                ->mapWithKeys(fn ($label, $key) => [$key => $roomCounts->get($key, 1)])
                ->all(),
            'roomParticipants' => $roomParticipants,
            'showAds' => ! auth()->user()?->hasAdFreeAccess(),
        ]);
    }
}
