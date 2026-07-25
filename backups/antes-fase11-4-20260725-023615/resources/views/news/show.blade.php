@extends('layouts.app', [
    'title' => $article->title.' - '.data_get(\App\Models\Setting::siteIdentity(), 'name', 'Luzicity'),
    'meta' => $meta,
])

@section('content')
    <article class="news-detail-shell">
        <header class="news-detail-head">
            <p class="eyebrow">{{ $article->category?->name ?: 'Noticia' }}</p>
            <h1>{{ $article->title }}</h1>
            @if($article->excerpt)
                <p>{{ $article->excerpt }}</p>
            @endif
            <div class="story-meta">
                <span>{{ $article->author?->name ?: 'Redacao' }}</span>
                <span>{{ optional($article->published_at)->format('d/m/Y H:i') }}</span>
            </div>
        </header>

        @if($article->cover_image_path)
            <img class="news-detail-cover" src="{{ asset($article->cover_image_path) }}" alt="{{ $article->cover_image_alt ?: $article->title }}">
        @endif

        @if($showAds)
            <x-ad-slot name="home_after_hero" label="Publicidade na noticia" variant="wide" />
        @endif

        <div class="news-detail-body">
            {!! nl2br(e($article->body)) !!}
        </div>
    </article>
@endsection
