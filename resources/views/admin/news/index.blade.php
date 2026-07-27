@extends('layouts.app', ['title' => 'Notícias - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Notícias</h1>
        <p>Crie, revise e publique notícias com apoio de IA e data de publicação editável.</p>
        <div class="admin-actions" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem">
            <a class="secondary-action inline-action" href="{{ route('admin.news.create') }}">Nova notícia</a>
            <a class="primary-action inline-action" href="{{ route('admin.news.ai.create') }}">✨ Gerar notícia com IA</a>
        </div>
    </section>

    <section class="news-list" aria-label="Notícias cadastradas">
        @foreach($articles as $article)
            <article class="news-row">
                <div>
                    <p class="eyebrow">{{ $article->category?->name ?? 'Sem editoria' }} · {{ $article->status }}</p>
                    <h3>{{ $article->title }}</h3>
                    <p>{{ $article->excerpt }}</p>
                </div>
                <a class="secondary-action" href="{{ route('admin.news.edit', $article) }}">Editar</a>
                @if(auth()->user()->hasAnyRole(['Super Admin','Admin']) && $distributionSites->isNotEmpty())
                <details><summary>Distribuir</summary>
                    <form method="post" action="{{ route('admin.news.distributions.store',$article) }}" class="admin-form">@csrf
                        <label>Site <select name="target_site_id">@foreach($distributionSites as $site)<option value="{{ $site->id }}">{{ $site->name }}</option>@endforeach</select></label>
                        <label>Modo <select name="mode"><option value="reference">Referenciar original</option><option value="copy">Copiar como rascunho</option></select></label>
                        <button class="secondary-action">Distribuir</button>
                    </form>
                    @foreach($article->distributions as $distribution)<small>{{ $distribution->targetSite?->name }} · {{ $distribution->mode }}</small>@endforeach
                </details>
                @endif
            </article>
        @endforeach

        {{ $articles->links() }}
    </section>
@endsection
