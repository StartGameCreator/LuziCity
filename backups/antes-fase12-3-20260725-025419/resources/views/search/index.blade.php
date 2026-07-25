@extends('layouts.app', ['title' => ($term ? 'Busca por '.$term : 'Busca').' - LuziCity'])

@section('content')
<section class="search-hero">
    <p class="eyebrow">Busca inteligente</p>
    <h1>Encontre tudo no LuziCity</h1>
    <p>Pesquise simultaneamente em notícias, imóveis e classificados de veículos.</p>

    <form method="get" action="{{ route('search.index') }}" class="unified-search-form" role="search">
        <label class="sr-only" for="unified-q">O que você procura?</label>
        <input id="unified-q" name="q" value="{{ $term }}" placeholder="Digite notícia, bairro, veículo, cidade..." maxlength="100" autocomplete="off" autofocus>
        <select name="type" aria-label="Tipo de conteúdo">
            <option value="all" @selected($type === 'all')>Tudo</option>
            <option value="news" @selected($type === 'news')>Notícias</option>
            <option value="properties" @selected($type === 'properties')>Imóveis</option>
            <option value="vehicles" @selected($type === 'vehicles')>Veículos</option>
        </select>
        <button type="submit">Buscar</button>
    </form>
    <div id="search-suggestions" class="search-suggestions" hidden></div>
</section>

@if($term === '')
    <section class="search-empty"><h2>Digite algo para começar</h2><p>A busca aceita títulos, descrições, cidades, bairros, marcas e modelos.</p></section>
@elseif($total === 0)
    <section class="search-empty"><h2>Nenhum resultado para “{{ $term }}”</h2><p>Tente palavras menores, outra grafia ou escolha “Tudo”.</p></section>
@else
    <div class="search-summary"><strong>{{ $total }}</strong> {{ $total === 1 ? 'resultado encontrado' : 'resultados encontrados' }} para “{{ $term }}”</div>

    @if($results['news']->isNotEmpty())
        <section class="search-group"><div class="search-group-head"><h2>Notícias</h2><span>{{ $results['news']->count() }}</span></div>
            <div class="search-results-grid">
                @foreach($results['news'] as $item)
                    <a class="search-result-card" href="{{ route('news.show', $item) }}">
                        <small>{{ $item->category?->name ?? 'Notícia' }}</small><h3>{{ $item->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($item->excerpt ?: $item->body), 150) }}</p>
                        <time>{{ optional($item->published_at)->format('d/m/Y H:i') }}</time>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($results['properties']->isNotEmpty())
        <section class="search-group"><div class="search-group-head"><h2>Imóveis</h2><span>{{ $results['properties']->count() }}</span></div>
            <div class="search-results-grid">
                @foreach($results['properties'] as $item)
                    <a class="search-result-card" href="{{ route('real-estate.show', $item) }}">
                        <small>Imóvel • {{ $item->city }}/{{ $item->state }}</small><h3>{{ $item->title }}</h3>
                        <p>{{ $item->neighborhood }}{{ $item->description ? ' — '.\Illuminate\Support\Str::limit(strip_tags($item->description), 120) : '' }}</p>
                        <strong>{{ $item->price ? 'R$ '.number_format((float)$item->price, 2, ',', '.') : 'Preço a combinar' }}</strong>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($results['vehicles']->isNotEmpty())
        <section class="search-group"><div class="search-group-head"><h2>Veículos</h2><span>{{ $results['vehicles']->count() }}</span></div>
            <div class="search-results-grid">
                @foreach($results['vehicles'] as $item)
                    <a class="search-result-card" href="{{ route('vehicles.show', $item) }}">
                        <small>Veículo • {{ $item->city }}/{{ $item->state }}</small><h3>{{ $item->title }}</h3>
                        <p>{{ trim($item->brand.' '.$item->model) }}{{ $item->year ? ' • '.$item->year : '' }}</p>
                        <strong>{{ $item->price ? 'R$ '.number_format((float)$item->price, 2, ',', '.') : 'Preço a combinar' }}</strong>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endif

<style>
.search-hero{position:relative;padding:34px;border:1px solid #e5e7eb;border-radius:20px;background:linear-gradient(135deg,#fff,#f8fafc);box-shadow:0 18px 50px rgba(15,23,42,.07)}
.search-hero h1{font-size:clamp(1.8rem,4vw,3rem);margin:.25rem 0}.search-hero>p{color:#64748b}.unified-search-form{display:grid;grid-template-columns:1fr 180px auto;gap:10px;margin-top:22px}.unified-search-form input,.unified-search-form select{min-height:52px;border:1px solid #cbd5e1;border-radius:12px;padding:0 15px;background:#fff}.unified-search-form button{border:0;border-radius:12px;padding:0 24px;background:#0f3f78;color:#fff;font-weight:800}.search-summary{margin:24px 0 8px;color:#475569}.search-group{margin-top:30px}.search-group-head{display:flex;align-items:center;gap:10px;margin-bottom:13px}.search-group-head h2{margin:0}.search-group-head span{display:grid;place-items:center;min-width:28px;height:28px;border-radius:999px;background:#f59e0b;color:#fff;font-weight:800}.search-results-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.search-result-card{display:flex;flex-direction:column;gap:8px;padding:20px;border:1px solid #e2e8f0;border-radius:16px;background:#fff;color:inherit;text-decoration:none;transition:.18s}.search-result-card:hover{transform:translateY(-3px);box-shadow:0 15px 35px rgba(15,23,42,.10);border-color:#93c5fd}.search-result-card small,.search-result-card time{color:#64748b}.search-result-card h3{margin:0;font-size:1.05rem}.search-result-card p{margin:0;color:#475569;line-height:1.55}.search-result-card strong{margin-top:auto;color:#0f3f78}.search-empty{text-align:center;padding:60px 20px}.search-suggestions{position:absolute;z-index:20;left:34px;right:34px;margin-top:6px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;box-shadow:0 20px 45px rgba(15,23,42,.15);overflow:hidden}.search-suggestions a{display:flex;justify-content:space-between;padding:12px 15px;color:#0f172a;text-decoration:none;border-bottom:1px solid #f1f5f9}.search-suggestions a:hover{background:#f8fafc}.search-suggestions small{color:#64748b}@media(max-width:850px){.unified-search-form{grid-template-columns:1fr}.unified-search-form button{min-height:50px}.search-results-grid{grid-template-columns:1fr}.search-hero{padding:22px}.search-suggestions{left:22px;right:22px}}
</style>

<script>
(() => {
 const input=document.getElementById('unified-q'), box=document.getElementById('search-suggestions'); if(!input||!box)return;
 let timer, controller;
 input.addEventListener('input',()=>{clearTimeout(timer); const q=input.value.trim(); if(q.length<2){box.hidden=true;box.innerHTML='';return;} timer=setTimeout(async()=>{try{controller?.abort();controller=new AbortController();const r=await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(q)}`,{signal:controller.signal,headers:{Accept:'application/json'}});if(!r.ok)return;const j=await r.json();box.innerHTML=(j.data||[]).map(x=>`<a href="${x.url}"><span>${escapeHtml(x.title)}</span><small>${escapeHtml(x.type)}</small></a>`).join('');box.hidden=!box.innerHTML;}catch(e){if(e.name!=='AbortError')box.hidden=true;}},250)});
 document.addEventListener('click',e=>{if(!box.contains(e.target)&&e.target!==input)box.hidden=true});
 function escapeHtml(v){const d=document.createElement('div');d.textContent=v??'';return d.innerHTML}
})();
</script>
@endsection
