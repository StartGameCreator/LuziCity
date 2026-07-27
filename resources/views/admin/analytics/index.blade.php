@extends('layouts.app')
@section('title','Dashboard de audiência')
@section('content')
<section class="content-band"><p class="eyebrow">Fases 15.3 e 15.4 · Analytics próprio</p><h1>Dashboard de audiência</h1><p>Audiência, conversões e desempenho editorial.</p><form method="get"><label>Período <select name="period" onchange="this.form.submit()"><option value="7" @selected($period===7)>7 dias</option><option value="30" @selected($period===30)>30 dias</option><option value="90" @selected($period===90)>90 dias</option></select></label></form></section>
<section class="category-admin-list">@foreach([['Visualizações',$metrics['views']],['Visitantes',$metrics['visitors']],['Visitantes identificados',$metrics['identified_visitors']],['Leitura média',$metrics['read_time'].'s'],['Taxa de conversão',number_format($metrics['conversion_rate'],2,',','.').'%']] as [$label,$value])<article class="settings-panel"><small>{{ $label }}</small><h2>{{ $value }}</h2></article>@endforeach</section>
<section class="settings-panel"><h2>Evolução diária</h2>@forelse($daily as $day)<p>{{ \Illuminate\Support\Carbon::parse($day->day)->format('d/m') }} · {{ $day->visitors }} visitantes · {{ $day->views }} visualizações</p>@empty<p>Sem dados no período.</p>@endforelse</section>
<section class="settings-panel"><h2>Notícias</h2>@forelse($news as $item)<p><strong>{{ $item->title }}</strong> · {{ $item->visitors }} visitantes · {{ $item->views }} visualizações · {{ (int)$item->reading_time }}s</p>@empty<p>Nenhuma notícia medida.</p>@endforelse</section>

<section class="content-band"><p class="eyebrow">Fase 15.4</p><h2>Analytics editorial</h2><p>Leitura, abandono, compartilhamento e desempenho do conteúdo.</p></section>
<section class="category-admin-list">@foreach([
    ['Leitura editorial média',$metrics['editorial_read_time'].'s'],
    ['Conclusão de leitura',number_format($metrics['completion_rate'],2,',','.').'%'],
    ['Abandono',number_format($metrics['abandonment_rate'],2,',','.').'%'],
    ['Compartilhamentos',$metrics['shares']],
] as [$label,$value])<article class="settings-panel"><small>{{ $label }}</small><h2>{{ $value }}</h2></article>@endforeach</section>
<section class="category-admin-list">
    <article class="settings-panel"><h2>Desempenho por categoria</h2>@forelse($categories as $category)<p><strong>{{ $category->category_name }}</strong> · {{ $category->views }} visualizações · {{ (int)$category->reading_time }}s · {{ (int)$category->scroll_depth }}% de rolagem · {{ (int)$category->shares }} compartilhamentos</p>@empty<p>Sem categorias medidas.</p>@endforelse</article>
    <article class="settings-panel"><h2>Desempenho por horário</h2>@forelse($hours as $hour)<p><strong>{{ str_pad((string)$hour->view_hour, 2, '0', STR_PAD_LEFT) }}h</strong> · {{ $hour->visitors }} visitantes · {{ $hour->views }} visualizações · {{ (int)$hour->reading_time }}s</p>@empty<p>Sem horários medidos.</p>@endforelse</article>
</section>

<section class="category-admin-list"><article class="settings-panel"><h2>Autores</h2>@forelse($authors as $author)<p>{{ $author->name }} · {{ $author->visitors }} visitantes · {{ $author->views }} visualizações</p>@empty<p>Sem autores medidos.</p>@endforelse</article><article class="settings-panel"><h2>Conversões</h2><p>Novas assinaturas · {{ $conversions['subscriptions'] }}</p><p>Pagamentos aprovados · {{ $conversions['payments'] }}</p><p>Benefícios resgatados · {{ $conversions['benefits'] }}</p><p><strong>Total · {{ $conversions['total'] }}</strong></p></article></section>
<section class="category-admin-list"><article class="settings-panel"><h2>Origens</h2>@foreach($sources as $source)<p>{{ $source->source_name }} · {{ $source->visitors }} visitantes · {{ $source->views }} visualizações</p>@endforeach</article><article class="settings-panel"><h2>Campanhas</h2>@forelse($campaigns as $campaign)<p>{{ $campaign->campaign }} · {{ $campaign->visitors }} visitantes · {{ $campaign->views }} visualizações</p>@empty<p>Sem campanhas identificadas.</p>@endforelse</article></section>
<section class="settings-panel"><h2>Páginas</h2>@foreach($pages as $page)<p>{{ $page->page_path }} · {{ $page->visitors }} visitantes · {{ $page->views }} visualizações</p>@endforeach</section>
@endsection
