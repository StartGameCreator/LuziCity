@extends('layouts.app')
@section('title', $campaign->exists ? 'Editar campanha' : 'Nova campanha')
@section('content')
<section class="content-band">
    <p class="eyebrow">Comercial · Campanhas</p>
    <h1>{{ $campaign->exists ? 'Editar campanha' : 'Nova campanha' }}</h1>
    @if($campaign->exists)<p>{{ number_format($campaign->impressions_count, 0, ',', '.') }} impressões · {{ number_format($campaign->clicks_count, 0, ',', '.') }} cliques · CTR {{ number_format($campaign->ctr, 2, ',', '.') }}%</p>@endif
</section>
<section class="settings-panel">
<form class="admin-form" method="post" enctype="multipart/form-data" action="{{ $campaign->exists ? route('admin.campaigns.update', $campaign) : route('admin.campaigns.store') }}">
    @csrf @if($campaign->exists) @method('PUT') @endif
    <label>Anunciante<select name="advertiser_profile_id" required><option value="">Selecione</option>@foreach($advertisers as $advertiser)<option value="{{ $advertiser->id }}" @selected(old('advertiser_profile_id', $campaign->advertiser_profile_id)==$advertiser->id)>{{ $advertiser->company_name }}</option>@endforeach</select></label>
    <label>Nome<input name="name" required value="{{ old('name', $campaign->name) }}"></label>
    <label>Tipo<select name="campaign_type">@foreach(['banner'=>'Banner','native'=>'Nativa','sponsored'=>'Conteúdo patrocinado'] as $key=>$label)<option value="{{ $key }}" @selected(old('campaign_type', $campaign->campaign_type ?: 'banner')===$key)>{{ $label }}</option>@endforeach</select></label>
    <label>Posição<input name="placement" required value="{{ old('placement', $campaign->placement) }}" placeholder="home_top, sidebar, article"></label>
    <label>Status<select name="status">@foreach(['draft'=>'Rascunho','pending'=>'Aguardando aprovação','active'=>'Ativa','paused'=>'Pausada','finished'=>'Finalizada','cancelled'=>'Cancelada'] as $key=>$label)<option value="{{ $key }}" @selected(old('status', $campaign->status ?: 'draft')===$key)>{{ $label }}</option>@endforeach</select></label>
    <label>Modelo de cobrança<select name="billing_model">@foreach(['fixed'=>'Valor fixo','cpm'=>'CPM','cpc'=>'CPC'] as $key=>$label)<option value="{{ $key }}" @selected(old('billing_model', $campaign->billing_model ?: 'fixed')===$key)>{{ $label }}</option>@endforeach</select></label>
    <label>Orçamento total<input type="number" name="budget" step="0.01" min="0" required value="{{ old('budget', $campaign->budget ?? 0) }}"></label>
    <label>Orçamento diário<input type="number" name="daily_budget" step="0.01" min="0" value="{{ old('daily_budget', $campaign->daily_budget) }}"></label>
    <label>Preço por impressão<input type="number" name="price_per_impression" step="0.000001" min="0" value="{{ old('price_per_impression', $campaign->price_per_impression) }}"></label>
    <label>Preço por clique<input type="number" name="price_per_click" step="0.000001" min="0" value="{{ old('price_per_click', $campaign->price_per_click) }}"></label>
    <label>Limite de impressões<input type="number" name="impression_limit" min="1" value="{{ old('impression_limit', $campaign->impression_limit) }}"></label>
    <label>Limite de cliques<input type="number" name="click_limit" min="1" value="{{ old('click_limit', $campaign->click_limit) }}"></label>
    <label>URL de destino<input type="url" name="target_url" value="{{ old('target_url', $campaign->target_url) }}"></label>
    <label>Banner<input type="file" name="image" accept="image/*"></label>
    <label>Texto alternativo<input name="image_alt" value="{{ old('image_alt', $campaign->image_alt) }}"></label>
    <label>Início<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $campaign->starts_at?->format('Y-m-d\TH:i')) }}"></label>
    <label>Fim<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $campaign->ends_at?->format('Y-m-d\TH:i')) }}"></label>
    <label>Cidades (separadas por vírgula)<input name="target_cities" value="{{ old('target_cities', implode(', ', $campaign->target_cities ?? [])) }}"></label>
    <label>Categorias (separadas por vírgula)<input name="target_categories" value="{{ old('target_categories', implode(', ', $campaign->target_categories ?? [])) }}"></label>
    <fieldset><legend>Dispositivos</legend>@foreach(['desktop'=>'Desktop','mobile'=>'Celular','tablet'=>'Tablet'] as $key=>$label)<label><input type="checkbox" name="target_devices[]" value="{{ $key }}" @checked(in_array($key, old('target_devices', $campaign->target_devices ?? [])))>{{ $label }}</label>@endforeach</fieldset>
    <fieldset><legend>Dias da semana</legend>@foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $key=>$label)<label><input type="checkbox" name="weekdays[]" value="{{ $key }}" @checked(in_array($key, old('weekdays', $campaign->weekdays ?? [])))>{{ $label }}</label>@endforeach</fieldset>
    <label>Horário inicial<input type="time" name="daily_start_time" value="{{ old('daily_start_time', $campaign->daily_start_time) }}"></label>
    <label>Horário final<input type="time" name="daily_end_time" value="{{ old('daily_end_time', $campaign->daily_end_time) }}"></label>
    <label>Prioridade<input type="number" name="priority" min="0" max="999" value="{{ old('priority', $campaign->priority ?? 0) }}"></label>
    <label>Observações<textarea name="notes">{{ old('notes', $campaign->notes) }}</textarea></label>
    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $campaign->exists ? $campaign->is_active : true))> Cadastro ativo</label>
    <button class="primary-action">Salvar campanha</button>
</form>
@if($campaign->exists && !$campaign->approved_at)
    <form method="post" action="{{ route('admin.campaigns.approve', $campaign) }}">@csrf<button class="secondary-action">Aprovar e ativar campanha</button></form>
@elseif($campaign->approved_at)
    <p>Aprovada em {{ $campaign->approved_at->format('d/m/Y H:i') }}.</p>
@endif
</section>
@endsection
