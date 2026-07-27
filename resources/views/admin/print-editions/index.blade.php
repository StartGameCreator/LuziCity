@extends('layouts.app')
@section('title', 'Edições impressas')
@section('content')
<section class="content-band">
    <p class="eyebrow">Fase 19.1 · Jornal impresso</p>
    <h1>Edições</h1>
    <p>Monte a pauta do jornal por data, seção e ordem de publicação.</p>
    <div class="action-row">
        <a class="primary-action" href="{{ route('admin.print-editions.create') }}">Nova edição</a>
        <a class="secondary-action" href="{{ route('admin.print-templates.index') }}">Templates</a>
    </div>
</section>

<section class="settings-panel">
    <h2>Edições cadastradas</h2>
    @forelse($editions as $edition)
        <article class="settings-summary">
            <div>
                <strong>{{ $edition->title }}</strong>
                <p>{{ $edition->edition_date->format('d/m/Y') }} · {{ $edition->sections_count }} seção(ões)</p>
            </div>
            <div class="action-row">
                <a class="secondary-action" href="{{ route('admin.print-editions.edit', $edition) }}">Editar pauta</a>
                <a class="secondary-action" href="{{ route('admin.print-editions.preview', $edition) }}">Prévia</a>
                @if($edition->print_template_id)<a class="secondary-action" target="_blank" href="{{ route('admin.print-editions.pdf', $edition) }}">PDF</a>@endif
                <form method="post" action="{{ route('admin.print-editions.destroy', $edition) }}" onsubmit="return confirm('Remover esta edição?')">
                    @csrf @method('DELETE')
                    <button class="secondary-action" type="submit">Remover</button>
                </form>
            </div>
        </article>
    @empty
        <p>Nenhuma edição criada.</p>
    @endforelse
    {{ $editions->links() }}
</section>
@endsection
