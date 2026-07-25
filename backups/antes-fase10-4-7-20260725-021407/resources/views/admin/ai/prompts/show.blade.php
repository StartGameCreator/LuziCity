@extends('layouts.app')
@section('title',$prompt->name)
@section('content')
<section class="content-band"><p class="eyebrow">Prompt {{ $prompt->key }}</p><h1>{{ $prompt->name }}</h1>
@if(session('status'))<p><strong>{{ session('status') }}</strong></p>@endif
<div class="admin-actions"><a class="primary-action" href="{{ route('admin.ai.prompts.edit',$prompt) }}">Editar</a><a class="secondary-action" href="{{ route('admin.ai.prompts.test',$prompt) }}">Testar sem publicar</a><a class="secondary-action" href="{{ route('admin.ai.prompts.index') }}">Biblioteca</a></div>
<article class="admin-card"><h2>Versão atual {{ $prompt->version }}</h2><h3>Sistema</h3><pre>{{ $prompt->system_prompt }}</pre><h3>Usuário</h3><pre>{{ $prompt->user_template }}</pre></article>
<article class="admin-card"><h2>Histórico</h2>
<form method="get" action="{{ route('admin.ai.prompts.compare',$prompt) }}" class="admin-actions"><select name="from">@foreach($prompt->versions as $v)<option>{{ $v->version }}</option>@endforeach</select><span>com</span><select name="to">@foreach($prompt->versions as $v)<option>{{ $v->version }}</option>@endforeach</select><button class="secondary-action">Comparar</button></form>
@foreach($prompt->versions as $version)<div class="version-row"><div><strong>Versão {{ $version->version }}</strong> · {{ $version->created_at?->format('d/m/Y H:i') }} · {{ $version->author?->name??'Sistema' }}<p>{{ $version->change_notes }}</p></div>
@if($version->version!==$prompt->version)<form method="post" action="{{ route('admin.ai.prompts.restore',[$prompt,$version]) }}">@csrf<button class="secondary-action">Restaurar como nova versão</button></form>@endif</div>@endforeach</article></section>
<style>.admin-card{padding:1rem;margin-top:1rem;border:1px solid var(--border);border-radius:18px}.admin-actions{display:flex;gap:.7rem;align-items:center;flex-wrap:wrap}pre{white-space:pre-wrap;overflow-wrap:anywhere;background:var(--surface-soft);padding:1rem;border-radius:.7rem}.version-row{display:flex;justify-content:space-between;gap:1rem;padding:.8rem 0;border-bottom:1px solid var(--border)}</style>
@endsection
