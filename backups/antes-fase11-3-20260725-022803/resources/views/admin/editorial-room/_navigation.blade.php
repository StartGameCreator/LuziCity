<nav class="admin-card editorial-room-nav" aria-label="Sala de Redação">
<a @class(['active'=>request()->routeIs('admin.editorial-room.*')]) href="{{ route('admin.editorial-room.dashboard') }}">Visão geral</a>
<a @class(['active'=>request()->routeIs('admin.editorial-pitches.*')]) href="{{ route('admin.editorial-pitches.index') }}">Kanban</a>
<a @class(['active'=>request()->routeIs('admin.ai-agents.*')]) href="{{ route('admin.ai-agents.index') }}">Agentes</a>
<a href="{{ route('admin.editorial-pitches.index') }}">Fontes e verificação</a>
<a href="{{ route('admin.news.index') }}">Aprovação</a>
<a @class(['active'=>request()->routeIs('admin.editorial-calendar.*')]) href="{{ route('admin.editorial-calendar.index') }}">Calendário</a>
</nav><style>.editorial-room-nav{display:flex;gap:.45rem;flex-wrap:wrap;padding:.65rem;margin-bottom:1rem}.editorial-room-nav a{padding:.6rem .8rem;border-radius:.55rem;font-weight:700}.editorial-room-nav a.active,.editorial-room-nav a:hover{background:var(--surface-soft);color:var(--primary)}</style>
