@extends('layouts.app')

@section('content')
<section class="admin-card">
    <p class="eyebrow">PWA avançado</p>
    <h1>Notificações Push</h1>
    <p>Dispositivos cadastrados: <strong>{{ $subscriptions->total() }}</strong></p>
    @unless($firebaseConfigured)
        <div class="notice notice-error">Configure as variáveis FIREBASE_* no arquivo .env antes de enviar.</div>
    @endunless
    <form method="post" action="{{ route('admin.push-notifications.send') }}" class="stack-form">
        @csrf
        <label>Título<input name="title" maxlength="120" required value="{{ old('title') }}"></label>
        <label>Mensagem<textarea name="body" maxlength="500" required>{{ old('body') }}</textarea></label>
        <label>Link ao abrir<input name="url" value="{{ old('url', '/') }}" placeholder="/noticias/minha-noticia"></label>
        <button type="submit" @disabled(!$firebaseConfigured)>Enviar para todos</button>
    </form>
</section>
<section class="admin-card">
    <h2>Dispositivos inscritos</h2>
    <div class="table-scroll"><table><thead><tr><th>Usuário</th><th>Dispositivo</th><th>Plataforma</th><th>Último acesso</th></tr></thead><tbody>
    @forelse($subscriptions as $subscription)
        <tr><td>{{ $subscription->user?->email ?? 'Visitante' }}</td><td>{{ $subscription->device_name ?: 'N/D' }}</td><td>{{ $subscription->platform ?: 'N/D' }}</td><td>{{ optional($subscription->last_seen_at)->format('d/m/Y H:i') }}</td></tr>
    @empty
        <tr><td colspan="4">Nenhum dispositivo inscrito.</td></tr>
    @endforelse
    </tbody></table></div>
    {{ $subscriptions->links() }}
</section>
@endsection
