@extends('layouts.app', ['title' => $vehicle->title.' - Luzicity', 'meta' => $meta ?? []])

@section('content')
    <section class="vehicle-detail-layout">
        <article class="vehicle-detail-main">
            <div class="vehicle-gallery">
                @forelse(($vehicle->photos ?? []) as $photo)
                    <img src="{{ asset($photo) }}" alt="{{ $vehicle->title }}">
                @empty
                    <div class="vehicle-gallery-empty">Sem foto</div>
                @endforelse
            </div>

            <div class="vehicle-detail-card">
                <p class="eyebrow">{{ $vehicle->city }}/{{ $vehicle->state }}</p>
                <h1>{{ $vehicle->title }}</h1>
                <strong class="vehicle-price">{{ $vehicle->price ? 'R$ '.number_format((float) $vehicle->price, 2, ',', '.') : 'Preco a combinar' }}</strong>
                <p>{{ $vehicle->description ?: 'O anunciante ainda nao adicionou uma descricao detalhada.' }}</p>
            </div>

            @if(filled($vehicle->video_embed_code))
                <section class="vehicle-detail-card vehicle-video-panel" aria-label="Video do veiculo">
                    <p class="eyebrow">{{ $vehicle->video_platform === 'facebook' ? 'Facebook' : 'YouTube' }}</p>
                    <h2>Video do anuncio</h2>
                    <div class="media-frame media-frame-{{ $vehicle->video_orientation === 'portrait' ? 'portrait' : 'landscape' }}">
                        {!! $vehicle->video_embed_code !!}
                    </div>
                </section>
            @endif
        </article>

        <aside class="vehicle-contact-panel">
            <h2>Fale com o anunciante</h2>
            <dl>
                <div><dt>Marca</dt><dd>{{ $vehicle->brand }}</dd></div>
                <div><dt>Modelo</dt><dd>{{ $vehicle->model }}</dd></div>
                <div><dt>Ano</dt><dd>{{ $vehicle->year }}</dd></div>
                <div><dt>KM</dt><dd>{{ $vehicle->mileage ? number_format($vehicle->mileage, 0, ',', '.') : 'Nao informado' }}</dd></div>
                <div><dt>Cambio</dt><dd>{{ $vehicle->transmission ?: 'Nao informado' }}</dd></div>
                <div><dt>Combustivel</dt><dd>{{ $vehicle->fuel ?: 'Nao informado' }}</dd></div>
            </dl>

            @if($vehicle->whatsapp)
                <a class="primary-action" href="https://wa.me/{{ preg_replace('/\D+/', '', $vehicle->whatsapp) }}" target="_blank" rel="noopener noreferrer">Chamar no WhatsApp</a>
            @endif

            @if($vehicle->phone)
                <a class="secondary-action" href="tel:{{ preg_replace('/\D+/', '', $vehicle->phone) }}">Ligar para anunciante</a>
            @endif

            @if($showAds)
                <x-ad-slot name="vehicles_sidebar" label="Publicidade lateral de veiculos" variant="infeed" />
            @endif
        </aside>
    </section>
@endsection
