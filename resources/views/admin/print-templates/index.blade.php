@extends('layouts.app')
@section('title', 'Templates do jornal impresso')
@section('content')
<section class="content-band">
    <p class="eyebrow">Fase 19.2 · Diagramação</p>
    <h1>Templates do jornal</h1>
    <p>Configure capas, páginas internas, espaços publicitários e créditos reutilizáveis.</p>
    <div class="action-row">
        <a class="primary-action" href="{{ route('admin.print-templates.create') }}">Novo template</a>
        <a class="secondary-action" href="{{ route('admin.print-editions.index') }}">Ver edições</a>
    </div>
</section>

<section class="settings-panel">
    <h2>Templates cadastrados</h2>
    @forelse($templates as $template)
        <article class="settings-summary">
            <div>
                <strong>{{ $template->name }} @if($template->is_default) · padrão @endif</strong>
                <p>Capa {{ $template->cover_style }} · interno {{ $template->internal_style }} · {{ $template->ad_slots_count }} anúncio(s) · {{ $template->editions_count }} edição(ões)</p>
            </div>
            <div class="action-row">
                <a class="secondary-action" href="{{ route('admin.print-templates.edit', $template) }}">Editar</a>
                <form method="post" action="{{ route('admin.print-templates.destroy', $template) }}" onsubmit="return confirm('Remover este template? As edições vinculadas ficarão sem template.')">
                    @csrf @method('DELETE')
                    <button class="secondary-action">Remover</button>
                </form>
            </div>
        </article>
    @empty
        <p>Nenhum template cadastrado.</p>
    @endforelse
</section>
@endsection
