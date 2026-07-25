@extends('layouts.app', ['title' => 'Backend - Luzicity'])

@section('content')
    <section class="content-band admin-home-hero">
        <p class="eyebrow">Backend Luzicity</p>
        <h1>Painel administrativo</h1>
        <p>Escolha uma area para configurar o portal, publicar conteudo, moderar anuncios e administrar a plataforma.</p>
    </section>

    <section class="admin-home-grid" aria-label="Areas do backend">
        @foreach($sections as $section)
            <a class="settings-panel admin-home-card" href="{{ route($section['route']) }}">
                <span class="admin-home-icon"><x-app-icon :name="$section['icon']" /></span>
                <span>
                    <strong>{{ $section['label'] }}</strong>
                    <small>{{ $section['description'] }}</small>
                </span>
            </a>
        @endforeach
    </section>
@endsection