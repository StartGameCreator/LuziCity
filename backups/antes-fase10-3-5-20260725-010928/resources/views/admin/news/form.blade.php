@extends('layouts.app', ['title' => ($article->exists ? 'Editar noticia' : 'Nova noticia').' - Luzicity'])

@section('content')
    @php($aiPrefill = $aiPrefill ?? [])
    <section class="content-band">
        <p class="eyebrow">Administracao</p>
        <h1>{{ $article->exists ? 'Editar noticia' : 'Nova noticia' }}</h1>
        <p>Informe o que esta sendo postado e use IA para produzir chamada, resumo e redacao inicial revisavel.</p>
    </section>

    <section class="settings-panel" aria-label="Editor de noticia">
        <form method="post" action="{{ $article->exists ? route('admin.news.update', $article) : route('admin.news.store') }}" enctype="multipart/form-data" class="admin-form social-settings-form">
            @csrf
            @if($article->exists)
                @method('put')
            @endif

            <div class="social-settings-grid">
                <label>
                    Titulo
                    <input id="news_title" name="title" value="{{ old('title', $article->title ?: ($aiPrefill['title'] ?? null)) }}" required>
                </label>
                <label>
                    Subtítulo
                    <input name="subtitle" value="{{ old('subtitle', $article->subtitle ?: ($aiPrefill['subtitle'] ?? null)) }}" maxlength="240">
                </label>
                <label>
                    Slug
                    <input name="slug" value="{{ old('slug', $article->slug ?: ($aiPrefill['slug'] ?? null)) }}" placeholder="gerado automaticamente">
                </label>
                <label>
                    Editoria
                    <select name="category_id">
                        <option value="">Sem editoria</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $article->category_id ?: ($aiPrefill['category_id'] ?? null)) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Status
                    <select name="status">
                        @foreach(['draft' => 'Rascunho', 'published' => 'Publicado', 'archived' => 'Arquivado'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $article->status ?? 'draft') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Data de publicacao
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}">
                </label>
            </div>

            <label>
                O que estou postando
                <textarea id="news_brief" rows="5" placeholder="Descreva fatos, local, pessoas envolvidas, fontes e pontos principais."></textarea>
            </label>

            <section class="ad-settings-panel" aria-label="Chamada da noticia com IA">
                <p class="eyebrow">Copy da noticia</p>
                <h2>Chamada para a home</h2>
                <p>Use a IA para criar uma chamada curta para cards, resumos e destaque da noticia.</p>

                <div class="ai-toolbar" data-ai-toolbar>
                    <label>
                        IA para chamada
                        <select data-ai-provider>
                            @foreach(['chatgpt' => 'ChatGPT', 'gemini' => 'Gemini', 'copilot' => 'Copilot'] as $value => $label)
                                <option value="{{ $value }}" @selected(($aiSettings['provider'] ?? 'chatgpt') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="secondary-action" type="button" data-ai-generate data-ai-context="news_summary" data-ai-title="news_title" data-ai-target="news_excerpt" data-ai-brief="news_brief">Gerar chamada com IA</button>
                    <div class="ai-external-actions" aria-label="Abrir IA logada no navegador">
                        <button class="secondary-action" type="button" data-ai-open="chatgpt" data-ai-context="news_summary" data-ai-title="news_title" data-ai-brief="news_brief">Abrir ChatGPT logado</button>
                        <button class="secondary-action" type="button" data-ai-open="gemini" data-ai-context="news_summary" data-ai-title="news_title" data-ai-brief="news_brief">Abrir Gemini logado</button>
                        <button class="secondary-action" type="button" data-ai-open="copilot" data-ai-context="news_summary" data-ai-title="news_title" data-ai-brief="news_brief">Abrir Copilot logado</button>
                    </div>
                </div>
            </section>

            <label>
                Resumo / chamada
                <textarea id="news_excerpt" name="excerpt" rows="3">{{ old('excerpt', $article->excerpt ?: ($aiPrefill['excerpt'] ?? null)) }}</textarea>
            </label>


            <section class="ad-settings-panel" aria-label="SEO da notícia">
                <p class="eyebrow">SEO</p>
                <h2>Metadados para buscadores</h2>

                <label>
                    Título SEO
                    <input name="seo_title" maxlength="180" value="{{ old('seo_title', $article->seo_title ?: ($aiPrefill['seo_title'] ?? null)) }}">
                </label>

                <label>
                    Descrição SEO
                    <textarea name="seo_description" rows="3" maxlength="320">{{ old('seo_description', $article->seo_description ?: ($aiPrefill['seo_description'] ?? null)) }}</textarea>
                </label>

                <input type="hidden" name="ai_execution_id" value="{{ old('ai_execution_id', $article->ai_execution_id ?: ($aiPrefill['ai_execution_id'] ?? null)) }}">

                @foreach(($aiPrefill['ai_metadata'] ?? []) as $metaKey => $metaValue)
                    @if(is_scalar($metaValue))
                        <input type="hidden" name="ai_metadata[{{ $metaKey }}]" value="{{ $metaValue }}">
                    @endif
                @endforeach
            </section>

            <section class="ad-settings-panel" aria-label="Capa da noticia">
                <p class="eyebrow">Compartilhamento</p>
                <h2>Foto de capa da noticia</h2>
                <p>Esta imagem aparece na pagina da materia e na previa quando a noticia for compartilhada no Facebook, WhatsApp e outras redes.</p>

                <div class="social-settings-grid">
                    <label>
                        Foto de capa
                        <input type="file" name="cover_image" accept="image/png,image/jpeg,image/webp">
                    </label>
                    <label>
                        Texto alternativo da imagem
                        <input name="cover_image_alt" value="{{ old('cover_image_alt', $article->cover_image_alt) }}" placeholder="Descricao curta da foto">
                    </label>
                </div>

                @if($article->cover_image_path)
                    <div class="carousel-image-preview">
                        <img src="{{ asset($article->cover_image_path) }}" alt="{{ $article->cover_image_alt ?: 'Capa atual da noticia' }}">
                    </div>
                @endif
            </section>

            <section class="ad-settings-panel" aria-label="Redacao da noticia com IA">
                <p class="eyebrow">Redacao</p>
                <h2>Texto completo com IA</h2>
                <p>Gere uma redacao inicial para revisar antes de publicar.</p>

                <div class="ai-toolbar" data-ai-toolbar>
                    <label>
                        IA para redacao
                        <select data-ai-provider>
                            @foreach(['chatgpt' => 'ChatGPT', 'gemini' => 'Gemini', 'copilot' => 'Copilot'] as $value => $label)
                                <option value="{{ $value }}" @selected(($aiSettings['provider'] ?? 'chatgpt') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="secondary-action" type="button" data-ai-generate data-ai-context="news" data-ai-title="news_title" data-ai-target="news_body" data-ai-brief="news_brief">Gerar redacao com IA</button>
                    <div class="ai-external-actions" aria-label="Abrir IA logada no navegador">
                        <button class="secondary-action" type="button" data-ai-open="chatgpt" data-ai-context="news" data-ai-title="news_title" data-ai-brief="news_brief">Abrir ChatGPT logado</button>
                        <button class="secondary-action" type="button" data-ai-open="gemini" data-ai-context="news" data-ai-title="news_title" data-ai-brief="news_brief">Abrir Gemini logado</button>
                        <button class="secondary-action" type="button" data-ai-open="copilot" data-ai-context="news" data-ai-title="news_title" data-ai-brief="news_brief">Abrir Copilot logado</button>
                    </div>
                </div>
            </section>

            <label>
                Texto da noticia
                <textarea id="news_body" name="body" rows="16" required>{{ old('body', $article->body ?: ($aiPrefill['body'] ?? null)) }}</textarea>
            </label>

            <div class="role-grid">
                <label class="inline-check">
                    <input type="checkbox" name="is_premium" value="1" @checked(old('is_premium', $article->is_premium))>
                    Conteudo premium
                </label>
                <label class="inline-check">
                    <input type="checkbox" name="allow_ads" value="1" @checked(old('allow_ads', $article->allow_ads ?? true))>
                    Exibir anuncios
                </label>
            </div>

            <section class="ad-settings-panel" aria-label="Carrossel audiovisual da home">
                <p class="eyebrow">Home</p>
                <h2>Carrossel audiovisual</h2>
                <p>Use esta area quando a noticia tiver video, reel ou foto de reportagem para aparecer no carrossel rotativo da pagina inicial.</p>

                <label class="inline-check">
                    <input type="checkbox" name="show_in_carousel" value="1" @checked(old('show_in_carousel', $article->show_in_carousel))>
                    Exibir esta noticia no carrossel
                </label>

                <div class="social-settings-grid">
                    <label>
                        Onde exibir
                        <select name="carousel_type">
                            <option value="youtube" @selected(old('carousel_type', $article->carousel_type ?? 'youtube') === 'youtube')>Bloco YouTube horizontal</option>
                            <option value="facebook_reel" @selected(old('carousel_type', $article->carousel_type) === 'facebook_reel')>Bloco Facebook/Reels vertical</option>
                        </select>
                    </label>
                    <label>
                        Ordem
                        <input type="number" min="0" name="carousel_sort_order" value="{{ old('carousel_sort_order', $article->carousel_sort_order ?? 0) }}">
                    </label>
                </div>

                <label>
                    Codigo iframe do video
                    <textarea name="carousel_embed_code" rows="5" placeholder="<iframe src=&quot;...&quot; allowfullscreen></iframe>">{{ old('carousel_embed_code', $article->carousel_embed_code) }}</textarea>
                </label>

                <label>
                    Foto da reportagem para o carrossel
                    <input type="file" name="carousel_image" accept="image/png,image/jpeg,image/webp">
                </label>

                @if($article->carousel_image_path)
                    <div class="carousel-image-preview">
                        <img src="{{ asset($article->carousel_image_path) }}" alt="Imagem atual do carrossel">
                    </div>
                @endif
            </section>

            <button class="secondary-action" type="submit">Salvar noticia</button>
        </form>
    </section>
@endsection
