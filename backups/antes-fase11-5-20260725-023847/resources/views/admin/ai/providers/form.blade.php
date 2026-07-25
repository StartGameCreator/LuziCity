@extends('layouts.app')
@section('title','Configurar '.$provider->name)
@section('content')
<section class="content-band"><p class="eyebrow">Provedores</p><h1>{{ $provider->name }}</h1><form class="admin-card" method="post" action="{{ route('admin.ai.providers.update',$provider) }}">@csrf @method('put')
@foreach([['name','Nome','text'],['model','Modelo','text'],['endpoint','URL base','url'],['priority','Prioridade','number'],['timeout_seconds','Timeout (segundos)','number'],['retry_attempts','Tentativas','number'],['daily_request_limit','Limite diário (0 = ilimitado)','number'],['monthly_request_limit','Limite mensal (0 = ilimitado)','number'],['input_cost_per_million','Custo/1M tokens entrada','number'],['output_cost_per_million','Custo/1M tokens saída','number']] as [$n,$l,$t])<label>{{ $l }}<input type="{{ $t }}" name="{{ $n }}" value="{{ old($n,$provider->$n) }}" @if(str_contains($n,'cost')) step="0.000001" @endif required></label>@endforeach
<label><input type="checkbox" name="is_enabled" value="1" @checked($provider->is_enabled)> Ativo</label><label><input type="checkbox" name="fallback_enabled" value="1" @checked($provider->fallback_enabled)> Permitir fallback</label><p>Credenciais são administradas separadamente e nunca aparecem nesta página.</p><button class="primary-action">Salvar provedor</button></form></section>
<style>.admin-card{max-width:850px;padding:1rem;display:grid;gap:.8rem;border:1px solid var(--border);border-radius:18px}.admin-card label{display:grid;gap:.3rem}.admin-card input{padding:.65rem}</style>
@endsection
