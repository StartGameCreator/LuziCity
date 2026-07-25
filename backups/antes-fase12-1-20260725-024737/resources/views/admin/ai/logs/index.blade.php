@extends('layouts.app')
@section('title','Logs da IA')
@section('content')
<section class="admin-page">@include('admin.ai.partials.navigation')<div class="section-heading"><div><span class="eyebrow">Auditoria</span><h1>Logs de IA</h1></div><a href="{{ route('admin.ai.costs.index') }}">Custos</a></div>
<form method="get" class="admin-card" style="padding:1rem"><input name="feature" placeholder="Recurso" value="{{ request('feature') }}"><select name="status"><option value="">Todos</option>@foreach(['completed','failed','running'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select><button>Filtrar</button></form>
<section class="admin-card" style="padding:1rem"><table class="admin-table"><tr><th>Data</th><th>Recurso</th><th>Provedor</th><th>Usuário</th><th>Status</th><th></th></tr>@forelse($logs as $log)<tr><td>{{ $log->created_at?->format('d/m/Y H:i') }}</td><td>{{ $log->feature }}</td><td>{{ $log->provider?->name ?? '—' }}</td><td>{{ $log->user?->name ?? 'Sistema' }}</td><td>{{ $log->status }}</td><td><a href="{{ route('admin.ai.logs.show',$log) }}">Detalhes</a></td></tr>@empty<tr><td colspan="6">Sem logs.</td></tr>@endforelse</table>{{ $logs->links() }}</section></section>
@endsection
