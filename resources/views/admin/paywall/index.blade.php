@extends('layouts.app')
@section('title','Paywall')
@section('content')
<section class="content-band"><p class="eyebrow">Fase 14.2 · Assinaturas</p><h1>Paywall</h1><p>Conteúdo exclusivo, limites mensais, prévias e regras por categoria.</p></section>
<section class="category-admin-list">@foreach([['Categorias protegidas',$metrics['protected_categories']],['Leituras no mês',$metrics['monthly_accesses']],['Leitores no mês',$metrics['readers']]] as [$label,$value])<article class="settings-panel"><small>{{ $label }}</small><h2>{{ $value }}</h2></article>@endforeach</section>
@foreach($categories as $category)<section class="settings-panel"><h2>{{ $category->name }}</h2><form class="admin-form" method="post" action="{{ route('admin.paywall.categories.update',$category) }}">@csrf @method('PUT')
<label><input type="checkbox" name="is_enabled" value="1" @checked($category->paywallRule?->is_enabled)> Proteger esta categoria</label>
<label>Plano mínimo<select name="minimum_plan_id"><option value="">Qualquer plano premium</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected($category->paywallRule?->minimum_plan_id===$plan->id)>{{ $plan->name }}</option>@endforeach</select></label>
<label>Caracteres da prévia<input type="number" name="preview_characters" min="100" max="5000" value="{{ $category->paywallRule?->preview_characters??600 }}" required></label><button class="secondary-action">Salvar regra</button></form></section>@endforeach
@endsection
