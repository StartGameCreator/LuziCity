@extends('layouts.app', ['title' => 'Tendências RSS - Luzicity'])
@section('content')
<section class="content-band"><p class="eyebrow">Agência assistida</p><h1>Tendências e alertas</h1><p>Sinais automáticos para apoiar decisões humanas. Nenhuma pauta ou notícia é criada automaticamente.</p></section>
<section class="category-admin-list">
@forelse($alerts as $alert)
 <article class="settings-panel"><p class="eyebrow">{{ $alert->severity === 'high' ? 'Alta recorrência' : 'Em crescimento' }}</p><h2>{{ $alert->title }}</h2><p>{{ $alert->pitch_suggestion }}</p><small>{{ $alert->trend->category ?: 'Geral' }} · {{ $alert->trend->location ?: 'Sem local definido' }} · {{ $alert->trend->mention_count }} menções</small></article>
@empty <article class="settings-panel"><strong>Nenhum alerta ativo.</strong><p>Os alertas aparecem quando um assunto ganha recorrência suficiente.</p></article>@endforelse
</section>
<section class="settings-panel"><h2>Assuntos recorrentes</h2>
@forelse($trends as $trend)<p><strong>{{ $trend->term }}</strong> — {{ $trend->mention_count }} menções · crescimento {{ number_format($trend->growth_percent,0,',','.') }}% · {{ $trend->category ?: 'Geral' }}{{ $trend->location ? ' · '.$trend->location : '' }}</p>
@empty <p>Ainda não há dados suficientes.</p>@endforelse
</section>
@endsection
