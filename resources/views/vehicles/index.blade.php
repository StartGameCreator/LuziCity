@extends('layouts.app', ['title' => 'Classificados de Veículos - Luzicity'])

@section('content')
    @php
        $heroImage = data_get($visualBlock ?? [], 'image');
        $heroImageUrl = $heroImage ? (str_starts_with($heroImage, 'http') ? $heroImage : asset($heroImage)) : null;
    @endphp

    <section class="vehicle-hero" @if($heroImageUrl) style="background-image: linear-gradient(135deg, rgba(255,255,255,.84), rgba(255,255,255,.60)), url('{{ $heroImageUrl }}'); background-size: cover; background-position: center;" @endif>
        <div>
            <p class="eyebrow">Classificados de Veículos</p>
            <div class="vehicle-type-menu" aria-label="Tipo de veículo">
                @foreach($vehicleTypes as $typeKey => $typeLabel)
                    <a
                        @class(['vehicle-type-option', 'is-active' => $selectedVehicleType === $typeKey])
                        href="{{ route('vehicles.index', ['vehicle_type' => $typeKey]) }}"
                    >
                        {{ $typeLabel }}
                    </a>
                @endforeach
            </div>

            <x-vehicle-brand-logos :type="$selectedVehicleType" />
            <p>Um canal local para carros, motos e utilitários, com busca rápida, anúncios com fotos e contato direto com o vendedor.</p>
        </div>

        <a class="primary-action" href="{{ route('vehicles.create') }}">Anunciar veículo</a>
    </section>

    @if($showAds)
        <x-ad-slot name="vehicles_top" label="Publicidade dos classificados" variant="leaderboard" />
    @endif

    <section class="vehicle-search-panel" aria-label="Buscar veículos">
        <form method="get" action="{{ route('vehicles.index') }}" class="vehicle-search-form">
            <input type="hidden" name="vehicle_type" value="{{ $selectedVehicleType }}">
            <label>
                Buscar
                <input name="q" value="{{ request('q') }}" placeholder="Marca, modelo ou cidade">
            </label>
            <label>
                Marca
                <input name="brand" value="{{ request('brand') }}" placeholder="Toyota, Fiat, Honda">
            </label>
            <label>
                Cidade
                <input name="city" value="{{ request('city') }}" placeholder="Luziânia">
            </label>
            <label>
                Preço máximo
                <input type="number" min="0" step="100" name="max_price" value="{{ request('max_price') }}" placeholder="80000">
            </label>
            <button class="secondary-action" type="submit">Filtrar</button>
        </form>

        <div class="vehicle-results-head">
            <div>
                <p class="eyebrow">Resultado da busca</p>
                <h2>{{ $listings->total() }} {{ $listings->total() === 1 ? 'veículo encontrado' : 'veículos encontrados' }} em {{ $vehicleTypes[$selectedVehicleType] }}</h2>
            </div>

            @if(request()->hasAny(['q', 'brand', 'city', 'state', 'max_price']))
                <a class="secondary-action" href="{{ route('vehicles.index', ['vehicle_type' => $selectedVehicleType]) }}">Limpar filtros</a>
            @endif
        </div>

        <section class="vehicle-market-layout vehicle-results-layout">
            <div class="vehicle-grid" aria-label="Lista de veículos encontrados">
                @forelse($listings as $vehicle)
                    <a class="vehicle-card" href="{{ route('vehicles.show', $vehicle) }}">
                        <span class="vehicle-photo" style="{{ $vehicle->primaryPhotoUrl() ? 'background-image: url('.$vehicle->primaryPhotoUrl().')' : '' }}">
                            @unless($vehicle->primaryPhotoUrl())
                                <span>Sem foto</span>
                            @endunless
                        </span>
                        <span class="vehicle-card-body">
                            <strong>{{ $vehicle->title }}</strong>
                            <span>{{ $vehicle->brand }} {{ $vehicle->model }} • {{ $vehicle->year }}</span>
                            <span>{{ $vehicle->city }}/{{ $vehicle->state }}</span>
                            <b>{{ $vehicle->price ? 'R$ '.number_format((float) $vehicle->price, 2, ',', '.') : 'Preço a combinar' }}</b>
                        </span>
                    </a>
                @empty
                    <section class="vehicle-video-carousel media-carousel" aria-label="Canal de venda de veículos" data-carousel>
                        <div class="media-carousel-head">
                            <p class="eyebrow">Canal de veículos</p>
                            <h2>Ofertas em vídeo</h2>
                        </div>

                        <div class="media-carousel-track">
                            @foreach($vehicleVideoBanners as $index => $banner)
                                <article class="media-slide @if($index === 0) is-active @endif" data-carousel-slide>
                                    <div class="media-frame media-frame-landscape">
                                        @if(data_get($banner, 'embed_code'))
                                            {!! data_get($banner, 'embed_code') !!}
                                        @else
                                            <span aria-hidden="true"></span>
                                        @endif
                                    </div>
                                    @if(data_get($banner, 'title'))
                                        <strong>{{ data_get($banner, 'title') }}</strong>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforelse
            </div>

            @if($showAds)
                <div class="vehicle-sidebar-ad">
                    <x-ad-slot name="vehicles_sidebar" label="Publicidade lateral de veículos" variant="infeed" />
                </div>
            @endif
        </section>

        {{ $listings->links() }}
    </section>

    @if($showAds)
        <x-ad-slot name="vehicles_after_search" label="Publicidade após busca de veículos" variant="wide" />
    @endif

    @if($featuredListings->isNotEmpty())
        <section class="vehicle-featured-grid" aria-label="Veículos em destaque">
            @foreach($featuredListings as $vehicle)
                <a class="vehicle-card vehicle-card-featured" href="{{ route('vehicles.show', $vehicle) }}">
                    <span class="vehicle-photo" style="{{ $vehicle->primaryPhotoUrl() ? 'background-image: url('.$vehicle->primaryPhotoUrl().')' : '' }}"></span>
                    <span class="vehicle-card-body">
                        <small>Destaque</small>
                        <strong>{{ $vehicle->title }}</strong>
                        <span>{{ $vehicle->brand }} {{ $vehicle->model }} • {{ $vehicle->year }}</span>
                    </span>
                </a>
            @endforeach
        </section>
    @endif

    <section class="vehicle-popular-carousel media-carousel" aria-label="Veículos mais clicados ou buscados" data-carousel>
        <div class="media-carousel-head">
            <p class="eyebrow">Mais procurados</p>
            <h2>Veículos em alta</h2>
        </div>

        <div class="media-carousel-track">
            @forelse($popularListings as $index => $vehicle)
                <a class="vehicle-popular-slide media-slide @if($index === 0) is-active @endif" href="{{ route('vehicles.show', $vehicle) }}" data-carousel-slide>
                    <span class="vehicle-popular-photo" style="{{ $vehicle->primaryPhotoUrl() ? 'background-image: url('.$vehicle->primaryPhotoUrl().')' : '' }}">
                        @unless($vehicle->primaryPhotoUrl())
                            <span>Sem foto</span>
                        @endunless
                    </span>
                    <span class="vehicle-popular-info">
                        <small>{{ ($vehicle->views_count + $vehicle->search_hits) ?: 'Novo' }} interações</small>
                        <strong>{{ $vehicle->title }}</strong>
                        <span>{{ $vehicle->brand }} {{ $vehicle->model }} • {{ $vehicle->city }}/{{ $vehicle->state }}</span>
                        <b>{{ $vehicle->price ? 'R$ '.number_format((float) $vehicle->price, 2, ',', '.') : 'Preço a combinar' }}</b>
                    </span>
                </a>
            @empty
                <article class="vehicle-popular-slide media-slide is-active" data-carousel-slide>
                    <span class="vehicle-popular-photo">
                        <span>Classificados</span>
                    </span>
                    <span class="vehicle-popular-info">
                        <small>Em breve</small>
                        <strong>Os veículos mais clicados aparecerão aqui</strong>
                        <span>Publique os primeiros anúncios para movimentar o carrossel.</span>
                    </span>
                </article>
            @endforelse
        </div>
    </section>

    @if($showAds)
        <x-ad-slot name="vehicles_footer" label="Publicidade final dos classificados" variant="leaderboard" />
    @endif

    <section class="local-sponsors-section vehicle-dealer-sponsors" aria-label="Lojas de veículos locais">
        <div class="local-sponsors-grid">
            @foreach(range(1, 12) as $dealerIndex)
                <a class="local-sponsor-banner" href="#" aria-label="Espaço para loja de veículos local {{ $dealerIndex }}">
                    <span class="sr-only">Espaço para loja de veículos local {{ $dealerIndex }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endsection
