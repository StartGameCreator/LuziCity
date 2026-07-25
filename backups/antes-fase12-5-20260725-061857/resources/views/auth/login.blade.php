@extends('layouts.app', ['title' => 'Entrar - Luzicity'])

@section('content')
    <section class="auth-grid">
        <div>
            <p class="eyebrow">Acesso Luzicity</p>
            <h1>Entre para acompanhar noticias, comentar e acessar beneficios.</h1>
            <p>Assinantes navegam sem anuncios. Jornalistas, colunistas e anunciantes recebem areas proprias quando o administrador liberar.</p>
        </div>

        <div class="auth-panel" aria-label="Formulario de login">
            <h2>Entrar ou cadastrar</h2>
            <p class="auth-helper">Use uma rede social para entrar. Se for seu primeiro acesso, sua conta será criada automaticamente.</p>

            <div class="social-list" aria-label="Entrar ou cadastrar com login social">
                @foreach($providers as $key => $provider)
                    <a class="social-button social-button-{{ $key }}" href="{{ route('social.redirect', $key) }}">
                        <span class="social-icon" aria-hidden="true">
                            <x-social-icon :provider="$key" />
                        </span>
                        <span>
                            <strong>Entrar ou cadastrar</strong>
                            <small>com {{ $provider['label'] }}</small>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="divider">ou entre com e-mail</div>

            <form method="post" action="{{ route('login.store') }}" class="stacked-form">
                @csrf
                <label>
                    E-mail
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                </label>
                <label>
                    Senha
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <label class="inline-check">
                    <input type="checkbox" name="remember" value="1">
                    Manter conectado
                </label>
                <button class="primary-action" type="submit">Entrar</button>
            </form>

            <details class="register-box">
                <summary>Cadastrar com e-mail</summary>
                <form method="post" action="{{ route('register.store') }}" class="stacked-form">
                    @csrf
                    <label>
                        Nome
                        <input type="text" name="name" autocomplete="name" required>
                    </label>
                    <label>
                        E-mail
                        <input type="email" name="email" autocomplete="email" required>
                    </label>
                    <label>
                        Senha
                        <input type="password" name="password" autocomplete="new-password" required>
                    </label>
                    <label>
                        Confirmar senha
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                    <button class="secondary-action" type="submit">Criar conta</button>
                </form>
            </details>
        </div>
    </section>
@endsection
