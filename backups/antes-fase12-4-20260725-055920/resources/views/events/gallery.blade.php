@extends('layouts.app', ['title' => 'Fotos de Eventos - Luzicity', 'meta' => $meta ?? []])

@section('content')
    <section class="events-gallery-hero" aria-labelledby="eventos-titulo">
        <div class="events-gallery-hero-image" style="background-image: url('{{ $eventImageUrl }}')" aria-hidden="true"></div>
        <div class="events-gallery-hero-content">
            <p class="eyebrow">{{ $event['subtitle'] }}</p>
            <h1 id="eventos-titulo">{{ $event['title'] }}</h1>
            <p>{{ $event['location'] }}</p>
        </div>
    </section>

    <section class="events-gallery-layout" aria-label="Reportagem e galeria do evento">
        <article class="events-report">
            <p class="eyebrow">Reportagem do evento</p>
            <h2>{{ $event['title'] }}</h2>
            <dl class="events-report-meta">
                <div>
                    <dt>Local</dt>
                    <dd>{{ $event['location'] }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>{{ $event['date'] }}</dd>
                </div>
            </dl>
            <p>{{ $event['report'] }}</p>
        </article>

        <section class="events-photo-carousel media-carousel" aria-label="Fotos em destaque do evento" data-carousel>
            <div class="media-carousel-head">
                <p class="eyebrow">Galeria especial</p>
                <h2>Destaques</h2>
            </div>

            <div class="media-carousel-track">
                @foreach($photos as $index => $photo)
                    <article class="media-slide @if($index === 0) is-active @endif" data-carousel-slide>
                        <figure class="events-carousel-photo">
                            <img src="{{ $photo['image'] }}" alt="{{ $photo['title'] }}">
                            <figcaption>
                                <strong>{{ $photo['title'] }}</strong>
                                <span>{{ $photo['location'] }}</span>
                            </figcaption>
                        </figure>
                    </article>
                @endforeach
            </div>
        </section>
    </section>

    <section class="events-photo-grid" aria-label="Todas as fotos do evento">
        @foreach($photos as $photo)
            <article class="events-photo-card">
                <img src="{{ $photo['image'] }}" alt="{{ $photo['title'] }}">
                <div>
                    <strong>{{ $photo['title'] }}</strong>
                    <span>{{ $photo['location'] }}</span>
                </div>
            </article>
        @endforeach
    </section>
@endsection
