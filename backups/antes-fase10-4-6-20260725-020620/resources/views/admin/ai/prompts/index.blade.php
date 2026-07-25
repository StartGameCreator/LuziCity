@extends('layouts.app')
@section('title','Biblioteca de Prompts')
@section('content')
<section class="content-band">
 <div class="section-heading"><div><p class="eyebrow">Central Editorial IA</p><h1>Biblioteca de Prompts</h1><p>Prompts controlados, versionados e sempre sujeitos à revisão humana.</p></div><a class="primary-action" href="{{ route('admin.ai.prompts.create') }}">Novo prompt</a></div>
 @if(session('status'))<p><strong>{{ session('status') }}</strong></p>@endif
 <form method="get" class="prompt-filter"><input name="q" value="{{ request('q') }}" placeholder="Buscar por nome ou chave"><select name="purpose"><option value="">Todas as finalidades</option>@foreach($purposes as $key=>$label)<option value="{{ $key }}" @selected(request('purpose')===$key)>{{ $label }}</option>@endforeach</select><button class="secondary-action">Filtrar</button></form>
 <div class="prompt-grid">@forelse($templates as $template)<article class="admin-card">
  <div><span class="eyebrow">{{ $purposes[$template->purpose] ?? $template->purpose }}</span><h2>{{ $template->name }}</h2><code>{{ $template->key }}</code></div>
  <p>Versão {{ $template->version }} · {{ $template->versions_count }} registros · {{ $template->is_active?'ativo':'inativo' }}{{ $template->is_default?' · padrão':'' }}</p>
  <div class="admin-actions"><a class="secondary-action" href="{{ route('admin.ai.prompts.show',$template) }}">Abrir</a><a class="secondary-action" href="{{ route('admin.ai.prompts.edit',$template) }}">Editar</a>
  <form method="post" action="{{ route('admin.ai.prompts.duplicate',$template) }}">@csrf<button class="secondary-action">Duplicar</button></form>
  <form method="post" action="{{ route('admin.ai.prompts.toggle',$template) }}">@csrf @method('patch')<button class="secondary-action">{{ $template->is_active?'Desativar':'Ativar' }}</button></form></div>
 </article>@empty<p>Nenhum prompt encontrado.</p>@endforelse</div>{{ $templates->links() }}
</section>
<style>.prompt-filter{display:flex;gap:.7rem;flex-wrap:wrap;margin:1rem 0}.prompt-filter input,.prompt-filter select{padding:.7rem;border:1px solid var(--border);border-radius:.6rem}.prompt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem}.admin-card{padding:1rem;display:grid;gap:.8rem;border:1px solid var(--border);border-radius:18px}.admin-actions{display:flex;gap:.5rem;flex-wrap:wrap}.admin-actions form{margin:0}</style>
@endsection
