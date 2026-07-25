@if($station)
<section class="settings-panel" aria-label="Programação da rádio"><p class="eyebrow">{{ ($station->force_on_air || $onAir) ? 'No ar agora' : 'Programação' }}</p><h2>{{ $onAir?->program?->name ?? ($station->on_air_label ?: $station->name) }}</h2>@if($onAir?->program?->host)<p>Com {{ $onAir->program->host->name }}</p>@endif
@if($station->stream_url)<audio controls preload="none" src="{{ $station->stream_url }}"></audio>@endif
<h3>Grade de hoje</h3>@forelse($todaySchedule as $slot)<p><strong>{{ substr($slot->starts_at,0,5) }}</strong> — {{ $slot->program->name }}{{ $slot->program->host?' com '.$slot->program->host->name:'' }}</p>@empty<p>Nenhum programa cadastrado para hoje.</p>@endforelse</section>
@endif
