@extends('layouts.app', ['title' => 'Usuarios - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administracao</p>
        <h1>Usuarios</h1>
        <p>Defina assinantes, jornalistas, colunistas, anunciantes e demais papeis de acesso.</p>
    </section>

    <section class="user-list" aria-label="Lista de usuarios">
        @foreach($users as $user)
            <article class="user-card">
                <div>
                    <h2>{{ $user->name }}</h2>
                    <p>{{ $user->email }}</p>
                    <p>Status: {{ $user->subscription?->status ?? 'inactive' }}</p>
                </div>

                <form method="post" action="{{ route('admin.users.update', $user) }}" class="admin-form">
                    @csrf
                    @method('put')

                    <fieldset>
                        <legend>Papeis</legend>
                        <div class="role-grid">
                            @foreach($roles as $role)
                                <label class="inline-check">
                                    <input type="checkbox" name="roles[]" value="{{ $role }}" @checked($user->hasRole($role))>
                                    {{ $role }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <label>
                        Assinatura
                        <select name="subscription_status">
                            @foreach(['inactive' => 'Nao assinante', 'active' => 'Assinante ativo', 'expired' => 'Expirado', 'suspended' => 'Suspenso'] as $value => $label)
                                <option value="{{ $value }}" @selected(($user->subscription?->status ?? 'inactive') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Fim da assinatura
                        <input type="datetime-local" name="subscription_ends_at" value="{{ $user->subscription?->ends_at?->format('Y-m-d\TH:i') }}">
                    </label>

                    <button class="secondary-action" type="submit">Salvar usuario</button>
                </form>
            </article>
        @endforeach

        {{ $users->links() }}
    </section>
@endsection
