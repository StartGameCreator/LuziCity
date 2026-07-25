@extends('layouts.app', ['title' => 'Notícias - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Notícias</h1>
        <p>Crie, revise e publique notícias com apoio de IA e data de publicação editável.</p>
        <a class="secondary-action inline-action" href="{{ route('admin.news.create') }}">Nova notícia</a>
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
            </article>
        @endforeach

        {{ $articles->links() }}
    </section>
@endsection
