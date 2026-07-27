@extends('layouts.app')
@section('title', 'Campanhas publicitárias')
@section('content')
<section class="content-band">
    <p class="eyebrow">Fase 13.2 · Comercial</p>
    <h1>Campanhas publicitárias</h1>
    <p>Banners, posições, períodos, segmentação e limites de entrega.</p>
    <a class="primary-action" href="{{ route('admin.campaigns.create') }}">Nova campanha</a>
</section>
<section class="category-admin-list">
    @foreach([['Total', $metrics['total']], ['Em veiculação', $metrics['active']], ['Impressões', $metrics['impressions']], ['Cliques', $metrics['clicks']]] as [$label, $value])
        <article class="settings-panel"><small>{{ $label }}</small><h2>{{ number_format($value, 0, ',', '.') }}</h2></article>
    @endforeach
</section>
<section class="settings-panel">
    <form method="get" class="admin-form">
        <label>Pesquisar<input name="q" value="{{ request('q') }}" placeholder="Nome da campanha"></label>
        <label>Status<select name="status"><option value="">Todos</option>@foreach(['draft'=>'Rascunho','pending'=>'Aguardando aprovação','active'=>'Ativa','paused'=>'Pausada','finished'=>'Finalizada','cancelled'=>'Cancelada'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select></label>
        <label>Posição<input name="placement" value="{{ request('placement') }}" placeholder="Ex.: home_top"></label>
        <button class="secondary-action">Filtrar</button>
    </form>
</section>
<section class="category-admin-list">
@forelse($campaigns as $campaign)
    <article class="settings-panel">
        <p class="eyebrow">{{ $campaign->status }} · {{ $campaign->placement }}</p>
        <h2><a href="{{ route('admin.campaigns.edit', $campaign) }}">{{ $campaign->name }}</a></h2>
        <p>{{ $campaign->advertiserProfile?->company_name ?: 'Sem anunciante' }}</p>
        <p>{{ number_format($campaign->impressions_count, 0, ',', '.') }} impressões · {{ number_format($campaign->clicks_count, 0, ',', '.') }} cliques · CTR {{ number_format($campaign->ctr, 2, ',', '.') }}%</p>
        <a class="secondary-action" href="{{ route('admin.campaigns.edit', $campaign) }}">Gerenciar</a>
    </article>
@empty
    <p>Nenhuma campanha cadastrada.</p>
@endforelse
</section>
{{ $campaigns->links() }}
@endsection
