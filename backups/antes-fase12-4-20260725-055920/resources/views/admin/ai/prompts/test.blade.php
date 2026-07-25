@extends('layouts.app')
@section('title','Testar '.$prompt->name)
@section('content')
<section class="content-band"><p class="eyebrow">Preview seguro</p><h1>Testar {{ $prompt->name }}</h1><p>Este teste apenas renderiza variáveis. Não chama provedor, não cobra e não cria notícia.</p>
<form method="post" class="admin-card">@csrf @foreach($variables as $variable)<label>{{ $variable }}<textarea name="variables[{{ $variable }}]" rows="2">{{ $values[$variable] }}</textarea></label>@endforeach<button class="primary-action">Atualizar preview</button></form>
<article class="admin-card"><h2>Sistema</h2><pre>{{ $renderedSystem }}</pre><h2>Usuário</h2><pre>{{ $renderedUser }}</pre></article></section>
<style>.admin-card{padding:1rem;margin-top:1rem;display:grid;gap:.8rem;border:1px solid var(--border);border-radius:18px}.admin-card label{display:grid;gap:.3rem}.admin-card textarea{width:100%}pre{white-space:pre-wrap;background:var(--surface-soft);padding:1rem;border-radius:.7rem}</style>
@endsection
