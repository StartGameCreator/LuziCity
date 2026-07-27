@extends('layouts.app')
@section('title','Meus benefícios')
@section('content')
<section class="content-band"><p class="eyebrow">Clube LuziCity</p><h1>Meus benefícios</h1><p>Vantagens disponíveis no seu plano atual.</p></section>
<section class="category-admin-list">@forelse($benefits as $benefit)<article class="settings-panel"><p class="eyebrow">{{ $benefit->type }}</p><h2>{{ $benefit->name }}</h2><p>{{ $benefit->description }}</p>@if($benefit->redemptions->isNotEmpty())<p class="notice">Resgatado @if($benefit->code)· código: <strong>{{ $benefit->code }}</strong>@endif</p>@else<form method="post" action="{{ route('subscriber.benefits.redeem',$benefit) }}">@csrf<button class="primary-action">Resgatar benefício</button></form>@endif @if($benefit->destination_url)<a class="secondary-action" href="{{ $benefit->destination_url }}" rel="noopener noreferrer">Acessar</a>@endif</article>@empty<p>Nenhum benefício disponível para seu plano.</p>@endforelse</section>
@endsection
