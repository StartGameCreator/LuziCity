@extends('layouts.app', ['title' => 'RSS - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>RSS</h1>
        <p>Cadastre feeds RSS para exibir na home abaixo do último banner de publicidade.</p>
    </section>

    <section class="settings-panel" aria-label="Novo feed RSS">
        <form method="post" action="{{ route('admin.rss-feeds.refresh') }}" class="admin-form rss-refresh-form">
            @csrf
            <label>
                Noticias por fonte
                <input type="number" name="limit" min="1" max="30" value="12">
            </label>
            <button class="secondary-action" type="submit">Importar e atualizar RSS agora</button>
        </form>

        <form method="post" action="{{ route('admin.rss-feeds.store') }}" class="admin-form social-settings-form">
            @csrf

            <div class="social-settings-grid">
                <label>
                    Nome
                    <input name="name" required placeholder="Ex: Agência Brasil">
                </label>
                <label>
                    Categoria
                    <input name="category" placeholder="Ex: Nacional">
                </label>
                <label>
                    Link RSS
                    <input name="url" required placeholder="https://.../feed">
                </label>
                @include('admin.rss-feeds._agency_fields', ['feed' => null])
                <label>
                    Ordem
                    <input type="number" name="sort_order" min="0" value="0">
                </label>
            </div>

            <label class="inline-check">
                <input type="checkbox" name="is_active" value="1" checked>
                Exibir na home
            </label>

            <button class="secondary-action" type="submit">Salvar RSS</button>
        </form>
    </section>

    <section class="category-admin-list" aria-label="Feeds RSS cadastrados">
        @foreach($feeds as $feed)
            @php($diagnostic = $diagnostics[$feed->id] ?? ['ok' => null, 'message' => 'Clique em "Importar e atualizar RSS agora" para conferir se esta fonte esta respondendo.'])
            <article class="settings-panel category-admin-card">
                <div class="rss-diagnostic @if($diagnostic['ok'] === true) is-ok @elseif($diagnostic['ok'] === false) is-error @endif">
                    <strong>
                        @if($diagnostic['ok'] === true)
                            RSS conectado
                        @elseif($diagnostic['ok'] === false)
                            RSS com pendencia
                        @else
                            RSS ainda nao testado
                        @endif
                    </strong>
                    <span>{{ $diagnostic['message'] }}</span>
                </div>

                <form method="post" action="{{ route('admin.rss-feeds.update', $feed) }}" class="admin-form social-settings-form">
                    @csrf
                    @method('put')

                    <div class="social-settings-grid">
                        <label>
                            Nome
                            <input name="name" value="{{ $feed->name }}" required>
                        </label>
                        <label>
                            Categoria
                            <input name="category" value="{{ $feed->category }}">
                        </label>
                        <label>
                            Link RSS
                            <input name="url" value="{{ $feed->url }}" required>
                        </label>
                        @include('admin.rss-feeds._agency_fields', ['feed' => $feed])
                        <label>
                            Ordem
                            <input type="number" name="sort_order" min="0" value="{{ $feed->sort_order }}">
                        </label>
                    </div>

                    <label class="inline-check">
                        <input type="checkbox" name="is_active" value="1" @checked($feed->is_active)>
                        Exibir na home
                    </label>

                    <button class="secondary-action" type="submit">Atualizar RSS</button>
                </form>
            </article>
        @endforeach
    </section>
@endsection
