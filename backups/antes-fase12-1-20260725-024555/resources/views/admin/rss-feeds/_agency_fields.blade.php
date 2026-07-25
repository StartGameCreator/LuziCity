<label>
    Frequência
    <select name="frequency_minutes">
        @foreach([15 => '15 minutos', 30 => '30 minutos', 60 => '1 hora', 180 => '3 horas', 360 => '6 horas', 720 => '12 horas', 1440 => '1 dia'] as $minutes => $label)
            <option value="{{ $minutes }}" @selected((int) old('frequency_minutes', $feed->frequency_minutes ?? 60) === $minutes)>{{ $label }}</option>
        @endforeach
    </select>
</label>
<label>
    Deduplicação
    <select name="deduplication_strategy">
        @foreach(['url' => 'URL', 'guid' => 'GUID', 'url_guid' => 'URL + GUID'] as $strategy => $label)
            <option value="{{ $strategy }}" @selected(old('deduplication_strategy', $feed->deduplication_strategy ?? 'url') === $strategy)>{{ $label }}</option>
        @endforeach
    </select>
</label>
@if(isset($feed) && $feed->exists)
    <div>
        <strong>Coleta:</strong> {{ $feed->last_collected_at?->format('d/m/Y H:i') ?? 'Nunca' }}<br>
        <strong>Próxima:</strong> {{ $feed->next_collection_at?->format('d/m/Y H:i') ?? 'Pendente' }}<br>
        <strong>Falhas consecutivas:</strong> {{ $feed->consecutive_failures }} ·
        <strong>Duplicados:</strong> {{ $feed->duplicates_found }}
        @if($feed->last_failure_message)<p class="notice notice-error">{{ $feed->last_failure_message }}</p>@endif
    </div>
@endif
