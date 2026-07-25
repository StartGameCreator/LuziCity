@extends('layouts.app')

@section('title', 'IA Editorial - Administração')

@section('content')
    <section class="content-band">
        <p class="eyebrow">Fase 10.0</p>
        <h1>Fundação da IA Editorial</h1>
        <p>Centralize provedores, modelos, limites, prompts, auditoria e histórico de execução antes de automatizar a produção jornalística.</p>

        <div class="admin-actions" style="margin-top:1rem">
            <a class="secondary-action" href="{{ route('admin.ai-settings.edit') }}">Chaves e conexões</a>
            <a class="secondary-action" href="{{ route('admin.news.index') }}">Notícias</a>
            <a class="secondary-action" href="{{ route('admin.rss-imports.index') }}">Importação RSS</a>
        </div>
    </section>

    @if(session('status'))
        <section class="content-band">
            <strong>{{ session('status') }}</strong>
        </section>
    @endif

    <section class="content-band">
        <h2>Visão geral</h2>
        <div class="admin-stats">
            <article><strong>{{ $stats['total'] }}</strong><span>Execuções</span></article>
            <article><strong>{{ $stats['completed'] }}</strong><span>Concluídas</span></article>
            <article><strong>{{ $stats['failed'] }}</strong><span>Falhas</span></article>
            <article><strong>{{ $stats['today'] }}</strong><span>Hoje</span></article>
            <article><strong>{{ number_format($stats['average_ms'] / 1000, 2, ',', '.') }}s</strong><span>Tempo médio</span></article>
        </div>
    </section>

    <section class="content-band">
        <h2>Provedores</h2>
        <p>As chaves continuam protegidas na área “Chaves e conexões”. Aqui ficam somente modelo, ativação, orçamento e limites.</p>

        <div class="admin-card-grid">
            @foreach($providers as $provider)
                <form class="admin-card" method="post" action="{{ route('admin.ai-editorial.providers.update', $provider) }}">
                    @csrf
                    @method('put')

                    <h3>{{ $provider->name }}</h3>
                    <p>{{ $provider->executions_count }} execuções · {{ $provider->successful_executions_count }} concluídas · {{ $provider->failed_executions_count }} falhas</p>

                    <label>
                        <input type="checkbox" name="is_enabled" value="1" @checked($provider->is_enabled)>
                        Provedor habilitado
                    </label>

                    <label>Modelo
                        <input type="text" name="model" value="{{ old('model', $provider->model) }}" maxlength="160">
                    </label>

                    <label>Endpoint opcional
                        <input type="url" name="endpoint" value="{{ old('endpoint', $provider->endpoint) }}" maxlength="2048">
                    </label>

                    <label>Orçamento mensal (R$)
                        <input type="number" name="monthly_budget_reais" min="0" step="0.01" value="{{ number_format($provider->monthly_budget_cents / 100, 2, '.', '') }}">
                    </label>

                    <label>Limite diário de requisições
                        <input type="number" name="daily_request_limit" min="1" value="{{ $provider->daily_request_limit }}" required>
                    </label>

                    <button class="primary-action" type="submit">Salvar provedor</button>
                </form>
            @endforeach
        </div>
    </section>

    <section class="content-band">
        <h2>Prompts editoriais</h2>
        <p>Os prompts são versionados automaticamente cada vez que são salvos.</p>

        <div class="admin-stack">
            @foreach($templates as $template)
                <details class="admin-card">
                    <summary>
                        <strong>{{ $template->name }}</strong>
                        <span>{{ $template->purpose }} · versão {{ $template->version }} · {{ $template->is_active ? 'ativo' : 'inativo' }}</span>
                    </summary>

                    <form method="post" action="{{ route('admin.ai-editorial.templates.update', $template) }}">
                        @csrf
                        @method('put')

                        <label>
                            <input type="checkbox" name="is_active" value="1" @checked($template->is_active)>
                            Prompt ativo
                        </label>

                        <label>Instrução do sistema
                            <textarea name="system_prompt" rows="5" required>{{ $template->system_prompt }}</textarea>
                        </label>

                        <label>Modelo do pedido
                            <textarea name="user_template" rows="8" required>{{ $template->user_template }}</textarea>
                        </label>

                        <button class="primary-action" type="submit">Salvar nova versão</button>
                    </form>
                </details>
            @endforeach
        </div>
    </section>

    <section class="content-band">
        <h2>Histórico de execuções</h2>

        @if($executions->isEmpty())
            <p>Nenhuma execução de IA foi registrada ainda.</p>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Recurso</th>
                            <th>Provedor</th>
                            <th>Prompt</th>
                            <th>Status</th>
                            <th>Duração</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($executions as $execution)
                            <tr>
                                <td>{{ $execution->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $execution->feature }}</td>
                                <td>{{ $execution->provider?->name ?? 'não definido' }}</td>
                                <td>{{ $execution->promptTemplate?->name ?? 'sem template' }}</td>
                                <td>{{ $execution->status }}</td>
                                <td>{{ number_format($execution->duration_ms / 1000, 2, ',', '.') }}s</td>
                                <td>{{ $execution->user?->name ?? 'sistema' }}</td>
                            </tr>
                            @if($execution->error_message)
                                <tr>
                                    <td colspan="7"><small>Erro: {{ $execution->error_message }}</small></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <style>
        .admin-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.8rem}
        .admin-stats article,.admin-card{border:1px solid var(--border);border-radius:18px;padding:1rem;background:var(--surface)}
        .admin-stats strong{display:block;font-size:1.7rem}.admin-stats span{opacity:.75}
        .admin-card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem}
        .admin-card,.admin-card form,.admin-stack{display:grid;gap:.85rem}
        .admin-card label{display:grid;gap:.35rem}
        .admin-card input[type="text"],.admin-card input[type="url"],.admin-card input[type="number"],.admin-card textarea{width:100%}
        .admin-card summary{cursor:pointer;display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap}
        .table-responsive{overflow:auto}.admin-table{width:100%;border-collapse:collapse}.admin-table th,.admin-table td{padding:.7rem;border-bottom:1px solid var(--border);text-align:left}
    </style>
@endsection
