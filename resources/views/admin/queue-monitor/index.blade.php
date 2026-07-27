@extends('layouts.app')
@section('title', 'Monitor de filas')
@section('content')
<section class="content-band">
    <p class="eyebrow">Fase 20.2 · Operações</p>
    <h1>Monitor de filas</h1>
    <p>Driver atual: <strong>{{ $driver }}</strong> · {{ $isAsync ? 'processamento assíncrono' : 'atenção: processamento síncrono' }}</p>
</section>

<section class="system-health-stats" aria-label="Estado das filas">
    @foreach($queues as $queue)
        <article class="settings-panel system-health-stat">
            <span>{{ $queue['name'] }}</span>
            <strong>{{ $queue['pending'] ?? '—' }}</strong>
            <small>Pendentes · {{ $queue['processed_24h'] }} concluídos / {{ $queue['failed_24h'] }} falhas em 24h</small>
        </article>
    @endforeach
</section>

<section class="settings-panel">
    <div class="action-row">
        <div><h2>Dead-letter</h2><p>Jobs que esgotaram todas as tentativas.</p></div>
        @if(count($failed))
            <form method="post" action="{{ route('admin.queue-monitor.retry-all') }}" onsubmit="return confirm('Reprocessar todos os jobs com falha?')">
                @csrf<button class="secondary-action">Reprocessar todos</button>
            </form>
            <form method="post" action="{{ route('admin.queue-monitor.prune') }}">
                @csrf<input type="hidden" name="hours" value="168"><button class="secondary-action">Limpar com mais de 7 dias</button>
            </form>
        @endif
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Job</th><th>Fila</th><th>Falhou em</th><th>Erro</th><th>Ações</th></tr></thead>
            <tbody>
            @forelse($failed as $job)
                <tr>
                    <td>{{ \App\Http\Controllers\AdminQueueMonitorController::displayName($job) }}</td>
                    <td>{{ $job->connection }} / {{ $job->queue }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($job->failed_at)->format('d/m/Y H:i:s') }}</td>
                    <td><small>{{ \Illuminate\Support\Str::limit(strtok($job->exception, "\n"), 180) }}</small></td>
                    <td><div class="action-row">
                        <form method="post" action="{{ route('admin.queue-monitor.retry', $job->uuid) }}">@csrf<button class="secondary-action">Reprocessar</button></form>
                        <form method="post" action="{{ route('admin.queue-monitor.forget', $job->uuid) }}" onsubmit="return confirm('Remover definitivamente este registro?')">@csrf @method('DELETE')<button class="secondary-action">Remover</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhum job no dead-letter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($failed, 'links')){{ $failed->links() }}@endif
</section>

<section class="settings-panel">
    <h2>Atividade recente</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Horário</th><th>Job</th><th>Fila</th><th>Status</th><th>Duração</th></tr></thead>
        <tbody>
        @forelse($recent as $event)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($event->occurred_at)->format('d/m/Y H:i:s') }}</td>
                <td>{{ \Illuminate\Support\Str::afterLast($event->job_name, '\\') }}</td>
                <td>{{ $event->connection }} / {{ $event->queue }}</td>
                <td>{{ $event->status }}</td>
                <td>{{ $event->duration_ms === null ? '—' : $event->duration_ms.' ms' }}</td>
            </tr>
        @empty
            <tr><td colspan="5">A atividade aparecerá quando os workers processarem jobs.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
