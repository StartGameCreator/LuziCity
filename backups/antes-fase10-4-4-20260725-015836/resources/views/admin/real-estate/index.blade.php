@extends('layouts.app', ['title' => 'Imóveis - Admin'])

@section('content')
    <style>
        .content-band-actions {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .content-band-actions .primary-action {
            flex: 0 0 auto;
            text-decoration: none;
        }
    </style>
    <section class="content-band">
        <div class="content-band-actions">
            <div>
                <p class="eyebrow">Administração</p>
                <h1>Imóveis</h1>
                <p>Acompanhe anúncios de compra, venda e aluguel, pause publicações e marque imóveis em destaque.</p>
            </div>

            <a class="primary-action" href="{{ route('real-estate.create') }}">
                Cadastrar imóvel
            </a>
        </div>
    </section>

    <section class="settings-panel">
        <div class="admin-vehicle-list">
            @forelse($properties as $property)
                <article class="admin-vehicle-row">
                    <div>
                        <strong>{{ $property->title }}</strong>
                        <span>{{ $purposeLabels[$property->purpose] ?? 'Imóvel' }} • {{ $propertyTypeLabels[$property->property_type] ?? 'Imóvel' }} • {{ $property->city }}/{{ $property->state }} • {{ $property->user?->name }}</span>
                    </div>

                    <form method="post" action="{{ route('admin.real-estate.update', $property) }}" class="admin-inline-form">
                        @csrf
                        @method('put')

                        <label>
                            Status
                            <select name="status">
                                <option value="published" @selected($property->status === 'published')>Publicado</option>
                                <option value="paused" @selected($property->status === 'paused')>Pausado</option>
                                <option value="deal_done" @selected($property->status === 'deal_done')>Negócio fechado</option>
                            </select>
                        </label>

                        <label class="toggle-row compact-toggle">
                            <input type="checkbox" name="is_featured" value="1" @checked($property->is_featured)>
                            <span>Destaque</span>
                        </label>

                        <button class="secondary-action" type="submit">Atualizar</button>
                    </form>
                </article>
            @empty
                <p>Nenhum imóvel cadastrado ainda.</p>
            @endforelse
        </div>
    </section>
@endsection
