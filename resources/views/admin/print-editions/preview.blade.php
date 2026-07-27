@extends('layouts.app')
@section('title', 'Prévia - '.$edition->title)
@section('content')
<section class="content-band">
    <p class="eyebrow">Fase 19.4 · Revisão final</p>
    <h1>Prévia da edição</h1>
    <p>{{ $edition->title }} · {{ $edition->edition_date->format('d/m/Y') }} · {{ $review['page_count'] }} página(s)</p>
    <div class="action-row">
        <a class="secondary-action" href="{{ route('admin.print-editions.edit', $edition) }}">Voltar à pauta</a>
        @if($edition->print_template_id)<a class="primary-action" target="_blank" href="{{ route('admin.print-editions.pdf', $edition) }}">Abrir PDF</a>@endif
    </div>
</section>

<section class="settings-panel">
    <h2>Paginação</h2>
    <div class="action-row" aria-label="Páginas previstas">
        @for($page = 1; $page <= $review['page_count']; $page++)
            <span class="secondary-action">Página {{ $page }}</span>
        @endfor
    </div>
    @if($edition->print_template_id)
        <iframe title="Prévia PDF de {{ $edition->title }}" src="{{ route('admin.print-editions.pdf', $edition) }}#toolbar=1&navpanes=0" style="width:100%;height:75vh;border:1px solid var(--border-color);margin-top:1rem"></iframe>
    @else
        <p>Selecione um template para gerar a prévia em PDF.</p>
    @endif
</section>

<section class="settings-panel">
    <h2>Alertas de diagramação</h2>
    @forelse($review['warnings'] as $warning)
        <article class="settings-summary">
            <div>
                <strong>{{ $warning['level'] === 'error' ? 'Erro' : 'Atenção' }}{{ isset($warning['article_title']) ? ' · '.$warning['article_title'] : '' }}</strong>
                <p>{{ $warning['message'] }}</p>
            </div>
        </article>
    @empty
        <p>Nenhum alerta encontrado. O conteúdo cabe na paginação prevista.</p>
    @endforelse
</section>

<section class="settings-panel">
    <h2>Aprovação final</h2>
    <p>Status:
        <strong>{{ ['draft' => 'Rascunho', 'review' => 'Em revisão', 'approved' => 'Aprovada'][$edition->review_status] ?? $edition->review_status }}</strong>
        @if($edition->approved_at)
            · {{ $edition->approved_at->format('d/m/Y H:i') }} por {{ $edition->approver?->name }}
        @endif
    </p>
    @if($edition->approved_pdf_sha256)
        <p><small>Integridade do PDF aprovado: <code>SHA-256 {{ $edition->approved_pdf_sha256 }}</code></small></p>
    @endif
    @if(!$edition->isApproved())
        <form class="admin-form" method="post" action="{{ route('admin.print-editions.review', $edition) }}">
            @csrf
            <label>Observações da revisão<textarea name="review_notes" maxlength="10000" rows="5">{{ old('review_notes', $edition->review_notes) }}</textarea></label>
            <button class="secondary-action">Enviar para aprovação</button>
        </form>
        @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
            <form method="post" action="{{ route('admin.print-editions.approve', $edition) }}">
                @csrf
                @if($review['has_warnings'])
                    <label><input type="checkbox" name="acknowledge_warnings" value="1"> Revisei e aceito os alertas apresentados</label>
                @endif
                <button class="primary-action" @disabled($review['has_errors'])>Aprovar edição final</button>
            </form>
        @endif
    @elseif(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
        <p>A edição está bloqueada para preservar a versão aprovada.</p>
        <form method="post" action="{{ route('admin.print-editions.reopen', $edition) }}">
            @csrf
            <button class="secondary-action">Reabrir para ajustes</button>
        </form>
    @endif
</section>
@endsection
