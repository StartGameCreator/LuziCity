@extends('layouts.app', ['title' => 'Luzicity - Notícias'])

@section('content')
    @php
        $liveEnabled = filter_var(data_get($homeLiveBroadcast, 'enabled'), FILTER_VALIDATE_BOOLEAN);
        $liveEmbedCode = trim((string) data_get($homeLiveBroadcast, 'embed_code', ''));
        $liveOrientation = data_get($homeLiveBroadcast, 'orientation') === 'landscape' ? 'landscape' : 'portrait';
        $liveExternalUrl = trim((string) data_get($homeLiveBroadcast, 'external_url', ''));
        $eventBlock = data_get($visualBlocks ?? [], 'events', []);
        $realEstateBlock = data_get($visualBlocks ?? [], 'real_estate', []);
        $vehicleBlock = data_get($visualBlocks ?? [], 'vehicles', []);
        $eventImage = data_get($eventBlock, 'image');
        $realEstateImage = data_get($realEstateBlock, 'image');
        $vehicleImage = data_get($vehicleBlock, 'image');
        $eventImageUrl = $eventImage ? (str_starts_with($eventImage, 'http') ? $eventImage : asset($eventImage)) : null;
        $realEstateImageUrl = $realEstateImage ? (str_starts_with($realEstateImage, 'http') ? $realEstateImage : asset($realEstateImage)) : null;
        $vehicleImageUrl = $vehicleImage ? (str_starts_with($vehicleImage, 'http') ? $vehicleImage : asset($vehicleImage)) : null;
        $eventLink = filled(data_get($eventBlock, 'link')) && data_get($eventBlock, 'link') !== '#' ? data_get($eventBlock, 'link') : route('events.gallery');
    @endphp

    @if($liveEnabled && filled($liveEmbedCode))
        <section class="home-live-broadcast home-live-broadcast-{{ $liveOrientation }}" aria-label="{{ data_get($homeLiveBroadcast, 'title') ?: 'Transmissao especial ao vivo' }}">
            <div class="home-live-frame media-frame media-frame-{{ $liveOrientation }}">
                {!! \App\Services\Security\EmbedCodeSanitizer::sanitize($liveEmbedCode) !!}
            </div>

            @if(filled($liveExternalUrl))
                <a class="home-live-link secondary-action" href="{{ $liveExternalUrl }}" target="_blank" rel="noopener noreferrer">
                    Abrir transmissao
                </a>
            @endif
        </section>
    @endif
    <section class="media-banner-zone" aria-label="Banners rotativos de vídeo">
        <div class="media-carousel media-carousel-youtube" data-carousel>
            <div class="media-carousel-head">
                <p class="eyebrow">Vídeos em destaque</p>
                <h2>YouTube</h2>
            </div>

            <div class="media-carousel-track">
                @foreach($youtubeBanners as $index => $banner)
                    <article class="media-slide @if($index === 0) is-active @endif" data-carousel-slide>
                        <div class="media-frame media-frame-landscape">
                            @if(data_get($banner, 'embed_code'))
                                {!! \App\Services\Security\EmbedCodeSanitizer::sanitize(data_get($banner, 'embed_code')) !!}
                            @elseif(data_get($banner, 'image_url'))
                                <img class="media-frame-image" src="{{ data_get($banner, 'image_url') }}" alt="{{ data_get($banner, 'title') ?: 'Reportagem audiovisual' }}">
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
        </div>

        <div class="media-carousel media-carousel-reels" data-carousel>
            <div class="media-carousel-head">
                <p class="eyebrow">Reels</p>
                <h2>Facebook</h2>
            </div>

            <div class="media-carousel-track">
                @foreach($facebookReels as $index => $banner)
                    <article class="media-slide @if($index === 0) is-active @endif" data-carousel-slide>
                        <div class="media-frame media-frame-portrait">
                            @if(data_get($banner, 'embed_code'))
                                {!! \App\Services\Security\EmbedCodeSanitizer::sanitize(data_get($banner, 'embed_code')) !!}
                            @elseif(data_get($banner, 'image_url'))
                                <img class="media-frame-image" src="{{ data_get($banner, 'image_url') }}" alt="{{ data_get($banner, 'title') ?: 'Reportagem audiovisual' }}">
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
        </div>
    </section>

    @if($showAds)
        <x-ad-slot name="home_top" label="Publicidade no topo" variant="leaderboard" />
    @endif

    <section class="news-hero" aria-labelledby="manchete-principal">
        <article class="lead-story">
            <p class="eyebrow">{{ data_get($leadArticle, 'section') ?? data_get($leadArticle, 'category.name') ?? 'Notícias' }}</p>
            <h1 id="manchete-principal">{{ data_get($leadArticle, 'title') }}</h1>
            <p>{{ data_get($leadArticle, 'excerpt') }}</p>
            <div class="story-meta">
                <span>{{ data_get($leadArticle, 'author_name') ?? data_get($leadArticle, 'author.name') ?? 'Redação Luzicity' }}</span>
                <span>{{ data_get($leadArticle, 'published_label') ?? optional(data_get($leadArticle, 'published_at'))->format('d/m/Y H:i') }}</span>
            </div>
        </article>

        <aside class="side-stack" aria-label="Destaques">
            @foreach($featuredArticles as $article)
                <article class="compact-story">
                    <p class="eyebrow">{{ data_get($article, 'section') ?? data_get($article, 'category.name') ?? 'Destaque' }}</p>
                    <h2>{{ data_get($article, 'title') }}</h2>
                    <p>{{ data_get($article, 'excerpt') }}</p>
                </article>
            @endforeach
        </aside>
    </section>

    @if($showAds)
        <x-ad-slot name="home_after_hero" label="Publicidade após manchete" variant="wide" />
        <x-ad-slot name="home_before_latest" label="Publicidade antes das últimas notícias" variant="infeed" />
    @endif

    <section id="ultimas" class="latest-section" aria-label="Últimas notícias">
        <div class="news-list">
            @foreach($latestArticles as $article)
                <article class="news-row">
                    <div>
                        <p class="eyebrow">{{ data_get($article, 'section') ?? data_get($article, 'category.name') ?? 'Notícias' }}</p>
                        <h3>{{ data_get($article, 'title') }}</h3>
                        <p>{{ data_get($article, 'excerpt') }}</p>
                    </div>
                    <span>{{ data_get($article, 'published_label') ?? optional(data_get($article, 'published_at'))->format('d/m/Y') }}</span>
                </article>
            @endforeach
        </div>
    </section>

    @if($showAds)
        <x-ad-slot name="home_after_latest" label="Publicidade após notícias" variant="wide" />
        <x-ad-slot name="home_before_topics" label="Publicidade antes das editorias" variant="infeed" />
    @endif

    <a class="real-estate-menu real-estate-image-menu" href="{{ data_get($realEstateBlock, 'link') ?: route('real-estate.index') }}" aria-label="Abrir classificados de imóveis" @if($realEstateImageUrl) style="background-image: url('{{ $realEstateImageUrl }}')" @endif>
        <span class="sr-only">Imóveis - Compra, Venda e Aluguel</span>
    </a>

    <section id="editorias" class="topic-strip" aria-label="Editorias">
        @foreach($topicMenu as $topic)
            @php
                $children = collect(data_get($topic, 'children', []));
            @endphp
            <div class="topic-menu-item">
                <a href="#">
                    {{ data_get($topic, 'name') }}
                </a>

                @if($children->isNotEmpty())
                    <div class="topic-submenu" aria-label="Submenus de {{ data_get($topic, 'name') }}">
                        @foreach($children as $child)
                            <a href="#">{{ data_get($child, 'name') }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    @if($showAds)
        <x-ad-slot name="home_footer" label="Publicidade final" variant="leaderboard" />
    @endif

    <section class="rss-section" aria-labelledby="fotos-eventos-titulo">
        <a class="event-photos-menu" href="{{ $eventLink }}" aria-label="Abrir Fotos Eventos" @if($eventImageUrl) style="background-image: url('{{ $eventImageUrl }}')" @endif>
            <span id="fotos-eventos-titulo" class="sr-only">{{ data_get($eventBlock, 'label') ?: 'Fotos Eventos' }}</span>
        </a>

        <div class="rss-grid rss-news-grid">
            @forelse($rssItems as $item)
                <article class="rss-card rss-news-card">
                    @if(filled(data_get($item, 'image')))
                        <img class="rss-card-image" src="{{ data_get($item, 'image') }}" alt="">
                    @endif
                    <span>{{ data_get($item, 'category') }} - {{ data_get($item, 'source') }}</span>
                    @if(filled(data_get($item, 'url')))
                        <a class="rss-card-title-link" href="{{ data_get($item, 'url') }}" target="_blank" rel="noopener noreferrer">
                            <strong>{{ data_get($item, 'title') }}</strong>
                        </a>
                    @else
                        <strong>{{ data_get($item, 'title') }}</strong>
                    @endif
                    <p>{{ data_get($item, 'excerpt') ?: 'Resumo indisponivel no feed RSS original.' }}</p>
                    <small>{{ data_get($item, 'published_label') }}</small>
                </article>
            @empty
                @foreach($rssFeeds->filter(fn ($feed) => filled(data_get($feed, 'url')) && data_get($feed, 'url') !== '#') as $feed)
                    <article class="rss-card rss-news-card">
                        <span>{{ data_get($feed, 'category') ?: 'RSS' }}</span>
                        <strong>{{ data_get($feed, 'name') }}</strong>
                        <p>Feed cadastrado. As noticias aparecem aqui quando a fonte RSS responder com itens validos.</p>
                        <small>Fonte RSS configurada para atualizacao editorial</small>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>

    <section class="local-sponsors-section" aria-label="Classificados de Veiculos">
        <div class="vehicle-classifieds-menu" aria-label="Abrir Classificados de Veiculos" @if($vehicleImageUrl) style="background-image: url('{{ $vehicleImageUrl }}')" @endif>
            <span>{{ data_get($vehicleBlock, 'label') ?: 'Classificados de Veiculos' }}</span>
            <div class="vehicle-type-menu">
                @foreach(\App\Models\Setting::vehicleTypeOptions() as $typeKey => $typeLabel)
                    <a class="vehicle-type-option" href="{{ route('vehicles.index', ['vehicle_type' => $typeKey]) }}">{{ $typeLabel }}</a>
                @endforeach
            </div>
            <x-vehicle-brand-logos type="car" />
        </div>

        <div class="local-sponsors-grid">
            @foreach(range(1, 12) as $sponsorIndex)
                <a class="local-sponsor-banner" href="#" aria-label="Espaço para patrocinador local {{ $sponsorIndex }}">
                    <span class="sr-only">Espaço para patrocinador local {{ $sponsorIndex }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endsection


