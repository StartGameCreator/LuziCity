@extends('layouts.app', ['title' => 'Quem somos - Luzicity'])

@section('content')
    <section class="content-band about-page">
        <p class="eyebrow">Luzicity</p>
        <h1>Quem somos</h1>
        <div class="rich-text">
            {!! nl2br(e($aboutContent)) !!}
        </div>
    </section>
@endsection
