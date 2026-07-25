@extends('layouts.app', ['title' => $location['name'].' - Luzicity'])

@section('content')
    <section class="content-band city-page">
        <p class="eyebrow">Noticias locais</p>
        <h1>{{ $location['name'] }}</h1>
        <p>Central regional de noticias de {{ $location['name'] }}{{ $location['state'] ? ' - '.$location['state'] : '' }}.</p>
    </section>

    <section class="city-layout" aria-label="Noticias da cidade">
        <div class="city-news-list">
            @foreach($articles as $article)
                <article class="news-row">
                    <div>
                        <p class="eyebrow">{{ $article['section'] }}</p>
                        <h3>{{ $article['title'] }}</h3>
                        <p>{{ $article['excerpt'] }}</p>
                    </div>
                    <span>{{ $article['time'] }}</span>
                </article>
            @endforeach
        </div>

        <aside class="city-side-panel" aria-label="Outras cidades">
            <h2>Cidades</h2>
            <div class="city-side-list">
                @foreach($cityMenu as $city)
                    <a @class(['is-active' => $city['slug'] === $location['slug']]) href="{{ route('cities.show', $city['slug']) }}">
                        <span>{{ $city['name'] }}</span>
                        <small>{{ $city['state'] }}</small>
                    </a>
                @endforeach
            </div>
        </aside>
    </section>
@endsection
