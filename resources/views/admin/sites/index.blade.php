@extends('layouts.app')
@section('title','Sites')
@section('content')
<section class="content-band"><p class="eyebrow">Fase 17.1 · Multisite</p><h1>Estrutura de sites</h1><p>Domínios, identidade, cidade, tema e configurações específicas.</p></section>
<section class="settings-panel"><h2>Novo site</h2>
<form class="admin-form" method="post" action="{{ route('admin.sites.store') }}" enctype="multipart/form-data">@csrf
@include('admin.sites.partials.fields',['site'=>null])
<button class="primary-action">Criar site</button></form></section>
@foreach($sites as $site)
<section class="settings-panel"><h2>{{ $site->name }} @if($site->is_default)<small>· padrão</small>@endif</h2>
<form class="admin-form" method="post" action="{{ route('admin.sites.update',$site) }}" enctype="multipart/form-data">@csrf @method('put')
@include('admin.sites.partials.fields',['site'=>$site])
<button class="secondary-action">Salvar alterações</button></form></section>
@endforeach
@endsection
