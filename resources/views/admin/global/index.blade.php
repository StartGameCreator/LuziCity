@extends('layouts.app')
@section('title','Administração global')
@section('content')
<section class="content-band"><p class="eyebrow">Fase 17.4 · Multisite</p><h1>Administração global</h1><p>Visão central da rede, custos, saúde e auditoria.</p></section>
<section class="category-admin-list">@foreach([
['Sites',$totals['sites']],['Sites ativos',$totals['active_sites']],['Usuários',$totals['users']],
['Notícias',$totals['news']],['Anúncios',$totals['ads']]
] as [$label,$value])<article class="settings-panel"><small>{{ $label }}</small><h2>{{ $value }}</h2></article>@endforeach</section>

<section class="settings-panel"><h2>Sites da rede</h2>
@foreach($sites as $site)@php($content=$contentBySite->get($site->id))
<p><strong>{{ $site->name }}</strong> · {{ $site->city ?: 'sem cidade' }}/{{ $site->state ?: '—' }} · {{ $site->domains_count }} domínio(s) · {{ $site->users_count }} usuário(s) · {{ $content?->total??0 }} notícia(s), {{ $content?->published??0 }} publicada(s) · {{ $adsBySite[$site->id]??0 }} anúncio(s) · {{ $site->is_active?'ativo':'inativo' }}</p>
@endforeach
<a class="secondary-action" href="{{ route('admin.sites.index') }}">Gerenciar sites</a></section>

<section class="category-admin-list">
<article class="settings-panel"><h2>Custos globais</h2><p>Execuções de IA · {{ $costs['ai_executions'] }}</p><p>IA estimada · $ {{ number_format($costs['ai_cost'],6,',','.') }}</p><p>Áudio estimado/real · $ {{ number_format($costs['audio_cost'],6,',','.') }}</p></article>
<article class="settings-panel"><h2>Saúde</h2><p>Banco · {{ $health['database']['ok']?'saudável':'requer atenção' }}</p><p>Tabelas · {{ $health['database']['table_count'] }} · migrações {{ $health['database']['migration_count'] }}</p><p>Jobs com falha · {{ $health['failed_jobs'] }}</p><p>Webhooks com falha · {{ $health['failed_webhooks'] }}</p>@foreach($health['providers'] as $provider)<p>{{ $provider->name }} · {{ $provider->health_status }} · {{ $provider->consecutive_failures }} falha(s)</p>@endforeach</article>
</section>

<section class="settings-panel"><h2>Auditoria administrativa</h2>@forelse($auditLogs as $log)<p>{{ $log->created_at?->format('d/m/Y H:i:s') }} · {{ $log->user?->name??'sistema' }} · {{ $log->event }} · {{ data_get($log->new_values,'path') }}</p>@empty<p>Nenhuma mutação administrativa registrada.</p>@endforelse</section>
<section class="settings-panel"><h2>Auditoria de IA</h2>@forelse($aiAuditLogs as $log)<p>{{ $log->created_at->format('d/m/Y H:i:s') }} · {{ $log->user?->name??'sistema' }} · {{ $log->action }} · {{ $log->provider?->name??$log->model }} · {{ $log->result_status }}</p>@empty<p>Nenhum evento de IA registrado.</p>@endforelse</section>
<section class="category-admin-list">
<article class="settings-panel"><h2>Notícias recentes</h2>@forelse($recentNews as $article)<p>{{ $article->site?->name??'sem site' }} · {{ $article->title }} · {{ $article->status }}</p>@empty<p>Sem notícias.</p>@endforelse</article>
<article class="settings-panel"><h2>Campanhas recentes</h2>@forelse($recentAds as $campaign)<p>{{ $campaign->site?->name??'sem site' }} · {{ $campaign->name }} · {{ $campaign->status }}</p>@empty<p>Sem campanhas.</p>@endforelse</article>
</section>
@endsection
