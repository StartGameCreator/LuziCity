@extends('layouts.app',['title'=>'Fila de Aprovação - Luzicity'])
@section('content')
<section class="content-band"><p class="eyebrow">Agência assistida</p><h1>Fila de aprovação</h1><p>Toda decisão exige ação humana. Aprovar envia para redação como ideia; nunca publica notícia.</p></section>
@foreach([['Itens coletados',$articles,'article'],['Pré-pautas',$prePitches,'pre-pitch']] as [$heading,$items,$type])
<section class="category-admin-list"><h2>{{ $heading }}</h2>
@forelse($items as $item)<article class="settings-panel"><h3>{{ $item->title }}</h3><p>{{ $item->excerpt ?? $item->summary }}</p>
<form method="post" action="{{ $type === 'article' ? route('admin.agency-approval.article',$item) : route('admin.agency-approval.pre-pitch',$item) }}" class="admin-form">@csrf
<label>Observação <input name="note" maxlength="2000"></label><button class="secondary-action" name="action" value="approve">Aprovar para redação</button><button class="secondary-action" name="action" value="reject">Rejeitar</button><button class="secondary-action" name="action" value="archive">Arquivar</button></form></article>
@empty <article class="settings-panel"><p>Nenhum item pendente.</p></article>@endforelse</section>
@endforeach
<section class="category-admin-list"><h2>Rascunhos editoriais</h2>@forelse($drafts as $draft)<article class="settings-panel"><h3>{{ $draft->title }}</h3><p>Status: {{ $draft->workflow_status }}</p><a class="secondary-action" href="{{ route('admin.news.edit',$draft) }}">Abrir na redação</a></article>@empty<p>Nenhum rascunho.</p>@endforelse</section>
@endsection
