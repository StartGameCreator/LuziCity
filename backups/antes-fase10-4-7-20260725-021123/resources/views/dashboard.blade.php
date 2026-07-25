@extends('layouts.app', ['title' => 'Painel - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Painel</p>
        <h1>Ola, {{ $user->name }}</h1>
        <p>Seus papeis: {{ $user->roles->pluck('name')->join(', ') ?: 'Usuario' }}</p>

        @if($user->hasAnyRole(['Super Admin', 'Admin']))
            <div class="dashboard-admin-actions">
                <a class="secondary-action" href="{{ route('admin.index') }}"><x-app-icon name="dashboard" /> Abrir Backend</a>
                <a class="secondary-action" href="{{ route('admin.news.index') }}"><x-app-icon name="news" /> Notícias</a>
                <a class="secondary-action" href="{{ route('admin.users.index') }}"><x-app-icon name="user" /> Usuários</a>
            </div>
        @endif

        @if($user->hasAdFreeAccess())
            <div class="notice">Sua assinatura esta ativa. A experiencia sera exibida sem anuncios.</div>
        @else
            <div class="notice">Sua conta ainda nao esta marcada como assinante.</div>
        @endif
    </section>
@endsection
