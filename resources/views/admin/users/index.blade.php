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
                    @if($currentSite)
                    <fieldset><legend>Permissões em {{ $currentSite->name }}</legend>
                        @foreach(['manage_content'=>'Conteúdo','manage_users'=>'Usuários','manage_media'=>'Mídia','manage_ads'=>'Anúncios'] as $permission=>$label)
                        <label class="inline-check"><input type="checkbox" name="site_permissions[]" value="{{ $permission }}" @checked(in_array($permission,$user->sitePermissions($currentSite),true))> {{ $label }}</label>
                        @endforeach
                        <small>Sem seleção mantém as permissões herdadas dos papéis atuais.</small>
                    </fieldset>
                    @endif

                    <label>
                        Assinatura
                        <select name="subscription_status">
                            @foreach(['inactive' => 'Nao assinante', 'active' => 'Assinante ativo', 'expired' => 'Expirado', 'suspended' => 'Suspenso'] as $value => $label)
                                <option value="{{ $value }}" @selected(($user->subscription?->status ?? 'inactive') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Plano<select name="subscription_plan_id"><option value="">Legado / sem plano</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected($user->subscription?->subscription_plan_id===$plan->id)>{{ $plan->name }}</option>@endforeach</select></label>
                    <label>Ciclo<select name="billing_cycle"><option value="monthly" @selected(($user->subscription?->billing_cycle??'monthly')==='monthly')>Mensal</option><option value="yearly" @selected($user->subscription?->billing_cycle==='yearly')>Anual</option></select></label>

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
