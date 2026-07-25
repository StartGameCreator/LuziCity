@extends('layouts.app')
@section('title','Comparar versões')
@section('content')
<section class="content-band"><p class="eyebrow">{{ $prompt->name }}</p><h1>Comparar versões {{ $from->version }} e {{ $to->version }}</h1>
<div class="compare"><article class="admin-card"><h2>Versão {{ $from->version }}</h2><h3>Sistema</h3><pre>{{ $from->system_prompt }}</pre><h3>Usuário</h3><pre>{{ $from->user_prompt }}</pre></article><article class="admin-card"><h2>Versão {{ $to->version }}</h2><h3>Sistema</h3><pre>{{ $to->system_prompt }}</pre><h3>Usuário</h3><pre>{{ $to->user_prompt }}</pre></article></div></section>
<style>.compare{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.admin-card{padding:1rem;border:1px solid var(--border);border-radius:18px}pre{white-space:pre-wrap;overflow-wrap:anywhere;background:var(--surface-soft);padding:1rem}@media(max-width:800px){.compare{grid-template-columns:1fr}}</style>
@endsection
