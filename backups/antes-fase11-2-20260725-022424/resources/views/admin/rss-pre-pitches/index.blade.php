@extends('layouts.app',['title'=>'Pré-pautas RSS - Luzicity'])
@section('content')
<section class="content-band"><p class="eyebrow">Agência assistida</p><h1>Pré-pautas RSS</h1><p>Material auxiliar para revisão humana. Nada nesta tela cria ou publica notícia automaticamente.</p></section>
<section class="category-admin-list">
@forelse($items as $item)<article class="settings-panel"><p class="eyebrow">{{ $item->status }}</p><h2>{{ $item->title }}</h2><p>{{ $item->summary }}</p>
<h3>Perguntas a apurar</h3><ul>@foreach($item->questions as $question)<li>{{ $question }}</li>@endforeach</ul>
<h3>Riscos</h3><ul>@foreach($item->risks as $risk)<li>{{ $risk }}</li>@endforeach</ul>
<p><strong>Relevância local:</strong> {{ $item->local_relevance }}</p><p><strong>Recomendação:</strong> {{ $item->editorial_recommendation }}</p>
<h3>Fontes</h3><ul>@foreach($item->source_links as $source)<li><a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">{{ $source['name'] ?: $source['title'] }}</a></li>@endforeach</ul></article>
@empty <article class="settings-panel"><strong>Nenhuma pré-pauta.</strong><p>Gere uma a partir de um item coletado na Importação RSS.</p></article>@endforelse
</section>{{ $items->links() }}
@endsection
