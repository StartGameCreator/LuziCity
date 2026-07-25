@extends('layouts.app')
@section('title','Log de IA')
@section('content')
<section class="admin-page">@include('admin.ai.partials.navigation')<div class="section-heading"><div><span class="eyebrow">Execução #{{ $execution->id }}</span><h1>{{ $execution->feature }}</h1></div><a href="{{ route('admin.ai.logs.index') }}">Voltar</a></div>
<section class="admin-card" style="padding:1rem"><dl><dt>Status</dt><dd>{{ $execution->status }}</dd><dt>Usuário</dt><dd>{{ $execution->user?->name ?? 'Sistema' }}</dd><dt>Provedor/modelo</dt><dd>{{ $execution->provider?->name ?? '—' }} / {{ $execution->model ?? $execution->provider?->model ?? '—' }}</dd><dt>Tokens</dt><dd>{{ $execution->total_tokens }}</dd><dt>Custo</dt><dd>R$ {{ number_format($execution->estimated_cost_micros/1000000,6,',','.') }}</dd><dt>Erro</dt><dd>{{ $execution->error_message ?: '—' }}</dd><dt>Hash de entrada</dt><dd>{{ $execution->input_hash ?: '—' }}</dd></dl><p>Payloads integrais não são exibidos neste log comum.</p></section></section>
@endsection
