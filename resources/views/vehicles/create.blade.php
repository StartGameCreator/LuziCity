@extends('layouts.app', ['title' => 'Anunciar veiculo - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Classificados de Veiculos</p>
        <h1>Anunciar veiculo</h1>
        <p>Preencha os dados principais, fotografe pelo smartphone, envie imagens da galeria e publique seu anuncio.</p>
    </section>

    <section class="vehicle-form-shell">
        <div class="vehicle-limit-note">
            @if($settings['limit_enabled'])
                <strong>Limite ativo</strong>
                <span>Voce usou {{ $activeListingsCount }} de {{ $settings['max_active_listings'] }} anuncios disponiveis.</span>
            @else
                <strong>Anuncios liberados</strong>
                <span>Neste momento nao ha limite de anuncios por usuario.</span>
            @endif
        </div>

        <form method="post" action="{{ route('vehicles.store') }}" enctype="multipart/form-data" class="admin-form vehicle-listing-form">
            @csrf

            <label>
                Titulo do anuncio
                <input id="vehicle_title" name="title" value="{{ old('title') }}" required placeholder="Honda Civic EXL 2020 completo">
            </label>

            <div class="form-grid">
                <label>
                    Tipo de veiculo
                    <select name="vehicle_type" required>
                        @foreach($vehicleTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" @selected(old('vehicle_type', 'car') === $typeKey)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Marca
                    <input name="brand" value="{{ old('brand') }}" required placeholder="Honda">
                </label>
                <label>
                    Modelo
                    <input name="model" value="{{ old('model') }}" required placeholder="Civic">
                </label>
                <label>
                    Ano
                    <input type="number" name="year" min="1950" max="{{ date('Y') + 1 }}" value="{{ old('year') }}" required>
                </label>
                <label>
                    Preco
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" placeholder="89000">
                </label>
                <label>
                    Quilometragem
                    <input type="number" min="0" name="mileage" value="{{ old('mileage') }}" placeholder="52000">
                </label>
                <label>
                    Combustivel
                    <select name="fuel">
                        <option value="">Selecione</option>
                        @foreach(['Flex', 'Gasolina', 'Etanol', 'Diesel', 'Eletrico', 'Hibrido'] as $fuel)
                            <option value="{{ $fuel }}" @selected(old('fuel') === $fuel)>{{ $fuel }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Cambio
                    <select name="transmission">
                        <option value="">Selecione</option>
                        @foreach(['Manual', 'Automatico', 'CVT', 'Automatizado'] as $transmission)
                            <option value="{{ $transmission }}" @selected(old('transmission') === $transmission)>{{ $transmission }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Cidade
                    <input name="city" value="{{ old('city') }}" required placeholder="Luziania">
                </label>
                <label>
                    UF
                    <input name="state" value="{{ old('state') }}" maxlength="2" required placeholder="GO">
                </label>
                <label>
                    Telefone
                    <input name="phone" value="{{ old('phone') }}" inputmode="tel" placeholder="(61) 00000-0000">
                </label>
                <label>
                    WhatsApp
                    <input name="whatsapp" value="{{ old('whatsapp') }}" inputmode="tel" placeholder="(61) 00000-0000">
                </label>
            </div>

            <label>
                Fotos do veiculo
                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple>
            </label>

            <section class="ad-settings-panel" aria-label="Video do anuncio">
                <p class="eyebrow">Video do anuncio</p>
                <h2>YouTube ou Facebook</h2>
                <p>Cole aqui o iframe do video do veiculo. Pode ser horizontal ou vertical.</p>

                <div class="social-settings-grid">
                    <label>
                        Plataforma
                        <select name="video_platform">
                            <option value="">Sem video</option>
                            <option value="youtube" @selected(old('video_platform') === 'youtube')>YouTube</option>
                            <option value="facebook" @selected(old('video_platform') === 'facebook')>Facebook</option>
                        </select>
                    </label>
                    <label>
                        Formato
                        <select name="video_orientation">
                            <option value="landscape" @selected(old('video_orientation', 'landscape') === 'landscape')>Horizontal</option>
                            <option value="portrait" @selected(old('video_orientation') === 'portrait')>Vertical</option>
                        </select>
                    </label>
                </div>

                <label>
                    Codigo iframe
                    <textarea name="video_embed_code" rows="5" placeholder="<iframe src=&quot;...&quot; allowfullscreen></iframe>">{{ old('video_embed_code') }}</textarea>
                </label>
            </section>

            <section class="ad-settings-panel" aria-label="Copy do anuncio com IA">
                <p class="eyebrow">Copy com IA</p>
                <h2>Assistente para vender melhor</h2>
                <p>Descreva os diferenciais do veiculo e gere uma copy inicial para revisar antes de publicar.</p>

                <div class="ai-toolbar" data-ai-toolbar>
                    <label>
                        IA para copy
                        <select data-ai-provider>
                            @foreach(['chatgpt' => 'ChatGPT', 'gemini' => 'Gemini', 'copilot' => 'Copilot'] as $value => $label)
                                <option value="{{ $value }}" @selected((\App\Models\Setting::aiSettings()['provider'] ?? 'chatgpt') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="secondary-action" type="button" data-ai-generate data-ai-context="vehicle_ad" data-ai-title="vehicle_title" data-ai-target="vehicle_description" data-ai-brief="vehicle_ai_brief">Gerar copy com IA</button>
                    <div class="ai-external-actions" aria-label="Abrir IA logada no navegador">
                        <button class="secondary-action" type="button" data-ai-open="chatgpt" data-ai-context="vehicle_ad" data-ai-title="vehicle_title" data-ai-brief="vehicle_ai_brief">Abrir ChatGPT logado</button>
                        <button class="secondary-action" type="button" data-ai-open="gemini" data-ai-context="vehicle_ad" data-ai-title="vehicle_title" data-ai-brief="vehicle_ai_brief">Abrir Gemini logado</button>
                        <button class="secondary-action" type="button" data-ai-open="copilot" data-ai-context="vehicle_ad" data-ai-title="vehicle_title" data-ai-brief="vehicle_ai_brief">Abrir Copilot logado</button>
                    </div>
                </div>

                <label>
                    Informacoes para IA
                    <textarea id="vehicle_ai_brief" rows="5" placeholder="Ex: unico dono, revisoes em dia, pneus novos, documento ok, aceita troca, ideal para familia..."></textarea>
                </label>
            </section>

            <label>
                Descricao
                <textarea id="vehicle_description" name="description" rows="7" placeholder="Conte o estado do veiculo, opcionais, documentacao e informacoes importantes.">{{ old('description') }}</textarea>
            </label>

            <button class="primary-action" type="submit">Publicar anuncio</button>
        </form>
    </section>
@endsection