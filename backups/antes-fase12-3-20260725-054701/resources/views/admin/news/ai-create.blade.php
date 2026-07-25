@extends('layouts.app', ['title' => 'Gerar notícia com IA - LuziCity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Fase 10.1</p>
        <h1>Motor Editorial Inteligente</h1>
        <p>Forneça fatos, contexto e fontes. A IA cria um rascunho estruturado, mas a publicação depende de revisão humana.</p>
    </section>

    <section class="settings-panel">
        <form id="ai-news-form" class="admin-form social-settings-form">
            @csrf

            <div class="social-settings-grid">
                <label>
                    Provedor
                    <select name="provider" required>
                        <option value="chatgpt">ChatGPT</option>
                        <option value="gemini">Gemini</option>
                        <option value="copilot">Copilot</option>
                    </select>
                </label>

                <label>
                    Título de trabalho
                    <input name="working_title" maxlength="180" placeholder="Opcional">
                </label>
            </div>

            <label>
                Briefing obrigatório
                <textarea name="brief" rows="8" minlength="20" maxlength="20000" required
                    placeholder="Descreva fatos confirmados, local, data, pessoas envolvidas, contexto e destaques."></textarea>
            </label>

            <label>
                Texto-fonte
                <textarea name="source_text" rows="8" maxlength="60000"
                    placeholder="Cole a notícia original, release, transcrição ou anotações."></textarea>
            </label>

            <label>
                URL da fonte
                <input type="url" name="source_url" maxlength="2048" placeholder="https://...">
            </label>

            <button class="primary-action" type="submit" data-generate-button>Gerar rascunho com IA</button>
            <p data-ai-status aria-live="polite"></p>
        </form>
    </section>

    <section class="settings-panel" data-result-panel hidden>
        <form method="get" action="{{ route('admin.news.create') }}" data-result-form>
            @foreach([
                'execution_id','title','subtitle','excerpt','body','slug','seo_title',
                'seo_description','category_id','tags','ai_metadata'
            ] as $field)
                <input type="hidden" name="ai_{{ $field }}" data-field="{{ $field }}">
            @endforeach

            <div class="ai-result-grid">
                <article class="content-band">
                    <p class="eyebrow">Prévia</p>
                    <h2 data-preview="title"></h2>
                    <h3 data-preview="subtitle"></h3>
                    <p data-preview="excerpt"></p>
                    <div data-preview="body" class="ai-body-preview"></div>
                </article>

                <aside class="content-band">
                    <h2>Revisão automática</h2>
                    <dl class="ai-review-list">
                        <div><dt>Confiança</dt><dd data-preview="confidence_score"></dd></div>
                        <div><dt>Risco jurídico</dt><dd data-preview="legal_risk"></dd></div>
                        <div><dt>Tempo de leitura</dt><dd data-preview="reading_time_minutes"></dd></div>
                        <div><dt>Categoria sugerida</dt><dd data-preview="category"></dd></div>
                        <div><dt>Tags</dt><dd data-preview="tags"></dd></div>
                        <div><dt>Fontes</dt><dd data-preview="sources"></dd></div>
                    </dl>

                    <h3>Pontos para revisão humana</h3>
                    <ul data-preview="review_notes"></ul>

                    <button class="primary-action" type="submit">Levar para o editor</button>
                </aside>
            </div>
        </form>
    </section>

    @if(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']) && $profile)
        <section class="settings-panel">
            <details>
                <summary><strong>Memória Editorial do LuziCity</strong></summary>

                <form method="post" action="{{ route('admin.news.ai.profile.update') }}" class="admin-form">
                    @csrf
                    @method('put')

                    <label>Tom editorial
                        <input name="tone" value="{{ $profile->tone }}" required maxlength="180">
                    </label>

                    <div class="social-settings-grid">
                        <label>Máximo do título
                            <input type="number" name="max_title_length" min="30" max="180" value="{{ $profile->max_title_length }}" required>
                        </label>
                        <label>Máximo do resumo
                            <input type="number" name="max_excerpt_length" min="80" max="600" value="{{ $profile->max_excerpt_length }}" required>
                        </label>
                    </div>

                    <label class="inline-check">
                        <input type="checkbox" name="require_source_attribution" value="1" @checked($profile->require_source_attribution)>
                        Exigir atribuição da fonte
                    </label>

                    <label class="inline-check">
                        <input type="checkbox" name="avoid_sensationalism" value="1" @checked($profile->avoid_sensationalism)>
                        Evitar sensacionalismo
                    </label>

                    <label>Regras editoriais
                        <textarea name="editorial_rules" rows="12" required>{{ $profile->editorial_rules }}</textarea>
                    </label>

                    <button class="secondary-action" type="submit">Salvar memória editorial</button>
                </form>
            </details>
        </section>
    @endif

    <style>
        .ai-result-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,1fr);gap:1rem}
        .ai-body-preview{white-space:pre-wrap;line-height:1.65}
        .ai-review-list{display:grid;gap:.75rem}
        .ai-review-list div{display:grid;grid-template-columns:1fr auto;gap:1rem;border-bottom:1px solid var(--border);padding-bottom:.5rem}
        @media(max-width:900px){.ai-result-grid{grid-template-columns:1fr}}
    </style>

    <script>
        (() => {
            const form = document.getElementById('ai-news-form');
            const button = document.querySelector('[data-generate-button]');
            const status = document.querySelector('[data-ai-status]');
            const panel = document.querySelector('[data-result-panel]');
            const resultForm = document.querySelector('[data-result-form]');

            if (!form || !button || !status || !panel || !resultForm) return;

            const setPreview = (name, value) => {
                const node = document.querySelector(`[data-preview="${name}"]`);
                if (!node) return;

                if (name === 'review_notes') {
                    node.innerHTML = '';
                    (Array.isArray(value) ? value : []).forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        node.appendChild(li);
                    });
                    return;
                }

                if (Array.isArray(value)) {
                    node.textContent = value.join(', ');
                    return;
                }

                if (name === 'reading_time_minutes') {
                    node.textContent = `${value || 1} min`;
                    return;
                }

                if (name === 'confidence_score') {
                    node.textContent = `${value ?? 0}/100`;
                    return;
                }

                node.textContent = value ?? '—';
            };

            form.addEventListener('submit', async event => {
                event.preventDefault();
                button.disabled = true;
                status.textContent = 'Gerando e analisando o rascunho...';

                try {
                    const response = await fetch('{{ route('admin.news.ai.generate') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.ok) {
                        throw new Error(payload.message || 'A geração não foi concluída.');
                    }

                    const article = payload.article;
                    [
                        'title','subtitle','excerpt','body','category','tags','sources',
                        'confidence_score','review_notes','legal_risk','reading_time_minutes'
                    ].forEach(name => setPreview(name, article[name]));

                    const metadata = {
                        confidence_score: article.confidence_score,
                        sources: article.sources || [],
                        review_notes: article.review_notes || [],
                        legal_risk: article.legal_risk,
                        reading_time_minutes: article.reading_time_minutes,
                        provider: article._provider || null,
                    };

                    const values = {
                        execution_id: article.execution_id || '',
                        title: article.title || '',
                        subtitle: article.subtitle || '',
                        excerpt: article.excerpt || '',
                        body: article.body || '',
                        slug: article.slug || '',
                        seo_title: article.seo_title || '',
                        seo_description: article.seo_description || '',
                        category_id: article.category_id || '',
                        tags: JSON.stringify(article.tags || []),
                        ai_metadata: JSON.stringify(metadata),
                    };

                    Object.entries(values).forEach(([key, value]) => {
                        const input = resultForm.querySelector(`[data-field="${key}"]`);
                        if (input) input.value = value;
                    });

                    panel.hidden = false;
                    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    status.textContent = 'Rascunho gerado. Revise antes de levar ao editor.';
                } catch (error) {
                    console.error('[LuziCity AI News]', error);
                    status.textContent = error.message || 'Falha ao gerar notícia.';
                } finally {
                    button.disabled = false;
                }
            });
        })();
    </script>
@endsection
