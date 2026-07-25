@php
    $isGlobalAdmin = auth()->user()?->hasAnyRole(['Super Admin', 'Admin']);
@endphp
<nav class="admin-card ai-central-nav" aria-label="Navegação da Central Editorial IA">
    <a @class(['active' => request()->routeIs('admin.ai.dashboard*')]) href="{{ route('admin.ai.dashboard') }}">Visão geral</a>
    <a @class(['active' => request()->routeIs('admin.news.ai.*')]) href="{{ route('admin.news.ai.create') }}">Gerar notícia</a>
    @if($isGlobalAdmin)
        <a @class(['active' => request()->routeIs('admin.ai.prompts.*')]) href="{{ route('admin.ai.prompts.index') }}">Prompts</a>
        <a @class(['active' => request()->routeIs('admin.ai.memory.*')]) href="{{ route('admin.ai.memory.index') }}">Memória</a>
        <a @class(['active' => request()->routeIs('admin.ai.providers.*')]) href="{{ route('admin.ai.providers.index') }}">Provedores</a>
        <a @class(['active' => request()->routeIs('admin.ai.costs.*')]) href="{{ route('admin.ai.costs.index') }}">Custos</a>
        <a @class(['active' => request()->routeIs('admin.ai.logs.*')]) href="{{ route('admin.ai.logs.index') }}">Logs</a>
    @endif
</nav>
<style>
.ai-central-nav{display:flex;gap:.45rem;flex-wrap:wrap;padding:.65rem;margin-bottom:1rem}
.ai-central-nav a{padding:.6rem .8rem;border-radius:.55rem;font-weight:700}
.ai-central-nav a:hover,.ai-central-nav a.active{background:var(--surface-soft);color:var(--primary)}
</style>
