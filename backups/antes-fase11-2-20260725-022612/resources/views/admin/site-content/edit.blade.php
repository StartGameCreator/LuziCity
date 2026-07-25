@extends('layouts.app', ['title' => 'Conteudo do Site - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administracao</p>
        <h1>Conteudo do site</h1>
        <p>Edite o texto da pagina Quem somos e configure qual IA sera usada nos campos assistidos.</p>
    </section>

    <section class="settings-panel" aria-label="Editor de conteudo do site">
        <form method="post" action="{{ route('admin.site-content.update') }}" enctype="multipart/form-data" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <div class="ai-toolbar" data-ai-toolbar>
                <label>
                    IA padrao
                    <select name="ai_provider" data-ai-provider>
                        @foreach(['chatgpt' => 'ChatGPT', 'gemini' => 'Gemini', 'copilot' => 'Copilot'] as $value => $label)
                            <option value="{{ $value }}" @selected(($aiSettings['provider'] ?? 'chatgpt') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="secondary-action" type="button" data-ai-generate data-ai-context="about" data-ai-target="about_content" data-ai-brief="about_brief">Gerar redacao com IA</button>
                <div class="ai-external-actions" aria-label="Abrir IA logada no navegador">
                    <button class="secondary-action" type="button" data-ai-open="chatgpt" data-ai-context="about" data-ai-brief="about_brief">Abrir ChatGPT logado</button>
                    <button class="secondary-action" type="button" data-ai-open="gemini" data-ai-context="about" data-ai-brief="about_brief">Abrir Gemini logado</button>
                    <button class="secondary-action" type="button" data-ai-open="copilot" data-ai-context="about" data-ai-brief="about_brief">Abrir Copilot logado</button>
                </div>
            </div>

            <label>
                Briefing para IA
                <textarea id="about_brief" rows="5" placeholder="Escreva em topicos o que a pagina Quem somos deve comunicar."></textarea>
            </label>

            <label>
                Redacao de Quem somos
                <textarea id="about_content" name="about_content" rows="14" required>{{ old('about_content', $aboutContent) }}</textarea>
            </label>

            <section class="ad-settings-panel" aria-label="Banners visuais da home">
                <p class="eyebrow">Imagens da home</p>
                <h2>Blocos principais</h2>
                <p>Troque os banners de Fotos/Eventos, Imoveis e Classificados de Veiculos sem alterar o codigo do site.</p>

                <div class="visual-block-admin-grid">
                    @foreach([
                        'events' => 'Fotos de eventos e festas',
                        'real_estate' => 'Imoveis, feiroes e condicoes especiais',
                        'vehicles' => 'Veiculos, feiroes e campanhas',
                    ] as $blockKey => $blockTitle)
                        @php($block = $visualBlocks[$blockKey] ?? [])
                        @php($image = data_get($block, 'image'))
                        <article class="visual-block-admin-card">
                            <div class="visual-block-admin-preview" @if($image) style="background-image: url('{{ str_starts_with($image, 'http') ? $image : asset($image) }}')" @endif></div>

                            <label>
                                Bloco
                                <input name="visual_blocks[{{ $blockKey }}][label]" value="{{ old("visual_blocks.$blockKey.label", data_get($block, 'label', $blockTitle)) }}">
                            </label>

                            <label>
                                Link de destino
                                <input name="visual_blocks[{{ $blockKey }}][link]" value="{{ old("visual_blocks.$blockKey.link", data_get($block, 'link', '#')) }}" placeholder="https://... ou /pagina">
                            </label>

                            <label>
                                Nova imagem
                                <input type="file" name="visual_blocks[{{ $blockKey }}][image]" accept="image/png,image/jpeg,image/webp">
                            </label>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ad-settings-panel" aria-label="Configuracao das APIs de IA">
                <p class="eyebrow">APIs de IA</p>
                <h2>Chaves e modelos</h2>
                <p>Cadastre as chaves para gerar textos pela API. Se deixar uma chave vazia, a chave salva anteriormente sera mantida.</p>

                <div class="social-settings-grid">
                    <label>
                        Chave OpenAI / ChatGPT
                        <input type="password" name="openai_api_key" autocomplete="off" placeholder="sk-...">
                    </label>
                    <label>
                        Modelo ChatGPT
                        <input name="chatgpt_model" value="{{ old('chatgpt_model', $aiSettings['chatgpt_model'] ?? '') }}" placeholder="gpt-4o-mini">
                    </label>
                    <label>
                        Chave Gemini
                        <input type="password" name="gemini_api_key" autocomplete="off" placeholder="AIza...">
                    </label>
                    <label>
                        Modelo Gemini
                        <input name="gemini_model" value="{{ old('gemini_model', $aiSettings['gemini_model'] ?? '') }}" placeholder="gemini-1.5-flash">
                    </label>
                    <label>
                        Chave Copilot
                        <input type="password" name="copilot_api_key" autocomplete="off" placeholder="token do provedor">
                    </label>
                    <label>
                        Endpoint Copilot
                        <input type="url" name="copilot_endpoint" value="{{ old('copilot_endpoint', $aiSettings['copilot_endpoint'] ?? '') }}" placeholder="https://...">
                    </label>
                </div>
            </section>

            <button class="secondary-action" type="submit">Salvar conteudo</button>
        </form>
    </section>
@endsection
