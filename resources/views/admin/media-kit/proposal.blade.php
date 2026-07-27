@extends('layouts.app')
@section('title', $proposal->number)
@section('content')
<section class="content-band"><p class="eyebrow">{{ $proposal->status }}</p><h1>{{ $proposal->title }}</h1><p>{{ $proposal->number }} · {{ $proposal->advertiser->company_name }}</p><a class="primary-action" target="_blank" href="{{ route('admin.media-kit.proposals.pdf', $proposal) }}">Abrir PDF</a></section>
<section class="settings-panel"><h2>Itens</h2>@foreach($proposal->items as $item)<p>{{ $item->quantity }} × {{ $item->description }} — R$ {{ number_format((float)$item->subtotal, 2, ',', '.') }}</p>@endforeach<hr><p>Desconto: R$ {{ number_format((float)$proposal->discount, 2, ',', '.') }}</p><h2>Total: R$ {{ number_format((float)$proposal->total, 2, ',', '.') }}</h2><p>{{ $proposal->notes }}</p></section>
@if(!$proposal->approved_at)<form method="post" action="{{ route('admin.media-kit.proposals.approve', $proposal) }}">@csrf<button class="primary-action">Aprovar proposta</button></form>@else<p>Proposta aprovada em {{ $proposal->approved_at->format('d/m/Y H:i') }}.</p>@endif
@endsection
