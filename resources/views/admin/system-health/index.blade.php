@extends('layouts.app', ['title' => 'Saude do Sistema - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administracao</p>
        <h1>Saude do Sistema</h1>
        <p>Veja rapidamente o que esta funcionando, o que esta pendente e onde vale mexer primeiro.</p>
    </section>

    <section class="content-band">
        <h2>Observabilidade — última hora</h2>
        <p>{{ $telemetry['requests'] }} requisições · {{ $telemetry['errors'] }} erros · {{ $telemetry['error_rate'] }}% de erro · média {{ $telemetry['average_ms'] }} ms · {{ $telemetry['slow'] }} lentas</p>
        @forelse($alerts as $alert)
            <p class="system-health-card is-{{ $alert['level'] }}"><strong>Alerta:</strong> {{ $alert['message'] }}</p>
        @empty
            <p>Nenhum alerta operacional ativo.</p>
        @endforelse
        <p><a href="{{ route('health.ready') }}" target="_blank" rel="noopener">Abrir endpoint de prontidão</a></p>
    </section>

    <section class="system-health-summary" aria-label="Resumo da saude do sistema">
        <article class="settings-panel system-health-summary-card is-ok">
            <span>Ok</span>
            <strong>{{ $summary['ok'] }}</strong>
        </article>
        <article class="settings-panel system-health-summary-card is-warning">
            <span>Atencao</span>
            <strong>{{ $summary['warning'] }}</strong>
        </article>
        <article class="settings-panel system-health-summary-card is-error">
            <span>Critico</span>
            <strong>{{ $summary['error'] }}</strong>
        </article>
    </section>

    <section class="system-health-stats" aria-label="Numeros do projeto">
        @foreach($stats as $stat)
            <article class="settings-panel system-health-stat">
                <span>{{ $stat['label'] }}</span>
                <strong>{{ $stat['value'] }}</strong>
            </article>
        @endforeach
    </section>

    <section class="system-health-grid" aria-label="Checagens do sistema">
        @foreach($checks as $check)
            <article class="settings-panel system-health-card is-{{ $check['status'] }}">
                <div class="system-health-card-head">
                    <span class="system-health-badge">
                        @if($check['status'] === 'ok')
                            Ok
                        @elseif($check['status'] === 'warning')
                            Atencao
                        @else
                            Critico
                        @endif
                    </span>
                    <h2>{{ $check['title'] }}</h2>
                </div>
                <p>{{ $check['message'] }}</p>
                <small>{{ $check['detail'] }}</small>
            </article>
        @endforeach
    </section>
@endsection
