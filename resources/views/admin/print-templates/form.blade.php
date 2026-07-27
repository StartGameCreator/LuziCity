@extends('layouts.app')
@section('title', $template->exists ? 'Editar template impresso' : 'Novo template impresso')
@section('content')
@php
    $slots = old('slot_names')
        ? collect(old('slot_names'))->map(fn ($name, $index) => [
            'name' => $name,
            'page_type' => old("slot_page_types.$index", 'internal'),
            'placement' => old("slot_placements.$index", 'bottom'),
            'size' => old("slot_sizes.$index", 'banner'),
        ])
        : ($template->exists ? $template->adSlots : collect());
    while ($slots->count() < 4) {
        $slots->push(['name' => '', 'page_type' => 'internal', 'placement' => 'bottom', 'size' => 'banner']);
    }
@endphp
<section class="content-band">
    <p class="eyebrow">Fase 19.2 · Template editorial</p>
    <h1>{{ $template->exists ? 'Editar template' : 'Novo template' }}</h1>
    <p>As definições serão reutilizadas na montagem e geração das edições.</p>
    <a class="secondary-action" href="{{ route('admin.print-templates.index') }}">Voltar aos templates</a>
</section>

<form class="admin-form" method="post" action="{{ $template->exists ? route('admin.print-templates.update', $template) : route('admin.print-templates.store') }}">
    @csrf
    @if($template->exists) @method('PUT') @endif

    <section class="settings-panel">
        <h2>Identificação e capa</h2>
        <div class="form-grid">
            <label>Nome<input name="name" maxlength="120" required value="{{ old('name', $template->name) }}"></label>
            <label>Estilo da capa
                <select name="cover_style" required>
                    @foreach(['classic' => 'Clássica', 'modern' => 'Moderna', 'minimal' => 'Minimalista'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('cover_style', $template->cover_style ?: 'classic') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Colunas da capa<input type="number" name="cover_columns" min="1" max="4" required value="{{ old('cover_columns', $template->cover_columns ?: 3) }}"></label>
            <label><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $template->is_default))> Template padrão</label>
        </div>
    </section>

    <section class="settings-panel">
        <h2>Páginas internas</h2>
        <div class="form-grid">
            <label>Estilo
                <select name="internal_style" required>
                    @foreach(['columns' => 'Colunas', 'magazine' => 'Revista', 'compact' => 'Compacto'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('internal_style', $template->internal_style ?: 'columns') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Número de colunas<input type="number" name="internal_columns" min="1" max="4" required value="{{ old('internal_columns', $template->internal_columns ?: 3) }}"></label>
            <label><input type="checkbox" name="show_page_numbers" value="1" @checked(old('show_page_numbers', $template->exists ? $template->show_page_numbers : true))> Exibir números das páginas</label>
        </div>
    </section>

    <section class="settings-panel">
        <h2>Espaços publicitários</h2>
        <p>Linhas sem nome serão ignoradas.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nome</th><th>Página</th><th>Posição</th><th>Tamanho</th></tr></thead>
                <tbody>
                @foreach($slots as $slot)
                    <tr>
                        <td><input name="slot_names[]" maxlength="120" value="{{ data_get($slot, 'name') }}" placeholder="Ex.: Rodapé da capa"></td>
                        <td><select name="slot_page_types[]">
                            @foreach(['cover' => 'Capa', 'internal' => 'Interna', 'back_cover' => 'Contracapa'] as $value => $label)
                                <option value="{{ $value }}" @selected(data_get($slot, 'page_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select></td>
                        <td><select name="slot_placements[]">
                            @foreach(['top' => 'Topo', 'bottom' => 'Rodapé', 'sidebar' => 'Lateral', 'full_page' => 'Página inteira', 'half_page' => 'Meia página'] as $value => $label)
                                <option value="{{ $value }}" @selected(data_get($slot, 'placement') === $value)>{{ $label }}</option>
                            @endforeach
                        </select></td>
                        <td><select name="slot_sizes[]">
                            @foreach(['full' => 'Inteiro', 'half' => 'Metade', 'quarter' => 'Um quarto', 'banner' => 'Banner'] as $value => $label)
                                <option value="{{ $value }}" @selected(data_get($slot, 'size') === $value)>{{ $label }}</option>
                            @endforeach
                        </select></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="settings-panel">
        <h2>Créditos e expediente</h2>
        <label>Texto dos créditos
            <textarea name="credits" rows="8" maxlength="10000" placeholder="Direção, edição, redação, fotografia, contato e endereço.">{{ old('credits', $template->credits) }}</textarea>
        </label>
        <button class="primary-action">{{ $template->exists ? 'Salvar template' : 'Criar template' }}</button>
    </section>
</form>
@endsection
