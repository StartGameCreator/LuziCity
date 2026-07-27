@extends('layouts.app', [
    'title' => $article->title.' - '.data_get(\App\Models\Setting::siteIdentity(), 'name', 'Luzicity'),
    'meta' => $meta,
])

@section('content')
    <article class="news-detail-shell">
        <header class="news-detail-head">
            @if($article->is_sponsored)
                <p class="eyebrow" aria-label="Identificação de publicidade">{{ $article->sponsor_label ?: 'Conteúdo patrocinado' }} · {{ $article->sponsor?->company_name }}</p>
            @endif
            <p class="eyebrow">{{ $article->category?->name ?: 'Noticia' }}</p>
            <h1>{{ $article->title }}</h1>
            @if($article->excerpt)
                <p>{{ $article->excerpt }}</p>
            @endif
            <div class="story-meta">
                <span>{{ $article->author?->name ?: 'Redacao' }}</span>
                <span>{{ optional($article->published_at)->format('d/m/Y H:i') }}</span>
            </div>
            @if($article->attributionSite())
                <p class="notice">Conteúdo originalmente publicado por <strong>{{ $article->attributionSite()->name }}</strong>.</p>
            @endif
        </header>

        @if($article->cover_image_path)
            <img class="news-detail-cover" src="{{ asset($article->cover_image_path) }}" alt="{{ $article->cover_image_alt ?: $article->title }}">
        @endif

        @if($showAds)
            <x-ad-slot name="home_after_hero" label="Publicidade na noticia" variant="wide" />
        @endif

        <div class="news-detail-body">
            @if($paywall['allowed'])
                {!! nl2br(e($article->body)) !!}
                @if($paywall['protected'] && $paywall['remaining'] !== null)<p class="notice">Você ainda pode ler {{ $paywall['remaining'] }} conteúdo(s) exclusivo(s) neste mês.</p>@endif
            @else
                {!! nl2br(e($paywall['preview'])) !!}
                <section class="settings-panel" aria-label="Conteúdo exclusivo"><p class="eyebrow">Conteúdo exclusivo</p><h2>Continue lendo com uma assinatura</h2><p>Este conteúdo está protegido pelo Clube LuziCity.</p><a class="primary-action" href="{{ route('subscription-plans.index') }}">Conhecer os planos</a></section>
            @endif
        </div>
        <nav class="settings-panel" aria-label="Compartilhar notícia">
            <strong>Compartilhe esta notícia</strong>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:.75rem">
                <a class="secondary-action" data-analytics-share href="https://wa.me/?text={{ urlencode($article->title.' '.request()->url()) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                <a class="secondary-action" data-analytics-share href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a class="secondary-action" data-analytics-share href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode(request()->url()) }}">E-mail</a>
            </div>
        </nav>
    </article>
@endsection
