@extends('layouts.app', ['title' => 'Tags - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Tags</h1>
        <p>Cadastre palavras-chave para organizar matérias, temas e assuntos recorrentes.</p>
    </section>

    <section class="settings-panel" aria-label="Nova tag">
        <form method="post" action="{{ route('admin.tags.store') }}" class="admin-form social-settings-form">
            @csrf

            <div class="social-settings-grid">
                <label>
                    Nome da tag
                    <input name="name" required placeholder="Ex: Segurança Pública">
                </label>

                <label>
                    Slug
                    <input name="slug" placeholder="gerado automaticamente">
                </label>
            </div>

            <button class="secondary-action" type="submit">Salvar tag</button>
        </form>
    </section>

    <section class="category-admin-list" aria-label="Tags cadastradas">
        @foreach($tags as $tag)
            <article class="settings-panel category-admin-card">
                <form method="post" action="{{ route('admin.tags.update', $tag) }}" class="admin-form social-settings-form">
                    @csrf
                    @method('put')

                    <div class="social-settings-grid">
                        <label>
                            Nome
                            <input name="name" value="{{ $tag->name }}" required>
                        </label>

                        <label>
                            Slug
                            <input name="slug" value="{{ $tag->slug }}">
                        </label>
                    </div>

                    <p class="tag-admin-meta">{{ $tag->articles_count }} matéria(s) vinculada(s)</p>

                    <button class="secondary-action" type="submit">Atualizar tag</button>
                </form>
            </article>
        @endforeach
    </section>
@endsection
