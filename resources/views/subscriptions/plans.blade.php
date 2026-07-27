@extends('layouts.app')
@section('title','Planos de assinatura')
@section('content')
<section class="content-band"><p class="eyebrow">Clube LuziCity</p><h1>Escolha seu plano</h1><p>Informação local, conteúdo exclusivo e benefícios para você ou sua empresa.</p></section>
<section class="category-admin-list" aria-label="Planos disponíveis">
@forelse($plans as $plan)<article class="settings-panel" @if($plan->is_featured) aria-label="Plano recomendado" @endif><p class="eyebrow">{{ $plan->is_featured?'Recomendado':'Plano' }}</p><h2>{{ $plan->name }}</h2><p>{{ $plan->description }}</p><h3>R$ {{ number_format((float)$plan->monthly_price,2,',','.') }} <small>/mês</small></h3><p>ou R$ {{ number_format((float)$plan->yearly_price,2,',','.') }} por ano</p><ul>@foreach($plan->benefits??[] as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>@auth<a class="primary-action" href="{{ route('dashboard') }}">Selecionar plano</a>@else<a class="primary-action" href="{{ route('login') }}">Entrar para assinar</a>@endauth</article>@empty<p>Nenhum plano disponível.</p>@endforelse
</section>
@endsection
