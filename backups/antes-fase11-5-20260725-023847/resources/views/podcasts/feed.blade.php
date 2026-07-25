{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"><channel>
<title>{{ $series->title }}</title><link>{{ route('podcasts.index') }}</link><description>{{ $series->description }}</description><language>{{ $series->language }}</language><itunes:author>{{ $series->author }}</itunes:author>
@foreach($series->publishedEpisodes as $episode)<item><title>{{ $episode->title }}</title><description>{{ $episode->description }}</description><guid isPermaLink="false">podcast-episode-{{ $episode->id }}</guid><pubDate>{{ $episode->published_at->toRfc2822String() }}</pubDate><enclosure url="{{ $episode->audioUrl() }}" length="{{ $episode->audio_bytes ?: 0 }}" type="{{ $episode->audio_mime }}"/></item>@endforeach
</channel></rss>
