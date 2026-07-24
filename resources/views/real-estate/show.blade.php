@extends('layouts.app', ['title' => $property->title.' - Luzicity', 'meta' => $meta ?? []])

@section('content')
    <section class="vehicle-detail-layout">
        <article class="vehicle-detail-main">
            <div class="vehicle-gallery">
                @forelse(($property->photos ?? []) as $photo)
                    <img src="{{ asset($photo) }}" alt="{{ $property->title }}">
                @empty
                    <div class="vehicle-gallery-empty">Sem foto</div>
                @endforelse
            </div>

            <div class="vehicle-detail-card">
                <p class="eyebrow">{{ $purposeLabels[$property->purpose] ?? 'Imovel' }} - {{ $propertyTypeLabels[$property->property_type] ?? 'Imovel' }}</p>
                <h1>{{ $property->title }}</h1>
                <strong class="vehicle-price">{{ $property->price ? 'R$ '.number_format((float) $property->price, 2, ',', '.') : 'Preco a combinar' }}</strong>
                <p>{{ $property->description ?: 'O anunciante ainda nao adicionou uma descricao detalhada.' }}</p>
            </div>

            @if(filled($property->video_embed_code))
                <section class="vehicle-detail-card vehicle-video-panel" aria-label="Video do imovel">
                    <p class="eyebrow">{{ $property->video_platform === 'facebook' ? 'Facebook' : 'YouTube' }}</p>
                    <h2>Video do anuncio</h2>
                    <div class="media-frame media-frame-{{ $property->video_orientation === 'portrait' ? 'portrait' : 'landscape' }}">
                        {!! $property->video_embed_code !!}
                    </div>
                </section>
            @endif
        </article>

        <aside class="vehicle-contact-panel">
            <h2>Fale com o anunciante</h2>
            <dl>
                <div><dt>Cidade</dt><dd>{{ $property->city }}/{{ $property->state }}</dd></div>
                <div><dt>Bairro</dt><dd>{{ $property->neighborhood ?: 'Nao informado' }}</dd></div>
                <div><dt>Quartos</dt><dd>{{ $property->bedrooms ?? 'Nao informado' }}</dd></div>
                <div><dt>Banheiros</dt><dd>{{ $property->bathrooms ?? 'Nao informado' }}</dd></div>
                <div><dt>Vagas</dt><dd>{{ $property->parking_spaces ?? 'Nao informado' }}</dd></div>
                <div><dt>Area</dt><dd>{{ $property->area_m2 ? $property->area_m2.' m2' : 'Nao informada' }}</dd></div>
            </dl>

            @if($property->whatsapp)
                <a class="primary-action" href="https://wa.me/{{ preg_replace('/\D+/', '', $property->whatsapp) }}" target="_blank" rel="noopener noreferrer">Chamar no WhatsApp</a>
            @endif

            @if($property->phone)
                <a class="secondary-action" href="tel:{{ preg_replace('/\D+/', '', $property->phone) }}">Ligar para anunciante</a>
            @endif
        </aside>
    </section>
@endsection
