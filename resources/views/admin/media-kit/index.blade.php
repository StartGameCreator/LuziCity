@extends('layouts.app')
@section('title', 'Mídia Kit')
@section('content')
<section class="content-band">
    <p class="eyebrow">Fase 13.3 · Comercial</p><h1>Mídia Kit</h1>
    <p>Formatos, preços e propostas comerciais.</p>
    <a class="primary-action" href="{{ route('media-kit.pdf') }}" target="_blank">Visualizar mídia kit em PDF</a>
</section>
<section class="settings-panel">
    <h2>Novo formato</h2>
    <form class="admin-form" method="post" action="{{ route('admin.media-kit.formats.store') }}">@csrf
        <label>Nome<input name="name" required></label><label>Posição<input name="placement" required placeholder="home_top"></label>
        <label>Dimensões<input name="dimensions" placeholder="970 x 250 px"></label>
        <label>Preço<input type="number" name="price" required min="0" step="0.01"></label>
        <label>Cobrança<select name="billing_model"><option value="fixed">Fixa</option><option value="cpm">CPM</option><option value="cpc">CPC</option></select></label>
        <label>Ordem<input type="number" name="display_order" min="0" value="0"></label>
        <label>Descrição<textarea name="description"></textarea></label>
        <label><input type="checkbox" name="is_active" value="1" checked> Ativo</label><button class="secondary-action">Adicionar formato</button>
    </form>
</section>
<section class="category-admin-list">
@forelse($formats as $format)
    <article class="settings-panel"><p class="eyebrow">{{ $format->placement }} · {{ strtoupper($format->billing_model) }}</p><h2>{{ $format->name }}</h2><p>{{ $format->dimensions }} · R$ {{ number_format((float)$format->price, 2, ',', '.') }}</p><p>{{ $format->description }}</p></article>
@empty <p>Nenhum formato cadastrado.</p> @endforelse
</section>
<section class="settings-panel">
    <h2>Nova proposta</h2>
    <form class="admin-form" method="post" action="{{ route('admin.media-kit.proposals.store') }}">@csrf
        <label>Anunciante<select name="advertiser_profile_id" required><option value="">Selecione</option>@foreach($advertisers as $advertiser)<option value="{{ $advertiser->id }}">{{ $advertiser->company_name }}</option>@endforeach</select></label>
        <label>Título<input name="title" required></label><label>Válida até<input type="date" name="valid_until"></label>
        <label>Desconto<input type="number" name="discount" min="0" step="0.01" value="0"></label>
        <fieldset><legend>Itens da proposta</legend>@foreach($formats->where('is_active', true) as $format)<label><input type="checkbox" name="format_ids[]" value="{{ $format->id }}"> {{ $format->name }} — R$ {{ number_format((float)$format->price, 2, ',', '.') }} <input type="number" name="quantities[{{ $format->id }}]" min="1" value="1" aria-label="Quantidade"></label>@endforeach</fieldset>
        <label>Observações<textarea name="notes"></textarea></label><button class="primary-action">Criar proposta</button>
    </form>
</section>
<section class="category-admin-list">
@forelse($proposals as $proposal)
    <article class="settings-panel"><p class="eyebrow">{{ $proposal->number }} · {{ $proposal->status }}</p><h2><a href="{{ route('admin.media-kit.proposals.show', $proposal) }}">{{ $proposal->title }}</a></h2><p>{{ $proposal->advertiser?->company_name }} · R$ {{ number_format((float)$proposal->total, 2, ',', '.') }}</p></article>
@empty <p>Nenhuma proposta criada.</p> @endforelse
</section>
{{ $proposals->links() }}
@endsection
