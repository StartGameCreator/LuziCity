@extends('layouts.app', ['title' => 'Classificados de Veículos - Admin'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Classificados de Veículos</h1>
        <p>Configure o limite de anúncios por usuário, as marcas exibidas no canal e acompanhe os veículos publicados.</p>
    </section>

    <section class="settings-panel">
        <form method="post" action="{{ route('admin.vehicles.settings.update') }}" class="admin-form">
            @csrf
            @method('put')

            <label class="toggle-row">
                <input type="checkbox" name="limit_enabled" value="1" @checked($settings['limit_enabled'])>
                <span>
                    Limitar quantidade de anúncios por usuário
                    <small>Desligado: qualquer usuário logado pode anunciar sem limite. Ligado: aplica a quantidade abaixo.</small>
                </span>
            </label>

            <label>
                Quantidade máxima de anúncios ativos por usuário
                <input type="number" min="1" max="999" name="max_active_listings" value="{{ old('max_active_listings', $settings['max_active_listings']) }}" required>
            </label>

            @foreach($vehicleTypes as $typeKey => $typeLabel)
                <label>
                    Marcas de {{ $typeLabel }}
                    <textarea name="brand_logos[{{ $typeKey }}]" rows="7" placeholder="Honda|https://site.com/honda.png">{{ old("brand_logos.$typeKey", $brandLogosText[$typeKey] ?? '') }}</textarea>
                    <small>Use uma marca por linha. Formato: Nome|URL da logo. Se deixar sem URL, o sistema tenta montar a logo automaticamente.</small>
                </label>
            @endforeach

            <button class="secondary-action" type="submit">Salvar configuração</button>
        </form>
    </section>

    <section class="settings-panel">
        <div class="admin-section-head">
            <p class="eyebrow">Upload de logos</p>
            <h2>Enviar arquivo de marca</h2>
            <p>Envie PNG, JPG, JPEG ou WEBP. O sistema salva o arquivo e atualiza automaticamente a lista do tipo escolhido.</p>
        </div>

        <form method="post" action="{{ route('admin.vehicles.logos.upload') }}" enctype="multipart/form-data" class="admin-form">
            @csrf

            <div class="form-grid">
                <label>
                    Tipo de veículo
                    <select name="vehicle_type" required>
                        @foreach($vehicleTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Nome da marca
                    <input name="brand_name" required placeholder="Ex: Toyota">
                </label>

                <label>
                    Arquivo da logo
                    <input type="file" name="brand_logo" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                </label>
            </div>

            <button class="secondary-action" type="submit">Enviar logo</button>
        </form>
    </section>

    <section class="settings-panel">
        <div class="admin-section-head">
            <p class="eyebrow">Anúncios recentes</p>
            <h2>Veículos cadastrados</h2>
        </div>

        <div class="admin-vehicle-list">
            @forelse($vehicles as $vehicle)
                <article class="admin-vehicle-row">
                    <div>
                        <strong>{{ $vehicle->title }}</strong>
                        <span>{{ $vehicle->brand }} {{ $vehicle->model }} • {{ $vehicle->year }} • {{ $vehicle->user?->name }}</span>
                    </div>

                    <form method="post" action="{{ route('admin.vehicles.update', $vehicle) }}" class="admin-inline-form">
                        @csrf
                        @method('put')

                        <label>
                            Status
                            <select name="status">
                                <option value="published" @selected($vehicle->status === 'published')>Publicado</option>
                                <option value="paused" @selected($vehicle->status === 'paused')>Pausado</option>
                                <option value="sold" @selected($vehicle->status === 'sold')>Vendido</option>
                            </select>
                        </label>

                        <label class="toggle-row compact-toggle">
                            <input type="checkbox" name="is_featured" value="1" @checked($vehicle->is_featured)>
                            <span>Destaque</span>
                        </label>

                        <button class="secondary-action" type="submit">Atualizar</button>
                    </form>
                </article>
            @empty
                <p>Nenhum veículo cadastrado ainda.</p>
            @endforelse
        </div>
    </section>
@endsection
