@extends('layouts.app')
@section('title',$template->exists?'Editar prompt':'Novo prompt')
@section('content')
<section class="content-band"><p class="eyebrow">Biblioteca de Prompts</p><h1>{{ $template->exists?'Editar prompt':'Novo prompt' }}</h1>
@if($errors->any())<div class="admin-card"><strong>Revise os campos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="prompt-form admin-card" method="post" action="{{ $template->exists?route('admin.ai.prompts.update',$template):route('admin.ai.prompts.store') }}">@csrf @if($template->exists)@method('put')@endif
<label>Nome<input name="name" required maxlength="160" value="{{ old('name',$template->name) }}"></label>
<label>Chave técnica<input name="key" required maxlength="120" pattern="[a-z0-9._-]+" value="{{ old('key',$template->key) }}"></label>
<label>Finalidade<select name="purpose" required>@foreach($purposes as $key=>$label)<option value="{{ $key }}" @selected(old('purpose',$template->purpose)===$key)>{{ $label }}</option>@endforeach</select></label>
<label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$template->exists?$template->is_active:true))> Ativo</label>
<label><input type="checkbox" name="is_default" value="1" @checked(old('is_default',$template->is_default))> Padrão desta finalidade</label>
<label>Prompt do sistema<textarea name="system_prompt" rows="7" required>{{ old('system_prompt',$template->system_prompt) }}</textarea></label>
<label>Prompt do usuário<textarea name="user_template" rows="12" required>{{ old('user_template',$template->user_template) }}</textarea></label>
<label>Esquema de saída JSON<textarea name="output_schema_json" rows="6">{{ old('output_schema_json',$template->output_schema?json_encode($template->output_schema,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE):'') }}</textarea></label>
<label>Notas da alteração<textarea name="change_notes" rows="3" maxlength="1000">{{ old('change_notes') }}</textarea></label>
<details><summary>Variáveis permitidas</summary><p>@foreach($allowedVariables as $variable)<code>@{{{{ $variable }}}}</code> @endforeach</p></details>
<button class="primary-action">{{ $template->exists?'Salvar nova versão':'Criar prompt' }}</button></form></section>
<style>.prompt-form{max-width:950px;padding:1rem;display:grid;gap:1rem}.prompt-form label{display:grid;gap:.35rem}.prompt-form input[type=text],.prompt-form select,.prompt-form textarea{width:100%;padding:.7rem;border:1px solid var(--border);border-radius:.6rem}</style>
@endsection
