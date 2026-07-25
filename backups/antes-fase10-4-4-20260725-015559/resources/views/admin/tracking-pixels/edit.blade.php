@extends('layouts.app', ['title' => 'Pixels - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Pixels</h1>
        <p>Cadastre os IDs do Meta Pixel e do TikTok Pixel para medir campanhas, audiência e conversões.</p>
    </section>

    <section class="settings-panel" aria-label="Configuração de pixels">
        <form method="post" action="{{ route('admin.tracking-pixels.update') }}" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <div class="ad-settings-panel">
                <div>
                    <p class="eyebrow">Meta</p>
                    <h2>Facebook e Instagram</h2>
                    <p>Informe apenas o ID do Pixel. O código será carregado automaticamente no site.</p>
                </div>

                <label>
                    ID do Meta Pixel
                    <input
                        name="meta_pixel_id"
                        value="{{ old('meta_pixel_id', $trackingPixels['meta_pixel_id'] ?? '') }}"
                        placeholder="000000000000000"
                        inputmode="text"
                    >
                </label>
            </div>

            <div class="ad-settings-panel">
                <div>
                    <p class="eyebrow">TikTok</p>
                    <h2>TikTok Pixel</h2>
                    <p>Informe apenas o ID do Pixel do TikTok para ativar o rastreamento padrão.</p>
                </div>

                <label>
                    ID do TikTok Pixel
                    <input
                        name="tiktok_pixel_id"
                        value="{{ old('tiktok_pixel_id', $trackingPixels['tiktok_pixel_id'] ?? '') }}"
                        placeholder="C0000000000000000000"
                        inputmode="text"
                    >
                </label>
            </div>

            <button class="secondary-action" type="submit">Salvar pixels</button>
        </form>
    </section>
@endsection
