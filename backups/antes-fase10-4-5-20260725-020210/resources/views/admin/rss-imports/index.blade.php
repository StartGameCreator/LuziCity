@extends('layouts.app', ['title' => 'Importacao RSS - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administracao</p>
        <h1>Importacao RSS para o banco</h1>
        <p>Importe as noticias dos feeds ativos e mantenha a home mais rapida, estavel e independente de buscas ao vivo.</p>
    </section>

    <section class="system-health-stats" aria-label="Resumo da importacao RSS">
        <article class="settings-panel system-health-stat">
            <span>Noticias importadas</span>
            <strong>{{ $stats['total'] }}</strong>
        </article>
        <article class="settings-panel system-health-stat">
            <span>Visiveis na home</span>
            <strong>{{ $stats['visible'] }}</strong>
        </article>
        <article class="settings-panel system-health-stat">
            <span>Feeds ativos</span>
            <strong>{{ $stats['feeds'] }}</strong>
        </article>
    </section>

    <section class="settings-panel" aria-label="Importar feeds RSS">
        <form method="post" action="{{ route('admin.rss-imports.import') }}" class="admin-form">
            @csrf

            <div class="social-settings-grid">
                <label>
                    Quantidade por feed
                    <input type="number" name="limit" min="1" max="30" value="12">
                </label>
            </div>

            <button class="secondary-action" type="submit">Importar RSS agora</button>
        </form>
    </section>

    <section class="system-health-grid" aria-label="Feeds monitorados">
        @forelse($feeds as $feed)
            <article class="settings-panel system-health-card">
                <div class="system-health-card-head">
                    <span class="system-health-badge">{{ $feed->imported_articles_count }} salvas</span>
                    <h2>{{ $feed->name }}</h2>
                </div>
                <p>{{ $feed->category ?: 'RSS' }}</p>
                <small>{{ $feed->url }}</small>
            </article>
        @empty
            <article class="settings-panel">
                <strong>Nenhum feed real ativo</strong>
                <p>Cadastre feeds em Backend > RSS para iniciar a importacao.</p>
            </article>
        @endforelse
    </section>

    <section class="category-admin-list" aria-label="Ultimas noticias RSS importadas">
        @forelse($articles as $article)
            <article class="settings-panel category-admin-card rss-imported-card">
                <div class="rss-imported-preview">
                    <div class="rss-imported-image-box">
                        @if(filled($article->image_url))
                            <img src="{{ $article->image_url }}" alt="">
                        @else
                            <span>Sem imagem</span>
                        @endif
                    </div>
                    <div>
                        <p class="eyebrow">{{ $article->category }} - {{ $article->source_name }}</p>
                        <h2>{{ $article->title }}</h2>
                        <p>{{ $article->excerpt }}</p>
                        <small>
                            Publicada: {{ optional($article->published_at)->format('d/m/Y H:i') ?: 'sem data' }}
                            |
                            Importada: {{ optional($article->imported_at)->format('d/m/Y H:i') ?: 'sem data' }}
                        </small>
                    </div>
                </div>

                <form method="post" action="{{ route('admin.rss-imports.update', $article) }}" class="admin-form" enctype="multipart/form-data">
                    @csrf
                    @method('put')

                    <div class="social-settings-grid">
                        <label>
                            URL da imagem
                            <input name="image_url" value="{{ $article->image_url }}" placeholder="Cole aqui o link da imagem da materia">
                        </label>

                        <label>
                            Enviar imagem
                            <input type="file" name="image_upload" accept="image/png,image/jpeg,image/webp">
                        </label>
                    </div>

                    <label class="inline-check">
                        <input type="checkbox" name="is_visible" value="1" @checked($article->is_visible)>
                        Exibir na home
                    </label>

                    <label class="inline-check">
                        <input type="checkbox" name="clear_image" value="1">
                        Remover imagem atual
                    </label>

                    <button class="secondary-action" type="submit">Atualizar</button>
                    <a class="secondary-action" href="{{ $article->original_url }}" target="_blank" rel="noopener noreferrer">Abrir fonte</a>
                </form>
            </article>
        @empty
            <article class="settings-panel">
                <strong>Nenhuma noticia importada ainda</strong>
                <p>Clique em Importar RSS agora para salvar noticias no banco.</p>
            </article>
        @endforelse
    </section>
@endsection