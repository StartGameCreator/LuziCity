@extends('layouts.app', ['title' => 'Login Social - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Login Social</h1>
        <p>Configure as chaves de acesso usadas nos botões de entrada e cadastro social da Luzicity.</p>
    </section>

    <section class="settings-panel" aria-label="Configurações de login social">
        <form method="post" action="{{ route('admin.social-login.update') }}" class="admin-form">
            @csrf
            @method('put')

            <div class="social-login-settings">
                @foreach($providers as $key => $provider)
                    <article class="ad-settings-panel">
                        <div class="social-login-card-head">
                            <div>
                                <p class="eyebrow">{{ $provider['label'] }}</p>
                                <h2>{{ $provider['label'] }}</h2>
                            </div>

                            <label class="inline-check">
                                <input type="checkbox" name="{{ $key }}[enabled]" value="1" @checked($provider['enabled'])>
                                Ativo no login
                            </label>
                        </div>

                        <div class="social-settings-grid">
                            <label>
                                Client ID
                                <input name="{{ $key }}[client_id]" value="{{ old("{$key}.client_id", $provider['client_id']) }}" placeholder="Cole aqui o Client ID">
                            </label>

                            <label>
                                Client Secret
                                <input type="password" name="{{ $key }}[client_secret]" placeholder="{{ filled($provider['client_secret']) ? 'Secret já cadastrado. Preencha só para trocar.' : 'Cole aqui o Client Secret' }}">
                            </label>
                        </div>

                        <label>
                            URL de retorno
                            <input name="{{ $key }}[redirect]" value="{{ old("{$key}.redirect", $provider['redirect']) }}" placeholder="{{ url("/login/{$key}/callback") }}">
                        </label>
                    </article>
                @endforeach
            </div>

            <button class="secondary-action" type="submit">Salvar login social</button>
        </form>
    </section>
@endsection
