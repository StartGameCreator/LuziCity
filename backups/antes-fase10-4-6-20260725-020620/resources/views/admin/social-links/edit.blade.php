@extends('layouts.app', ['title' => 'Links do Site - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Links do site</h1>
        <p>Configure o link da loja e os links oficiais exibidos no topo da Luzicity.</p>
    </section>

    <section class="settings-panel" aria-label="Configuração dos links do site">
        <form method="post" action="{{ route('admin.social-links.update') }}" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <label>
                <span class="settings-label">
                    <span class="settings-icon" aria-hidden="true">
                        <x-app-icon name="store" />
                    </span>
                    Link da Loja
                </span>
                <input
                    type="url"
                    name="shop_url"
                    value="{{ old('shop_url', $shopUrl !== '#' ? $shopUrl : '') }}"
                    placeholder="https://..."
                >
            </label>

            <label>
                <span class="settings-label">
                    <span class="settings-icon" aria-hidden="true">
                        <x-app-icon name="store" />
                    </span>
                    Link do Comercio Local
                </span>
                <input
                    type="url"
                    name="local_commerce_url"
                    value="{{ old('local_commerce_url', $localCommerceUrl !== '#' ? $localCommerceUrl : '') }}"
                    placeholder="https://..."
                >
            </label>

            <div class="social-settings-grid">
                @foreach($socialLinks as $key => $social)
                    <label>
                        <span class="settings-label">
                            <span class="social-icon" aria-hidden="true">
                                <x-social-icon :provider="$key" />
                            </span>
                            {{ $social['label'] }}
                        </span>
                        <input
                            type="url"
                            name="links[{{ $key }}]"
                            value="{{ old("links.$key", $social['url'] !== '#' ? $social['url'] : '') }}"
                            placeholder="https://..."
                        >
                    </label>
                @endforeach
            </div>

            <div class="ad-settings-panel">
                <div>
                    <p class="eyebrow">Google Ads</p>
                    <h2>Publicidade da home</h2>
                    <p>Preencha o código do editor e os IDs dos slots gerados no Google AdSense.</p>
                </div>

                <label>
                    Código do editor
                    <input
                        name="google_ads_client"
                        value="{{ old('google_ads_client', $googleAds['client'] ?? '') }}"
                        placeholder="ca-pub-0000000000000000"
                    >
                </label>

                <div class="social-settings-grid">
                    @foreach([
                        'home_top' => 'Topo da home',
                        'home_after_hero' => 'Depois da manchete',
                        'home_before_latest' => 'Antes das últimas notícias',
                        'home_after_latest' => 'Depois das últimas notícias',
                        'home_before_topics' => 'Antes das editorias',
                        'home_footer' => 'Fim da home',
                        'radio_hero_1' => 'Rádio topo 1',
                        'radio_hero_2' => 'Rádio topo 2',
                        'radio_hero_3' => 'Rádio topo 3',
                        'radio_hero_4' => 'Rádio topo 4',
                        'vehicles_top' => 'Veículos topo',
                        'vehicles_after_search' => 'Veículos após busca',
                        'vehicles_sidebar' => 'Veículos lateral',
                        'vehicles_footer' => 'Veículos final',
                    ] as $slotKey => $slotLabel)
                        <label>
                            {{ $slotLabel }}
                            <input
                                name="google_ads_slots[{{ $slotKey }}]"
                                value="{{ old("google_ads_slots.$slotKey", data_get($googleAds, "slots.$slotKey", '')) }}"
                                placeholder="0000000000"
                            >
                        </label>
                    @endforeach
                </div>
            </div>

            <button class="secondary-action" type="submit">Salvar links</button>
        </form>
    </section>
@endsection
