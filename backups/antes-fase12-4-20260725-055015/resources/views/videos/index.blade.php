@extends('layouts.app',['title'=>'Videoteca - Luzicity'])
@section('content')<section class="content-band"><p class="eyebrow">TV Web</p><h1>Videoteca</h1><p>Reportagens, programas, séries e playlists.</p></section>
@foreach($seriesList as $series)<section class="settings-panel"><h2>{{ $series->title }}</h2><p>{{ $series->description }} · {{ $series->videos_count }} vídeos</p></section>@endforeach
<section class="category-admin-list">@forelse($videos as $video)<article class="settings-panel">@if($video->thumbnail_path)<img src="{{ asset('storage/'.$video->thumbnail_path) }}" alt="" style="max-width:280px">@endif<p class="eyebrow">{{ $video->category?->name }}</p><h2><a href="{{ route('videos.show',$video) }}">{{ $video->title }}</a></h2><p>{{ $video->description }}</p></article>@empty<p>Nenhum vídeo publicado.</p>@endforelse</section>{{ $videos->links() }}
@foreach($playlists as $playlist)<section class="settings-panel"><h2>{{ $playlist->title }}</h2><p>{{ $playlist->description }}</p>@foreach($playlist->videos as $video)<a href="{{ route('videos.show',$video) }}">{{ $video->title }}</a><br>@endforeach</section>@endforeach
@endsection
