@extends('layouts.app', ['title' => 'Anunciar imovel - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Imoveis</p>
        <h1>Anunciar imovel</h1>
        <p>Cadastre imoveis para compra, venda ou aluguel com fotos, localizacao, caracteristicas, video e contato direto.</p>
    </section>

    <section class="vehicle-form-shell">
        <form method="post" action="{{ route('real-estate.store') }}" enctype="multipart/form-data" class="admin-form vehicle-listing-form">
            @csrf

            <label>
                Titulo do anuncio
                <input id="property_title" name="title" value="{{ old('title') }}" required placeholder="Casa com 3 quartos no centro">
            </label>

            <div class="form-grid">
                <label>
                    Finalidade
                    <select name="purpose" required>
                        @foreach($purposeLabels as $key => $label)
                            <option value="{{ $key }}" @selected(old('purpose', 'sale') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Tipo do imovel
                    <select name="property_type" required>
                        @foreach($propertyTypeLabels as $key => $label)
                            <option value="{{ $key }}" @selected(old('property_type', 'house') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Preco
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" placeholder="350000">
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
                    Bairro
                    <input name="neighborhood" value="{{ old('neighborhood') }}" placeholder="Centro">
                </label>
                <label>
                    Endereco
                    <input name="address" value="{{ old('address') }}" placeholder="Rua, condominio ou referencia">
                </label>
                <label>
                    Quartos
                    <input type="number" min="0" name="bedrooms" value="{{ old('bedrooms') }}">
                </label>
                <label>
                    Banheiros
                    <input type="number" min="0" name="bathrooms" value="{{ old('bathrooms') }}">
                </label>
                <label>
                    Vagas
                    <input type="number" min="0" name="parking_spaces" value="{{ old('parking_spaces') }}">
                </label>
                <label>
                    Area em m2
                    <input type="number" min="0" name="area_m2" value="{{ old('area_m2') }}">
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
                Fotos do imovel
                <input type="file" name="photos[]" accept="image/*" capture="environment" multiple>
            </label>

            <section class="ad-settings-panel" aria-label="Video do anuncio de imovel">
                <p class="eyebrow">Video do anuncio</p>
                <h2>YouTube ou Facebook</h2>
                <p>Cole aqui o iframe do video do imovel. Pode ser horizontal ou vertical.</p>

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

            <section class="ad-settings-panel" aria-label="Copy do anuncio de imovel com IA">
                <p class="eyebrow">Copy com IA</p>
                <h2>Assistente para vender ou alugar melhor</h2>
                <p>Descreva os diferenciais do imovel e gere uma copy inicial para revisar antes de publicar.</p>

                <div class="ai-toolbar" data-ai-toolbar>
                    <label>
                        IA para copy
                        <select data-ai-provider>
                            @foreach(['chatgpt' => 'ChatGPT', 'gemini' => 'Gemini', 'copilot' => 'Copilot'] as $value => $label)
                                <option value="{{ $value }}" @selected((\App\Models\Setting::aiSettings()['provider'] ?? 'chatgpt') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="secondary-action" type="button" data-ai-generate data-ai-context="real_estate_ad" data-ai-title="property_title" data-ai-target="property_description" data-ai-brief="property_ai_brief">Gerar copy com IA</button>
                    <div class="ai-external-actions" aria-label="Abrir IA logada no navegador">
                        <button class="secondary-action" type="button" data-ai-open="chatgpt" data-ai-context="real_estate_ad" data-ai-title="property_title" data-ai-brief="property_ai_brief">Abrir ChatGPT logado</button>
                        <button class="secondary-action" type="button" data-ai-open="gemini" data-ai-context="real_estate_ad" data-ai-title="property_title" data-ai-brief="property_ai_brief">Abrir Gemini logado</button>
                        <button class="secondary-action" type="button" data-ai-open="copilot" data-ai-context="real_estate_ad" data-ai-title="property_title" data-ai-brief="property_ai_brief">Abrir Copilot logado</button>
                    </div>
                </div>

                <label>
                    Informacoes para IA
                    <textarea id="property_ai_brief" rows="5" placeholder="Ex: perto de escolas, aceita financiamento, condominio fechado, quintal amplo, nascente, area gourmet..."></textarea>
                </label>
            </section>

            <label>
                Descricao
                <textarea id="property_description" name="description" rows="7" placeholder="Descreva acabamento, localizacao, documentacao, condominio e detalhes importantes.">{{ old('description') }}</textarea>
            </label>

            <button class="primary-action" type="submit">Publicar imovel</button>
        </form>
    </section>
@endsection