@extends('layouts.app', ['title' => 'Banners Rotativos - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Banners rotativos</h1>
        <p>Cadastre iframes do YouTube, Reels do Facebook e vídeos horizontais para os classificados de veículos.</p>
    </section>

    <section class="settings-panel" aria-label="Transmissao especial ao vivo da home">
        <form method="post" action="{{ route('admin.media-banners.home-live.update') }}" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <div class="radio-panel-head">
                <div>
                    <p class="eyebrow">Home</p>
                    <h2>Transmissao especial ao vivo</h2>
                    <p>Bloco oculto por padrao para coberturas de shows, eventos e transmissoes externas via TikTok ou DLive.</p>
                </div>
            </div>

            <div class="social-settings-grid">
                <label>
                    Plataforma
                    <select name="provider">
                        <option value="tiktok" @selected(data_get($homeLiveBroadcast, 'provider') === 'tiktok')>TikTok</option>
                        <option value="dlive" @selected(data_get($homeLiveBroadcast, 'provider') === 'dlive')>DLive</option>
                    </select>
                </label>

                <label>
                    Formato
                    <select name="orientation">
                        <option value="portrait" @selected(data_get($homeLiveBroadcast, 'orientation') === 'portrait')>Vertical centralizado</option>
                        <option value="landscape" @selected(data_get($homeLiveBroadcast, 'orientation') === 'landscape')>Horizontal lado a lado</option>
                    </select>
                </label>

                <label>
                    Titulo interno
                    <input name="title" value="{{ data_get($homeLiveBroadcast, 'title') }}" placeholder="Cobertura especial ao vivo">
                </label>
            </div>

            <label>
                Codigo iframe
                <textarea name="embed_code" rows="6" placeholder='<iframe src="..." allowfullscreen></iframe>'>{{ data_get($homeLiveBroadcast, 'embed_code') }}</textarea>
            </label>

            <label>
                Link externo opcional
                <input name="external_url" value="{{ data_get($homeLiveBroadcast, 'external_url') }}" placeholder="https://...">
            </label>

            <label class="inline-check">
                <input type="checkbox" name="enabled" value="1" @checked(data_get($homeLiveBroadcast, 'enabled'))>
                Exibir transmissao na home
            </label>

            <button class="secondary-action" type="submit">Salvar transmissao</button>
        </form>
    </section>

    <section class="settings-panel" aria-label="Novo banner rotativo">
        <form method="post" action="{{ route('admin.media-banners.store') }}" class="admin-form social-settings-form">
            @csrf

            <div class="social-settings-grid">
                <label>
                    Título
                    <input name="title" required placeholder="Ex: Entrevista da semana">
                </label>
                <label>
                    Tipo
                    <select name="type">
                        @foreach($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Ordem
                    <input type="number" name="sort_order" min="0" value="0">
                </label>
            </div>

            <label>
                Código iframe
                <textarea name="embed_code" rows="6" placeholder='<iframe src="..." allowfullscreen></iframe>'></textarea>
            </label>

            <label class="inline-check">
                <input type="checkbox" name="is_active" value="1" checked>
                Exibir no site
            </label>

            <button class="secondary-action" type="submit">Salvar banner</button>
        </form>
    </section>

    <section class="category-admin-list" aria-label="Banners cadastrados">
        @foreach($banners as $banner)
            <article class="settings-panel category-admin-card">
                <form method="post" action="{{ route('admin.media-banners.update', $banner) }}" class="admin-form social-settings-form">
                    @csrf
                    @method('put')

                    <div class="social-settings-grid">
                        <label>
                            Título
                            <input name="title" value="{{ $banner->title }}" required>
                        </label>
                        <label>
                            Tipo
                            <select name="type">
                                @foreach($typeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($banner->type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Ordem
                            <input type="number" name="sort_order" min="0" value="{{ $banner->sort_order }}">
                        </label>
                    </div>

                    <label>
                        Código iframe
                        <textarea name="embed_code" rows="6">{{ $banner->embed_code }}</textarea>
                    </label>

                    <label class="inline-check">
                        <input type="checkbox" name="is_active" value="1" @checked($banner->is_active)>
                        Exibir no site
                    </label>

                    <button class="secondary-action" type="submit">Atualizar banner</button>
                </form>
            </article>
        @endforeach
    </section>
@endsection
