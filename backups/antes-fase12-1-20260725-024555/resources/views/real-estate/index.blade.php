@extends('layouts.app', ['title' => 'Imóveis - Luzicity'])

@section('content')
    @php
        $heroImage = data_get($visualBlock ?? [], 'image');
        $heroImageUrl = $heroImage ? (str_starts_with($heroImage, 'http') ? $heroImage : asset($heroImage)) : null;
    @endphp

    <section class="vehicle-hero property-hero" @if($heroImageUrl) style="background-image: linear-gradient(135deg, rgba(255,255,255,.84), rgba(255,255,255,.60)), url('{{ $heroImageUrl }}'); background-size: cover; background-position: center;" @endif>
        <div>
            <p class="eyebrow">Imóveis</p>
            <h1>Compra, venda e aluguel</h1>
            <p>Encontre casas, apartamentos, lotes, salas comerciais e chácaras com filtros rápidos e contato direto com o anunciante.</p>
        </div>
        <a class="primary-action" href="{{ route('real-estate.create') }}">Anunciar imóvel</a>
    </section>

    @if($showAds)
        <x-ad-slot name="home_top" label="Publicidade dos imóveis" variant="leaderboard" />
    @endif

    <section class="vehicle-search-panel property-search-panel" aria-label="Buscar imóveis">
        <form method="get" action="{{ route('real-estate.index') }}" class="vehicle-search-form property-search-form">
            <label>
                Finalidade
                <select name="purpose">
                    <option value="">Todas</option>
                    @foreach($purposeLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('purpose') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Tipo
                <select name="property_type">
                    <option value="">Todos</option>
                    @foreach($propertyTypeLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('property_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Buscar
                <input name="q" value="{{ request('q') }}" placeholder="Bairro, cidade ou título">
            </label>
            <label>
                Cidade
                <input name="city" value="{{ request('city') }}" placeholder="Luziânia">
            </label>
            <label>
                Quartos
                <input type="number" min="0" name="min_bedrooms" value="{{ request('min_bedrooms') }}" placeholder="2">
            </label>
            <label>
                Preço máximo
                <input type="number" min="0" step="100" name="max_price" value="{{ request('max_price') }}" placeholder="350000">
            </label>
            <button class="secondary-action" type="submit">Filtrar</button>
        </form>

        <div class="vehicle-results-head">
            <div>
                <p class="eyebrow">Resultado da busca</p>
                <h2>{{ $listings->total() }} {{ $listings->total() === 1 ? 'imóvel encontrado' : 'imóveis encontrados' }}</h2>
            </div>

            @if(request()->hasAny(['purpose', 'property_type', 'q', 'city', 'state', 'min_bedrooms', 'max_price']))
                <a class="secondary-action" href="{{ route('real-estate.index') }}">Limpar filtros</a>
            @endif
        </div>

        <section class="vehicle-grid property-grid" aria-label="Lista de imóveis encontrados">
            @forelse($listings as $property)
                <a class="vehicle-card property-card" href="{{ route('real-estate.show', $property) }}">
                    <span class="vehicle-photo" style="{{ $property->primaryPhotoUrl() ? 'background-image: url('.$property->primaryPhotoUrl().')' : '' }}">
                        @unless($property->primaryPhotoUrl())
                            <span>Sem foto</span>
                        @endunless
                    </span>
                    <span class="vehicle-card-body">
                        <small>{{ $purposeLabels[$property->purpose] ?? 'Imóvel' }} • {{ $propertyTypeLabels[$property->property_type] ?? 'Imóvel' }}</small>
                        <strong>{{ $property->title }}</strong>
                        <span>{{ $property->neighborhood ? $property->neighborhood.' • ' : '' }}{{ $property->city }}/{{ $property->state }}</span>
                        <span>{{ $property->bedrooms ?? 0 }} quartos • {{ $property->area_m2 ? $property->area_m2.' m²' : 'Área não informada' }}</span>
                        <b>{{ $property->price ? 'R$ '.number_format((float) $property->price, 2, ',', '.') : 'Preço a combinar' }}</b>
                    </span>
                </a>
            @empty
                <article class="vehicle-empty-state">
                    <h2>Nenhum imóvel encontrado</h2>
                    <p>Os primeiros anúncios de compra, venda e aluguel aparecerão aqui.</p>
                    <a class="secondary-action" href="{{ route('real-estate.create') }}">Anunciar imóvel</a>
                </article>
            @endforelse
        </section>

        {{ $listings->links() }}
    </section>

    @if($featuredListings->isNotEmpty())
        <section class="vehicle-featured-grid" aria-label="Imóveis em destaque">
            @foreach($featuredListings as $property)
                <a class="vehicle-card" href="{{ route('real-estate.show', $property) }}">
                    <span class="vehicle-photo" style="{{ $property->primaryPhotoUrl() ? 'background-image: url('.$property->primaryPhotoUrl().')' : '' }}"></span>
                    <span class="vehicle-card-body">
                        <small>Destaque</small>
                        <strong>{{ $property->title }}</strong>
                        <span>{{ $property->city }}/{{ $property->state }}</span>
                    </span>
                </a>
            @endforeach
        </section>
    @endif
@endsection
