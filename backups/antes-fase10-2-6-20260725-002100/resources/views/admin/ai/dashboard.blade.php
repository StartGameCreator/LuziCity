@extends('layouts.app')

@section('title', 'Central Editorial IA')

@section('content')
<section class="admin-page ai-dashboard">
    <div class="section-heading">
        <div>
            <span class="eyebrow">Inteligência artificial com revisão humana</span>
            <h1>Central Editorial IA</h1>
            <p>{{ $restricted ? 'Suas execuções e indicadores editoriais.' : 'Visão geral das execuções, custos e saúde do motor editorial.' }}</p>
        </div>
        <a class="primary-action" href="{{ route('admin.news.ai.create') }}">Gerar notícia</a>
    </div>

    <form method="get" class="admin-card ai-filters">
        <label>Período
            <select name="period">
                @foreach(['today' => 'Hoje', 'week' => 'Esta semana', 'month' => 'Este mês', 'custom' => 'Personalizado'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['period'] ?? 'month') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>De <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
        <label>Até <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
        <label>Provedor
            <select name="provider_id"><option value="">Todos</option>
                @foreach($providers as $provider)<option value="{{ $provider->id }}" @selected(($filters['provider_id'] ?? null) == $provider->id)>{{ $provider->name }}</option>@endforeach
            </select>
        </label>
        @unless($restricted)
            <label>Usuário
                <select name="user_id"><option value="">Todos</option>
                    @foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['user_id'] ?? null) == $user->id)>{{ $user->name }}</option>@endforeach
                </select>
            </label>
        @endunless
        <button class="secondary-action" type="submit">Aplicar filtros</button>
    </form>

    <div class="ai-kpis">
        @foreach([
            ['Hoje', $metrics['today']], ['Semana', $metrics['week']], ['Mês', $metrics['month']],
            ['No período', $metrics['summary']['total']], ['Sucesso', $metrics['summary']['completed']],
            ['Erros', $metrics['summary']['failed']], ['Taxa de sucesso', $metrics['summary']['success_rate'].'%'],
            ['Tempo médio', number_format($metrics['summary']['average_ms'] / 1000, 2, ',', '.').' s'],
            ['Tokens', number_format($metrics['summary']['total_tokens'], 0, ',', '.')],
            ['Custo estimado', 'R$ '.number_format((float) $metrics['summary']['estimated_cost'], 6, ',', '.')],
            ['Provedor mais usado', $metrics['summary']['provider']], ['Notícias geradas', $metrics['summary']['news_generated']],
        ] as [$label, $value])
            <article class="admin-card ai-kpi"><span>{{ $label }}</span><strong>{{ $value }}</strong></article>
        @endforeach
    </div>

    <nav class="admin-card ai-shortcuts" aria-label="Atalhos da Central Editorial">
        <a href="{{ route('admin.news.ai.create') }}">Gerar notícia</a>
        <a href="{{ route('admin.ai.prompts.index') }}">Prompts</a>
        <a href="{{ route('admin.ai.providers.index') }}">Provedores</a>
        <a href="{{ route('admin.ai.memory.index') }}">Memória editorial</a>
        <a href="{{ route('admin.ai.costs.index') }}">Custos</a>
        <a href="{{ route('admin.ai.logs.index') }}">Logs</a>
        <a href="{{ route('admin.ai-settings.edit') }}">Configurar chaves</a>
    </nav>

    <div class="ai-tables">
        <section class="admin-card">
            <h2>Últimas execuções</h2>
            <div class="table-responsive"><table class="admin-table">
                <thead><tr><th>Data</th><th>Recurso</th><th>Provedor</th><th>Usuário</th><th>Status</th><th>Tempo</th></tr></thead>
                <tbody>
                @forelse($metrics['latest'] as $execution)
                    <tr><td>{{ $execution->created_at?->format('d/m/Y H:i') }}</td><td>{{ $execution->feature }}</td><td>{{ $execution->provider?->name ?? '—' }}</td><td>{{ $execution->user?->name ?? 'Sistema' }}</td><td>{{ $execution->status }}</td><td>{{ number_format(($execution->duration_ms ?? 0) / 1000, 2, ',', '.') }} s</td></tr>
                @empty <tr><td colspan="6">Nenhuma execução no período.</td></tr> @endforelse
                </tbody>
            </table></div>
        </section>
        <section class="admin-card">
            <h2>Últimos erros</h2>
            @forelse($metrics['errors'] as $error)
                <article class="ai-error"><strong>{{ $error->error_type ?: 'Falha na execução' }}</strong><span>{{ $error->created_at?->format('d/m/Y H:i') }} · {{ $error->provider?->name ?? 'Sem provedor' }}</span><p>{{ \Illuminate\Support\Str::limit($error->error_message ?: 'Sem detalhes registrados.', 240) }}</p></article>
            @empty <p>Nenhum erro no período.</p> @endforelse
        </section>
    </div>
</section>

<style>
.ai-dashboard{display:grid;gap:1.2rem}.ai-filters{display:flex;gap:.8rem;align-items:end;flex-wrap:wrap;padding:1rem}.ai-filters label{display:grid;gap:.35rem;min-width:150px}.ai-filters input,.ai-filters select{padding:.65rem;border:1px solid var(--border);border-radius:.55rem;background:var(--surface);color:inherit}.ai-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.8rem}.ai-kpi{padding:1rem;display:grid;gap:.4rem}.ai-kpi span{font-size:.82rem;color:var(--muted)}.ai-kpi strong{font-size:1.35rem}.ai-shortcuts{padding:1rem;display:flex;gap:.7rem;flex-wrap:wrap}.ai-shortcuts a{padding:.55rem .8rem;border-radius:999px;background:var(--surface-soft);font-weight:700}.ai-tables{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,1fr);gap:1rem}.ai-tables>.admin-card{padding:1rem}.table-responsive{overflow:auto}.admin-table{width:100%;border-collapse:collapse}.admin-table th,.admin-table td{padding:.65rem;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}.ai-error{padding:.8rem 0;border-bottom:1px solid var(--border);display:grid;gap:.25rem}.ai-error span{font-size:.8rem;color:var(--muted)}.ai-error p{margin:0}@media(max-width:850px){.ai-tables{grid-template-columns:1fr}}
</style>
@endsection
