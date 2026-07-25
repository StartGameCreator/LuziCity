@extends('layouts.app', ['title' => 'Editorias - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Editorias e submenus</h1>
        <p>Crie botões principais do menu inferior e adicione submenus para assuntos específicos.</p>
    </section>

    <section class="settings-panel" aria-label="Nova editoria">
        <form method="post" action="{{ route('admin.categories.store') }}" class="admin-form social-settings-form">
            @csrf

            <div class="social-settings-grid">
                <label>
                    Nome
                    <input name="name" required placeholder="Ex: Ciência e Tecnologia">
                </label>
                <label>
                    Submenu de
                    <select name="parent_id">
                        <option value="">Botão principal</option>
                        @foreach($parentOptions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Slug
                    <input name="slug" placeholder="gerado automaticamente">
                </label>
                <label>
                    Ordem
                    <input type="number" name="sort_order" min="0" value="0">
                </label>
            </div>

            <label>
                Descrição
                <textarea name="description" rows="3"></textarea>
            </label>

            <label class="inline-check">
                <input type="checkbox" name="is_active" value="1" checked>
                Exibir no site
            </label>

            <button class="secondary-action" type="submit">Salvar editoria</button>
        </form>
    </section>

    <section class="category-admin-list" aria-label="Editorias cadastradas">
        @foreach($categories as $category)
            <article class="settings-panel category-admin-card">
                <form method="post" action="{{ route('admin.categories.update', $category) }}" class="admin-form social-settings-form">
                    @csrf
                    @method('put')

                    <div class="social-settings-grid">
                        <label>
                            Nome
                            <input name="name" value="{{ $category->name }}" required>
                        </label>
                        <label>
                            Submenu de
                            <select name="parent_id">
                                <option value="">Botão principal</option>
                                @foreach($parentOptions->where('id', '!=', $category->id) as $parent)
                                    <option value="{{ $parent->id }}" @selected($category->parent_id === $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Slug
                            <input name="slug" value="{{ $category->slug }}">
                        </label>
                        <label>
                            Ordem
                            <input type="number" name="sort_order" min="0" value="{{ $category->sort_order }}">
                        </label>
                    </div>

                    <label>
                        Descrição
                        <textarea name="description" rows="3">{{ $category->description }}</textarea>
                    </label>

                    <label class="inline-check">
                        <input type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                        Exibir no site
                    </label>

                    <button class="secondary-action" type="submit">Atualizar editoria</button>
                </form>

                <div class="category-children">
                    <strong>Adicionar submenu em {{ $category->name }}</strong>
                    <form method="post" action="{{ route('admin.categories.store') }}" class="category-child-form">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $category->id }}">
                        <input name="name" placeholder="Nome do submenu" aria-label="Nome do novo submenu">
                        <input name="slug" placeholder="slug automático" aria-label="Slug do novo submenu">
                        <input type="number" name="sort_order" min="0" value="0" aria-label="Ordem do novo submenu">
                        <input type="hidden" name="description" value="">
                        <label class="inline-check">
                            <input type="checkbox" name="is_active" value="1" checked>
                            Ativo
                        </label>
                        <button class="secondary-action" type="submit">Adicionar</button>
                    </form>
                </div>

                @if($category->children->isNotEmpty())
                    <div class="category-children">
                        <strong>Submenus</strong>
                        @foreach($category->children as $child)
                            <form method="post" action="{{ route('admin.categories.update', $child) }}" class="category-child-form">
                                @csrf
                                @method('put')
                                <input type="hidden" name="parent_id" value="{{ $category->id }}">
                                <input name="name" value="{{ $child->name }}" aria-label="Nome do submenu">
                                <input name="slug" value="{{ $child->slug }}" aria-label="Slug do submenu">
                                <input type="number" name="sort_order" min="0" value="{{ $child->sort_order }}" aria-label="Ordem do submenu">
                                <input type="hidden" name="description" value="{{ $child->description }}">
                                <label class="inline-check">
                                    <input type="checkbox" name="is_active" value="1" @checked($child->is_active)>
                                    Ativo
                                </label>
                                <button class="secondary-action" type="submit">Salvar</button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </section>
@endsection
