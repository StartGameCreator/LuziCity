@extends('layouts.app', ['title' => 'IA - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administracao</p>
        <h1>Inteligencia Artificial</h1>
        <p>Cadastre as chaves do ChatGPT, Gemini e Copilot para liberar a geracao real de textos no editor de noticias, veiculos, imoveis e Quem Somos.</p>
    </section>

    <section class="settings-panel" aria-label="Configuracao das chaves de IA">
        @if(session('ai_test'))
            @php($test = session('ai_test'))
            <div class="notice {{ $test['ok'] ? '' : 'notice-error' }}" role="status">
                <strong>{{ $test['ok'] ? 'Conexao de IA funcionando' : 'A IA ainda nao respondeu corretamente' }}</strong>
                <p>{{ strtoupper($test['provider']) }}: {{ $test['message'] }}</p>
                @if(filled($test['text'] ?? ''))
                    <p>{{ Str::limit($test['text'], 220) }}</p>
                @endif
            </div>
        @endif

        <section class="ad-settings-panel" aria-label="Teste das chaves de IA">
            <p class="eyebrow">Diagnostico</p>
            <h2>Testar chaves cadastradas</h2>
            <p>Use estes botoes depois de salvar. O teste confirma se a chave foi aceita pela API e se o provedor esta respondendo.</p>

            <div class="ai-test-grid">
                @foreach([
                    'chatgpt' => ['label' => 'Testar ChatGPT', 'status' => $keyStatus['chatgpt']['label'] ?? 'sem chave cadastrada', 'enabled' => $keyStatus['chatgpt']['saved'] ?? false],
                    'gemini' => ['label' => 'Testar Gemini', 'status' => $keyStatus['gemini']['label'] ?? 'sem chave cadastrada', 'enabled' => $keyStatus['gemini']['saved'] ?? false],
                    'copilot' => ['label' => 'Testar Copilot', 'status' => $keyStatus['copilot']['label'] ?? 'sem chave cadastrada', 'enabled' => ($keyStatus['copilot']['saved'] ?? false) && ($keyStatus['copilot_endpoint'] ?? false)],
                ] as $provider => $item)
                    <form method="post" action="{{ route('admin.ai-settings.test') }}" class="ai-test-card">
                        @csrf
                        <input type="hidden" name="provider" value="{{ $provider }}">
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['status'] }}</span>
                        @if($provider === 'copilot' && !($keyStatus['copilot_endpoint'] ?? false))
                            <small>Endpoint ainda nao cadastrado.</small>
                        @endif
                        <button class="secondary-action" type="submit" @disabled(! $item['enabled'])>Testar conexao</button>
                    </form>
                @endforeach
            </div>
        </section>

        <form method="post" action="{{ route('admin.ai-settings.update') }}" enctype="multipart/form-data" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <section class="ad-settings-panel" aria-label="IA padrao">
                <p class="eyebrow">Padrao do site</p>
                <h2>Provedor principal</h2>
                <p>Escolha qual assistente sera usado primeiro pelos botoes de redacao automatica.</p>

                <label>
                    IA padrao
                    <select name="ai_provider">
                        @foreach(['chatgpt' => 'ChatGPT / OpenAI', 'gemini' => 'Gemini / Google', 'copilot' => 'Copilot / Microsoft'] as $value => $label)
                            <option value="{{ $value }}" @selected(($aiSettings['provider'] ?? 'chatgpt') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </section>

            <section class="ad-settings-panel" aria-label="Chave do ChatGPT">
                <p class="eyebrow">ChatGPT</p>
                <h2>OpenAI</h2>
                <p>Status: <strong>{{ $keyStatus['chatgpt']['label'] }}</strong>. Se deixar em branco, a chave atual sera mantida.</p>

                <div class="social-settings-grid">
                    <label>
                        Colar chave OpenAI
                        <input type="password" name="openai_api_key" autocomplete="off" placeholder="sk-...">
                    </label>
                    <label>
                        Subir arquivo da chave
                        <input type="file" name="openai_key_file" accept=".txt,.json,.env,application/json,text/plain">
                    </label>
                    <label>
                        Modelo ChatGPT
                        <input name="chatgpt_model" value="{{ old('chatgpt_model', $aiSettings['chatgpt_model'] ?? 'gpt-4o-mini') }}" placeholder="gpt-4o-mini">
                    </label>
                </div>
            </section>

            <section class="ad-settings-panel" aria-label="Chave do Gemini">
                <p class="eyebrow">Gemini</p>
                <h2>Google AI</h2>
                <p>Status: <strong>{{ $keyStatus['gemini']['label'] }}</strong>. Aceita arquivo TXT, JSON ou ENV.</p>

                <div class="social-settings-grid">
                    <label>
                        Colar chave Gemini
                        <input type="password" name="gemini_api_key" autocomplete="off" placeholder="AIza...">
                    </label>
                    <label>
                        Subir arquivo da chave
                        <input type="file" name="gemini_key_file" accept=".txt,.json,.env,application/json,text/plain">
                    </label>
                    <label>
                        Modelo Gemini
                        <input name="gemini_model" value="{{ old('gemini_model', $aiSettings['gemini_model'] ?? 'gemini-1.5-flash') }}" placeholder="gemini-1.5-flash">
                    </label>
                </div>
            </section>

            <section class="ad-settings-panel" aria-label="Chave do Copilot">
                <p class="eyebrow">Copilot</p>
                <h2>Microsoft / endpoint compativel</h2>
                <p>Status: <strong>{{ $keyStatus['copilot']['label'] }}</strong>. O Copilot precisa de chave e endpoint compativel com API.</p>

                <div class="social-settings-grid">
                    <label>
                        Colar chave Copilot
                        <input type="password" name="copilot_api_key" autocomplete="off" placeholder="token do provedor">
                    </label>
                    <label>
                        Subir arquivo da chave
                        <input type="file" name="copilot_key_file" accept=".txt,.json,.env,application/json,text/plain">
                    </label>
                    <label>
                        Endpoint Copilot
                        <input type="url" name="copilot_endpoint" value="{{ old('copilot_endpoint', $aiSettings['copilot_endpoint'] ?? '') }}" placeholder="https://...">
                    </label>
                </div>
            </section>

            <button class="secondary-action" type="submit">Salvar chaves de IA</button>
        </form>
    </section>
@endsection
