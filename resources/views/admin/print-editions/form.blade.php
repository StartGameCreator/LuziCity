@extends('layouts.app')
@section('title', $edition->exists ? 'Editar edição' : 'Nova edição')
@section('content')
@php
    $selected = $edition->sections
        ->flatMap(fn ($section) => $section->items->mapWithKeys(fn ($item) => [
            $item->news_article_id => ['section' => $section->name, 'position' => $item->position],
        ]));
@endphp
<section class="content-band">
    <p class="eyebrow">Fase 19.1 · Planejamento editorial</p>
    <h1>{{ $edition->exists ? 'Editar edição' : 'Nova edição impressa' }}</h1>
    <p>Selecione as notícias, atribua uma seção e determine a ordem dentro dela.</p>
    <a class="secondary-action" href="{{ route('admin.print-editions.index') }}">Voltar às edições</a>
    @if($edition->exists)<a class="primary-action" href="{{ route('admin.print-editions.preview', $edition) }}">Prévia e revisão</a>@endif
</section>

<form class="admin-form" method="post" action="{{ $edition->exists ? route('admin.print-editions.update', $edition) : route('admin.print-editions.store') }}">
    @csrf
    @if($edition->exists) @method('PUT') @endif

    <section class="settings-panel">
        <h2>Identificação</h2>
        <div class="form-grid">
            <label>Título
                <input name="title" maxlength="180" required value="{{ old('title', $edition->title) }}" placeholder="Ex.: Luzicity · Edição 42">
            </label>
            <label>Data da edição
                <input type="date" name="edition_date" required value="{{ old('edition_date', optional($edition->edition_date)->format('Y-m-d')) }}">
            </label>
            <label>Template
                <select name="print_template_id">
                    <option value="">Definir posteriormente</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" @selected((string) old('print_template_id', $edition->print_template_id) === (string) $template->id)>
                            {{ $template->name }}{{ $template->is_default ? ' · padrão' : '' }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>Formato do PDF
                <select name="pdf_format" required>
                    @foreach(['a4' => 'A4 (210 × 297 mm)', 'tabloid' => 'Tabloide (11 × 17 pol.)', 'magazine' => 'Revista (Carta)'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('pdf_format', $edition->pdf_format ?: 'a4') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Sangria (mm)
                <input type="number" name="bleed_mm" min="0" max="10" step=".5" required value="{{ old('bleed_mm', $edition->bleed_mm ?? 3) }}">
            </label>
            <label><input type="checkbox" name="high_resolution_images" value="1" @checked(old('high_resolution_images', $edition->exists ? $edition->high_resolution_images : true))> Incorporar imagens em alta resolução</label>
        </div>
        @if($templates->isEmpty())
            <p><a href="{{ route('admin.print-templates.create') }}">Crie um template</a> para definir capa, páginas internas, anúncios e créditos.</p>
        @endif
    </section>

    <section class="settings-panel">
        <h2>Notícias selecionadas e ordem</h2>
        <p>Use o mesmo nome de seção para agrupar matérias. Números menores aparecem primeiro.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Selecionar</th><th>Notícia</th><th>Seção</th><th>Ordem</th></tr></thead>
                <tbody>
                @forelse($articles as $article)
                    @php
                        $item = $selected->get($article->id);
                        $checked = in_array($article->id, old('article_ids', $selected->keys()->all()));
                    @endphp
                    <tr>
                        <td><input type="checkbox" name="article_ids[]" value="{{ $article->id }}" @checked($checked) aria-label="Selecionar {{ $article->title }}"></td>
                        <td>
                            <strong>{{ $article->title }}</strong><br>
                            <small>{{ optional($article->published_at)->format('d/m/Y H:i') }}</small>
                        </td>
                        <td><input name="sections[{{ $article->id }}]" maxlength="120" value="{{ old("sections.$article->id", $item['section'] ?? '') }}" placeholder="Ex.: Cidade"></td>
                        <td><input type="number" name="positions[{{ $article->id }}]" min="0" max="9999" value="{{ old("positions.$article->id", $item['position'] ?? 0) }}"></td>
                    </tr>
                @empty
                    <tr><td colspan="4">Não há notícias publicadas disponíveis neste site.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @error('article_ids') <p class="form-error">{{ $message }}</p> @enderror
        @error('sections.*') <p class="form-error">{{ $message }}</p> @enderror
        <button class="primary-action" type="submit" @disabled($edition->isApproved())>{{ $edition->isApproved() ? 'Edição aprovada e bloqueada' : ($edition->exists ? 'Salvar edição' : 'Criar edição') }}</button>
        @if($edition->exists && $edition->print_template_id)
            <a class="secondary-action" target="_blank" href="{{ route('admin.print-editions.pdf', $edition) }}">Gerar e abrir PDF</a>
            @if($edition->pdf_generated_at)<small>Última geração: {{ $edition->pdf_generated_at->format('d/m/Y H:i') }}</small>@endif
        @endif
    </section>
</form>
@endsection
