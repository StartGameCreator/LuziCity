@extends('layouts.app', ['title' => 'Configuração da Rádio - Luzicity'])

@section('content')
    <section class="settings-panel azuracast-admin" aria-label="Motor AzuraCast">
        <div class="radio-panel-head">
            <div>
                <p class="eyebrow">Motor nativo da rádio</p>
                <h2>AzuraCast</h2>
                <p>{{ $azuraCastHealth['message'] }}</p>
            </div>
            <span @class(['broadcast-health-badge', 'is-online' => $azuraCastHealth['connected']])>
                {{ $azuraCastHealth['connected'] ? 'Conectado' : 'Desconectado' }}
            </span>
        </div>

        <div class="system-health-stats">
            <article class="system-health-stat">
                <span>Backend</span>
                <strong>{{ $azuraCastHealth['base_url'] ?: 'Não configurado' }}</strong>
            </article>
            <article class="system-health-stat">
                <span>Latência</span>
                <strong>{{ $azuraCastHealth['latency_ms'] !== null ? $azuraCastHealth['latency_ms'].' ms' : '—' }}</strong>
            </article>
            <article class="system-health-stat">
                <span>Ouvintes</span>
                <strong>{{ data_get($azuraCastNowPlaying, 'listeners', 0) }}</strong>
            </article>
            <article class="system-health-stat">
                <span>Estado</span>
                <strong>{{ data_get($azuraCastNowPlaying, 'is_online') ? 'No ar' : 'Offline' }}</strong>
            </article>
        </div>

        <div class="azuracast-now-playing">
            @if(data_get($azuraCastNowPlaying, 'art'))
                <img src="{{ data_get($azuraCastNowPlaying, 'art') }}" alt="Capa da faixa atual">
            @endif
            <div>
                <p class="eyebrow">No ar pelo AzuraCast</p>
                <h3>{{ data_get($azuraCastNowPlaying, 'title') ?: 'Aguardando a primeira emissora' }}</h3>
                <p>{{ data_get($azuraCastNowPlaying, 'artist') ?: 'Configure a emissora e a API Key para exibir os metadados.' }}</p>
            </div>
        </div>

        <div class="azuracast-controls">
            <form method="post" action="{{ route('admin.radio.azuracast.test') }}">
                @csrf
                <button class="secondary-action" type="submit">Testar conexão</button>
            </form>
            @foreach(['start' => 'Iniciar', 'restart' => 'Reiniciar', 'stop' => 'Parar'] as $action => $label)
                <form method="post" action="{{ route('admin.radio.azuracast.control', $action) }}">
                    @csrf
                    <button class="secondary-action" type="submit" @disabled(! $azuraCastHealth['configured'])>{{ $label }}</button>
                </form>
            @endforeach
            <a class="secondary-action" href="{{ route('radio.index') }}" target="_blank">Abrir saída pública</a>
        </div>

        @if(! $azuraCastHealth['configured'])
            <p class="form-help">A interface já está incorporada. Para liberar controles administrativos, configure AZURACAST_API_KEY, AZURACAST_STATION_ID e AZURACAST_STATION_SHORTCODE no ambiente do servidor.</p>
        @endif
    </section>

    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Rádio Web</h1>
        <p>Configure a live do TikTok, o streaming de áudio, transmissões externas pelo smartphone e acompanhe pedidos enviados pelos ouvintes.</p>
    </section>

    <section class="settings-panel" aria-label="Configuração da rádio">
        <form method="post" action="{{ route('admin.radio.update') }}" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <label>
                Link direto do TikTok
                <input type="url" name="tiktok_url" value="{{ old('tiktok_url', $radioSettings['tiktok_url'] ?? '') }}" placeholder="https://www.tiktok.com/@canal/live">
            </label>

            <label>
                Formato da transmissão TikTok
                <select name="tiktok_orientation">
                    <option value="portrait" @selected(old('tiktok_orientation', $radioSettings['tiktok_orientation'] ?? 'portrait') === 'portrait')>Vertical/retrato - melhor para smartphone</option>
                    <option value="landscape" @selected(old('tiktok_orientation', $radioSettings['tiktok_orientation'] ?? 'portrait') === 'landscape')>Horizontal/paisagem - melhor para live deitada</option>
                </select>
            </label>

            <label>
                Código iframe/embed do TikTok
                <textarea name="tiktok_embed_code" rows="7" placeholder='<iframe src="..."></iframe>'>{{ old('tiktok_embed_code', $radioSettings['tiktok_embed_code'] ?? '') }}</textarea>
            </label>

            <label>
                URL do streaming de áudio
                <input type="url" name="audio_stream_url" value="{{ old('audio_stream_url', $radioSettings['audio_stream_url'] ?? '') }}" placeholder="https://.../stream.mp3">
            </label>

            <label>
                Texto da programação
                <textarea name="schedule_text" rows="4">{{ old('schedule_text', $radioSettings['schedule_text'] ?? '') }}</textarea>
            </label>

            <div class="ad-settings-panel">
                <div>
                    <p class="eyebrow">Transmissão da rua</p>
                    <h2>Ferramentas para cobertura externa</h2>
                    <p>Use estes campos quando a equipe estiver em evento, campanha ou reportagem externa com smartphone.</p>
                </div>

                <label class="inline-check">
                    <input type="checkbox" name="field_live_enabled" value="1" @checked(old('field_live_enabled', $radioSettings['field_live_enabled'] ?? false))>
                    Ativar cobertura externa ao vivo na página da rádio
                </label>

                <div class="social-settings-grid">
                    <label>
                        Título da cobertura
                        <input name="field_live_title" value="{{ old('field_live_title', $radioSettings['field_live_title'] ?? '') }}" placeholder="Cobertura externa ao vivo">
                    </label>

                    <label>
                        Link público da live externa
                        <input type="url" name="field_video_url" value="{{ old('field_video_url', $radioSettings['field_video_url'] ?? '') }}" placeholder="https://...">
                    </label>

                    <label>
                        URL de áudio externo
                        <input type="url" name="field_audio_stream_url" value="{{ old('field_audio_stream_url', $radioSettings['field_audio_stream_url'] ?? '') }}" placeholder="https://.../stream.mp3">
                    </label>

                    <label>
                        WhatsApp da produção/repórter
                        <input name="field_reporter_whatsapp" value="{{ old('field_reporter_whatsapp', $radioSettings['field_reporter_whatsapp'] ?? '') }}" placeholder="(61) 00000-0000">
                    </label>

                    <label>
                        Link de retorno para a equipe
                        <input type="url" name="field_return_link" value="{{ old('field_return_link', $radioSettings['field_return_link'] ?? '') }}" placeholder="https://meet.google.com/...">
                    </label>

                    <label>
                        Servidor RTMP para app no smartphone
                        <input name="field_rtmp_server" value="{{ old('field_rtmp_server', $radioSettings['field_rtmp_server'] ?? '') }}" placeholder="rtmp://servidor/app">
                    </label>

                    <label>
                        Chave/stream key RTMP
                        <input name="field_rtmp_key" value="{{ old('field_rtmp_key', $radioSettings['field_rtmp_key'] ?? '') }}" placeholder="chave-da-live">
                    </label>
                </div>

                <label>
                    Iframe/embed da transmissão externa
                    <textarea name="field_video_embed_code" rows="6" placeholder='<iframe src="..."></iframe>'>{{ old('field_video_embed_code', $radioSettings['field_video_embed_code'] ?? '') }}</textarea>
                </label>

                <label>
                    Descrição para o público
                    <textarea name="field_live_description" rows="3">{{ old('field_live_description', $radioSettings['field_live_description'] ?? '') }}</textarea>
                </label>

                <label>
                    Notas internas para a equipe
                    <textarea name="field_team_notes" rows="5" placeholder="Checklist, app recomendado, orientação de internet, microfone, bateria, ponto de encontro...">{{ old('field_team_notes', $radioSettings['field_team_notes'] ?? '') }}</textarea>
                </label>
            </div>

            <button class="secondary-action" type="submit">Salvar rádio</button>
        </form>
    </section>

    <section class="category-admin-list" aria-label="Pedidos enviados para a rádio">
        <div class="section-heading">
            <p class="eyebrow">Participação</p>
            <h2>Pedidos recentes</h2>
        </div>

        @foreach($requests as $request)
            <article class="settings-panel category-admin-card">
                <p class="eyebrow">{{ $request->created_at->format('d/m/Y H:i') }}</p>
                <h3>{{ $request->name ?: 'Ouvinte' }}{{ $request->city ? ' - '.$request->city : '' }}</h3>
                <small>Sala {{ $request->categoryLabel() }}{{ $request->city ? ' | '.$request->city : '' }}{{ $request->recipient_name ? ' | Para '.$request->recipient_name : '' }}{{ $request->is_private ? ' | Reservado' : '' }}</small>
                <p>{{ $request->message }}</p>
                @if($request->is_private && $request->private_contact)
                    <small>Contato reservado informado: {{ $request->private_contact }}</small>
                @endif
                @if($request->attachment_path && $request->attachment_type === 'image')
                    <img class="admin-chat-attachment" src="{{ asset('storage/'.$request->attachment_path) }}" alt="Imagem enviada no chat da radio">
                @endif
                @if($request->phone)
                    <small>Contato: {{ $request->phone }}</small>
                @endif
            </article>
        @endforeach
    </section>
@endsection
