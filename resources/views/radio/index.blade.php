@extends('layouts.app', ['title' => 'Radio Web - Luzicity'])

@section('content')
    @php
        $tiktokUrl = trim((string) data_get($radioSettings, 'tiktok_url', ''));
        $audioStreamUrl = trim((string) data_get($radioPlayback, 'stream_url', data_get($radioSettings, 'audio_stream_url', '')));
        $hasTiktokUrl = filled($tiktokUrl);
        $hasAudioStream = filled($audioStreamUrl);
        $fieldLiveEnabled = filter_var(data_get($radioSettings, 'field_live_enabled'), FILTER_VALIDATE_BOOLEAN);
        $fieldVideoUrl = trim((string) data_get($radioSettings, 'field_video_url', ''));
        $fieldAudioStreamUrl = trim((string) data_get($radioSettings, 'field_audio_stream_url', ''));
        $fieldReporterWhatsapp = preg_replace('/\D+/', '', (string) data_get($radioSettings, 'field_reporter_whatsapp', ''));
        $fieldReturnLink = trim((string) data_get($radioSettings, 'field_return_link', ''));
    @endphp

    @include('radio._audio_ad')
    @include('radio._structured_schedule')

    <section class="radio-hero-grid" aria-label="Radio Web Luzicity">
        <div class="content-band radio-page">
            <p class="eyebrow">Ao vivo</p>
            <h1>Radio Web Luzicity</h1>
            <p>{{ data_get($radioSettings, 'schedule_text') }}</p>
            <div class="radio-native-now-playing" data-radio-native-state data-state-url="{{ route('radio.state') }}">
                <span class="live-dot" aria-hidden="true"></span>
                <div>
                    <small data-radio-native-station>{{ data_get($radioPlayback, 'station') }}</small>
                    <strong data-radio-native-title>{{ data_get($radioPlayback, 'title') ?: 'Rádio Web Luzicity' }}</strong>
                    <span data-radio-native-artist>{{ data_get($radioPlayback, 'artist') ?: (data_get($radioPlayback, 'source') === 'azuracast' ? 'Transmissão ao vivo' : 'Streaming de contingência') }}</span>
                </div>
                <span class="radio-native-listeners" data-radio-native-listeners>{{ data_get($radioPlayback, 'listeners', 0) }} ouvintes</span>
            </div>
        </div>

        <aside class="radio-hero-ads" aria-label="Publicidade da radio">
            @foreach(range(1, 4) as $adIndex)
                <x-ad-slot :name="'radio_hero_'.$adIndex" label="Patrocinador da radio" variant="radio-small" />
            @endforeach
        </aside>
    </section>

    @if($fieldLiveEnabled)
        <section class="radio-field-live-panel" aria-label="Cobertura externa ao vivo">
            <div class="radio-panel-head">
                <div>
                    <p class="eyebrow">Ao vivo da rua</p>
                    <h2>{{ data_get($radioSettings, 'field_live_title') }}</h2>
                    <p>{{ data_get($radioSettings, 'field_live_description') }}</p>
                </div>

                <div class="radio-field-actions">
                    @if(filled($fieldVideoUrl))
                        <a class="secondary-action" href="{{ $fieldVideoUrl }}" target="_blank" rel="noopener noreferrer">Abrir transmissao</a>
                    @endif
                    @if(filled($fieldReporterWhatsapp))
                        <a class="secondary-action" href="https://wa.me/{{ $fieldReporterWhatsapp }}" target="_blank" rel="noopener noreferrer">WhatsApp da equipe</a>
                    @endif
                </div>
            </div>

            <div class="radio-field-live-grid">
                <div class="radio-tiktok-frame radio-tiktok-frame-landscape">
                    @if(filled(data_get($radioSettings, 'field_video_embed_code')))
                        {!! \App\Services\Security\EmbedCodeSanitizer::sanitize(data_get($radioSettings, 'field_video_embed_code')) !!}
                    @else
                        <div class="radio-placeholder">
                            <span class="live-dot" aria-hidden="true"></span>
                            <strong>Cobertura externa</strong>
                            <p>Configure o iframe ou link publico da transmissao externa no painel da radio.</p>
                        </div>
                    @endif
                </div>

                <aside class="radio-player-shell">
                    <div>
                        <span class="live-dot" aria-hidden="true"></span>
                        <strong>Audio da cobertura</strong>
                    </div>

                    @if(filled($fieldAudioStreamUrl))
                        <audio controls preload="none" src="{{ $fieldAudioStreamUrl }}"></audio>
                    @else
                        <p>Configure um streaming de audio externo se quiser transmitir so o som da rua.</p>
                    @endif

                    @if(filled($fieldReturnLink))
                        <a class="secondary-action" href="{{ $fieldReturnLink }}" target="_blank" rel="noopener noreferrer">Link de retorno da equipe</a>
                    @endif
                </aside>
            </div>
        </section>
    @endif

    <section
        @class(['radio-chat-layout', 'radio-chat-layout-lobby' => ! $hasEnteredChatRoom])
        aria-label="Bate-papo da Radio Luzicity"
        data-radio-live
        @if($hasEnteredChatRoom)
            data-radio-chat-room="{{ $selectedChatRoom }}"
            data-radio-chat-nickname="{{ $chatNickname }}"
        @endif
    >
        @if(! $hasEnteredChatRoom)
            <div class="radio-request-box radio-chat-box radio-chat-room" aria-label="Entrada das salas">
                <div class="radio-chat-titlebar">
                    <div>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </div>
                    <strong>Luzicity Messenger</strong>
                </div>

                <form class="radio-chat-lobby" method="get" action="{{ route('radio.index') }}">
                    <p class="eyebrow">Entrada do bate-papo</p>
                    <h2>Escolha uma sala para entrar</h2>
                    <p>Acompanhe o locutor, defina seu apelido e escolha a sala. Mensagens, fotos e reservado aparecem somente depois da entrada.</p>

                    <div class="radio-chat-lobby-live">
                        <div class="radio-chat-lobby-video" data-radio-video-frame>
                            @if(filled(data_get($radioSettings, 'tiktok_embed_code')))
                                {!! \App\Services\Security\EmbedCodeSanitizer::sanitize(data_get($radioSettings, 'tiktok_embed_code')) !!}
                            @else
                                <div class="radio-placeholder">
                                    <span class="live-dot" aria-hidden="true"></span>
                                    <strong>Locutor ao vivo</strong>
                                </div>
                            @endif
                        </div>

                        <div class="radio-chat-lobby-audio">
                            <strong>Radio Web Luzicity</strong>
                            @if($hasAudioStream)
                                <audio controls preload="none" src="{{ $audioStreamUrl }}" data-radio-audio></audio>
                            @else
                                <p>Configure o streaming de audio no painel da radio.</p>
                            @endif

                            <div class="radio-mode-controls" role="group" aria-label="Economia de dados da radio">
                                <button class="secondary-action radio-mode-button" type="button" data-radio-mode="video" aria-pressed="true">Video + audio</button>
                                <button class="secondary-action radio-mode-button" type="button" data-radio-mode="audio" aria-pressed="false" @disabled(! $hasAudioStream)>So audio</button>
                                <button class="secondary-action radio-mode-button" type="button" data-radio-mode="pause" aria-pressed="false">Desligar</button>
                            </div>
                            <p class="radio-mode-status" data-radio-status>Escolha como acompanhar antes de entrar na sala.</p>
                        </div>
                    </div>

                    <div class="radio-chat-entry-fields">
                        <label class="radio-chat-nickname">
                            Apelido para entrar
                            <input name="apelido" value="{{ $chatNickname }}" placeholder="Ex: Joao de Luziania" required>
                        </label>

                        <label class="radio-chat-nickname">
                            Sala
                            <select name="sala" required>
                                <option value="">Escolha a sala</option>
                                @foreach($chatCategories as $categoryKey => $categoryLabel)
                                    <option value="{{ $categoryKey }}">{{ $categoryLabel }} - {{ $roomCounts[$categoryKey] ?? 1 }} pessoas agora</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <button class="secondary-action radio-chat-enter-button" type="submit">Entrar na sala</button>
                </form>
            </div>
        @else
            <form method="post" action="{{ route('radio.requests.store') }}" class="radio-request-box radio-chat-box radio-chat-room" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="category" value="{{ $selectedChatRoom }}">

                <div class="radio-chat-titlebar">
                    <div>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </div>
                    <strong>Luzicity Messenger</strong>
                </div>

                <div class="radio-chat-live-strip">
                    <div class="radio-chat-mini-video" data-radio-video-frame>
                        @if(filled(data_get($radioSettings, 'tiktok_embed_code')))
                            {!! \App\Services\Security\EmbedCodeSanitizer::sanitize(data_get($radioSettings, 'tiktok_embed_code')) !!}
                        @else
                            <div class="radio-placeholder">
                                <span class="live-dot" aria-hidden="true"></span>
                                <strong>Locutor ao vivo</strong>
                            </div>
                        @endif
                    </div>

                    <div class="radio-chat-live-info">
                        <p class="eyebrow">Sala {{ $chatCategories[$selectedChatRoom] ?? 'Geral' }}</p>
                        <h2>Converse ouvindo a radio</h2>
                        <p>O locutor e a monitoria acompanham as salas para manter o ambiente seguro.</p>
                        <a class="secondary-action radio-chat-exit" href="{{ route('radio.index', array_filter(['apelido' => $chatNickname ?: null])) }}">Sair da sala</a>

                        <div class="radio-chat-audio-panel">
                            <div>
                                <span class="live-dot" aria-hidden="true"></span>
                                <strong>Som da radio</strong>
                            </div>

                            @if($hasAudioStream)
                                <audio controls preload="none" src="{{ $audioStreamUrl }}" data-radio-audio></audio>
                            @else
                                <p>Configure a URL do streaming de audio no painel para permitir que o ouvinte converse ouvindo a radio.</p>
                            @endif
                        </div>

                        <div class="radio-mode-controls" role="group" aria-label="Economia de dados da radio">
                            <button class="secondary-action radio-mode-button" type="button" data-radio-mode="video" aria-pressed="true">Video + audio</button>
                            <button class="secondary-action radio-mode-button" type="button" data-radio-mode="audio" aria-pressed="false" @disabled(! $hasAudioStream)>So audio</button>
                            <button class="secondary-action radio-mode-button" type="button" data-radio-mode="pause" aria-pressed="false">Desligar</button>
                        </div>

                        <p class="radio-mode-status" data-radio-status>
                            Modo video ativo. Use os controles para economizar dados quando precisar.
                        </p>
                    </div>
                </div>

                @if($hasTiktokUrl)
                    <a class="secondary-action radio-chat-external" href="{{ $tiktokUrl }}" target="_blank" rel="noopener noreferrer">Abrir transmissao fora do site</a>
                @endif

                <div class="radio-chat-messages" aria-label="Mensagens recentes">
                    <article class="radio-chat-message radio-chat-message-host">
                        <div class="radio-chat-avatar" aria-hidden="true">M</div>
                        <div class="radio-chat-bubble">
                            <div class="radio-chat-meta">
                                <strong>Monitor Luzicity</strong>
                                <span>{{ $chatCategories[$selectedChatRoom] ?? 'Geral' }}</span>
                            </div>
                            <p>Esta sala e moderada. Conversas reservadas tambem podem ser vistas pela monitoria em caso de denuncia, ofensa ou violacao das regras.</p>
                        </div>
                    </article>

                    <article class="radio-chat-message radio-chat-message-host">
                        <div class="radio-chat-avatar" aria-hidden="true">L</div>
                        <div class="radio-chat-bubble">
                            <div class="radio-chat-meta">
                                <strong>Locutor Luzicity</strong>
                                <span>{{ $chatCategories[$selectedChatRoom] ?? 'Geral' }}</span>
                            </div>
                            <p>Estou presente na sala. Pode conversar com a galera e mandar pedido de musica por aqui.</p>
                        </div>
                    </article>

                    @forelse($chatMessages as $chatMessage)
                        <article
                            class="radio-chat-message"
                            data-chat-message-id="{{ $chatMessage->id }}"
                            data-chat-recipient="{{ $chatMessage->recipient_name }}"
                            data-chat-author="{{ $chatMessage->name }}"
                        >
                            <div class="radio-chat-avatar" aria-hidden="true">{{ str($chatMessage->name ?: 'O')->substr(0, 1)->upper() }}</div>
                            <div class="radio-chat-bubble">
                                <div class="radio-chat-meta">
                                    <strong>{{ $chatMessage->name ?: 'Ouvinte' }}</strong>
                                    <span>
                                        {{ $chatMessage->regionLabel() }}
                                        @if($chatMessage->recipient_name)
                                            | falando com {{ $chatMessage->recipient_name }}
                                        @endif
                                    </span>
                                </div>
                                <p>{{ $chatMessage->message }}</p>

                                @if($chatMessage->attachment_path && $chatMessage->attachment_type === 'image')
                                    <img src="{{ asset('storage/'.$chatMessage->attachment_path) }}" alt="Imagem enviada por {{ $chatMessage->name ?: 'ouvinte' }}">
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="radio-chat-empty">
                            <strong>Sala pronta para conversar</strong>
                            <p>As primeiras mensagens desta sala aparecem aqui.</p>
                        </div>
                    @endforelse
                </div>

                <div class="radio-chat-identity">
                    <span>Voce esta na sala como <strong>{{ $chatNickname ?: 'Ouvinte' }}</strong></span>
                    <a href="{{ route('radio.index', array_filter(['apelido' => $chatNickname ?: null])) }}">Sair e escolher outra sala</a>
                </div>

                <section class="radio-room-people" aria-label="Pessoas na sala">
                    <div>
                        <strong>Pessoas na sala</strong>
                        <span>{{ $roomParticipants->count() + 1 }} online</span>
                    </div>

                    <div class="radio-room-people-list">
                        <span class="is-current">{{ $chatNickname ?: 'Voce' }}</span>
                        @forelse($roomParticipants as $participant)
                            <span>{{ $participant }}</span>
                        @empty
                            <small>Quando outras pessoas enviarem mensagens, elas aparecem aqui.</small>
                        @endforelse
                    </div>
                </section>

                <input type="hidden" name="name" value="{{ $chatNickname }}">

                <label class="radio-message-mode radio-private-toggle">
                    <input type="checkbox" name="is_private" value="1">
                    <span>Marcar como reservado</span>
                </label>

                <label>
                    Falando com
                    <select name="recipient_name">
                        <option value="">Todos da sala</option>
                        @foreach($roomParticipants as $participant)
                            <option value="{{ $participant }}">{{ $participant }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Mensagem
                    <textarea name="message" rows="3" required placeholder="Digite sua mensagem para a sala. Links de perfis sociais sao permitidos."></textarea>
                </label>

                <label>
                    Foto leve opcional
                    <input type="file" name="attachment" accept="image/*">
                </label>

                <div class="radio-private-chat">
                    <strong>Dados do reservado</strong>
                    <p>Escolha a pessoa em "Falando com" e preencha o contato somente se escolheu falar reservadamente. A monitoria pode revisar mensagens reservadas para proteger os participantes.</p>

                    <label>
                        Contato para troca reservada
                        <input name="private_contact" placeholder="WhatsApp, Instagram, Facebook ou outro perfil">
                    </label>
                </div>

                <button class="secondary-action" type="submit">Enviar para a sala</button>
            </form>
        @endif
    </section>
@endsection
