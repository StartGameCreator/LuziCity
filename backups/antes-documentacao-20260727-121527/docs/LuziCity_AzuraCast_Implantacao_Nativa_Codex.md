# LuziCity + AzuraCast — Plano de Implantação Nativa para Codex

## 1. Objetivo

Integrar o AzuraCast ao LuziCity como motor de rádio, mantendo o LuziCity como aplicação principal e oferecendo uma experiência visual e operacional única.

A integração não deve ser um simples iframe do painel do AzuraCast. O painel do LuziCity deve consumir a API do AzuraCast e apresentar os controles dentro das telas existentes:

- `/admin/radio-central`
- `/admin/radio`
- `/radio`

A área de TV deve permanecer no LuziCity e ser ampliada em:

- `/admin/tv-central`

O resultado deve parecer um único sistema, embora o AzuraCast continue executando como serviço independente em containers.

---

## 2. Constatações técnicas obrigatórias

1. O AzuraCast é uma plataforma de rádio web e automação de áudio.
2. Ele possui API REST autenticada por Bearer Token.
3. Ele fornece:
   - dados de “Now Playing”;
   - ouvintes;
   - histórico;
   - informações da emissora;
   - controle de início, parada e reinício;
   - playlists, mídia e programação, conforme a API da versão instalada;
   - Icecast ou Shoutcast;
   - Liquidsoap;
   - WebSocket/SSE para atualização em tempo real.
4. O AzuraCast não deve ser incorporado ao código Laravel nem ter seu banco compartilhado com o LuziCity.
5. O AzuraCast não é um servidor nativo de TV/vídeo. A TV deve usar a estrutura já existente do LuziCity, FFmpeg e um servidor RTMP/HLS próprio.
6. A integração nativa será feita pelo padrão Backend for Frontend:
   - navegador acessa o LuziCity;
   - Laravel acessa a API privada do AzuraCast;
   - a chave da API nunca é enviada ao navegador.

---

## 3. Arquitetura final

```text
Navegador
   |
   v
LuziCity Laravel :9001
   |                     \
   | API privada           \ TV
   v                        v
AzuraCast :8080          FFmpeg + RTMP/HLS
   |
   +-- Liquidsoap
   +-- Icecast/Shoutcast
   +-- AutoDJ
   +-- Playlists
   +-- DJs
```

### Ambiente local Windows

- LuziCity: `http://127.0.0.1:9001`
- Vite: porta já configurada no projeto
- AzuraCast: `http://127.0.0.1:8080`
- AzuraCast HTTPS local opcional: `8443`
- Docker Desktop com WSL2

### Produção

Recomendado:

```text
https://luzicity.com.br
https://radio-engine.luzicity.com.br
```

O subdomínio do AzuraCast pode ficar protegido e sem navegação pública. Os ouvintes usam os players do LuziCity.

---

## 4. Primeira regra do Codex

Antes de modificar qualquer arquivo:

1. Examinar a base real do projeto em `D:\Skill\LuziCity`.
2. Confirmar a branch atual.
3. Criar uma branch:
   `feature/azuracast-native-integration`
4. Verificar:
   - models de rádio;
   - controllers;
   - services;
   - settings;
   - routes;
   - views;
   - testes;
   - estrutura de TV;
   - FFmpeg;
   - filas e scheduler.
5. Não substituir as funções existentes.
6. Não apagar dados.
7. Não alterar `.env` automaticamente.
8. Não armazenar chave da API em texto puro no banco.
9. Não usar iframe como solução principal.
10. Manter SQLite.

---

## 5. Implantação do AzuraCast

Criar:

```text
infrastructure/azuracast/
├── README.md
├── azuracast.env.example
├── install-azuracast-windows.ps1
├── start-azuracast.ps1
├── stop-azuracast.ps1
├── status-azuracast.ps1
├── update-azuracast.ps1
└── backup-azuracast.ps1
```

### Requisitos do instalador

O PowerShell deve:

1. Verificar se Docker Desktop está instalado.
2. Verificar se o daemon Docker está ativo.
3. Verificar suporte ao Docker Compose.
4. Criar uma pasta externa ao Laravel:
   `D:\Skill\LuziCity-Services\AzuraCast`
5. Não instalar o AzuraCast dentro de `vendor`, `storage` ou `public`.
6. Clonar o repositório oficial somente quando a pasta ainda não existir.
7. Usar o canal stable.
8. Configurar portas sem conflito:
   - HTTP 8080
   - HTTPS 8443
   - SFTP 2022
9. Não usar portas 8000–9000 sem verificar conflitos.
10. Criar logs em:
    `D:\Skill\LuziCity\storage\logs\azuracast-install.log`
11. Parar imediatamente em falha crítica.
12. Ser idempotente.
13. Nunca apagar volumes no comando normal de atualização.
14. Criar backup antes de atualizar.
15. Mostrar ao final:
    - URL;
    - status dos containers;
    - portas;
    - próximo passo para criar usuário e API key.

### Arquivo `azuracast.env.example`

Deve conter somente valores não secretos e explicações. O arquivo real não deve ser commitado.

---

## 6. Configuração Laravel

Adicionar em `.env.example`:

```dotenv
AZURACAST_ENABLED=false
AZURACAST_BASE_URL=http://127.0.0.1:8080
AZURACAST_API_KEY=
AZURACAST_STATION_ID=
AZURACAST_STATION_SHORTCODE=
AZURACAST_TIMEOUT=10
AZURACAST_VERIFY_SSL=false
AZURACAST_CACHE_SECONDS=10
```

Adicionar em `config/services.php`:

```php
'azuracast' => [
    'enabled' => env('AZURACAST_ENABLED', false),
    'base_url' => env('AZURACAST_BASE_URL'),
    'api_key' => env('AZURACAST_API_KEY'),
    'station_id' => env('AZURACAST_STATION_ID'),
    'station_shortcode' => env('AZURACAST_STATION_SHORTCODE'),
    'timeout' => (int) env('AZURACAST_TIMEOUT', 10),
    'verify_ssl' => (bool) env('AZURACAST_VERIFY_SSL', true),
    'cache_seconds' => (int) env('AZURACAST_CACHE_SECONDS', 10),
],
```

Nunca exibir `AZURACAST_API_KEY`.

---

## 7. Camada de integração

Criar:

```text
app/Contracts/RadioAutomationProvider.php
app/Services/AzuraCast/AzuraCastClient.php
app/Services/AzuraCast/AzuraCastHealthService.php
app/Services/AzuraCast/AzuraCastNowPlayingService.php
app/Services/AzuraCast/AzuraCastStationService.php
app/Services/AzuraCast/AzuraCastPlaylistService.php
app/Services/AzuraCast/AzuraCastMediaService.php
app/Services/AzuraCast/AzuraCastScheduleService.php
app/Services/AzuraCast/AzuraCastStreamerService.php
app/Services/AzuraCast/AzuraCastCacheService.php
app/Exceptions/AzuraCastException.php
```

### `AzuraCastClient`

Deve usar Laravel HTTP Client:

- `Http::baseUrl()`
- `acceptJson()`
- `withToken()`
- timeout
- retry somente para GET idempotente
- tratamento de 401, 403, 404, 409, 422, 429 e 5xx
- logs sem token
- circuit breaker simples usando cache
- retorno tipado ou arrays normalizados

Não fazer chamadas externas diretamente em controllers.

### Segurança

- Bearer Token somente no backend.
- Proibir URL com host interno não autorizado configurado por usuário comum.
- Somente Super Admin pode editar conexão.
- Validar URL base.
- Mascarar chave.
- Aplicar rate limit nas rotas de comandos.
- Registrar auditoria para start, stop, restart e alterações.

---

## 8. Banco de dados

Criar migration defensiva para:

### `radio_integrations`

```text
id
provider
name
base_url
station_id
station_shortcode
enabled
verify_ssl
last_health_status
last_health_message
last_health_at
last_sync_at
settings_encrypted
created_by
updated_by
timestamps
```

A chave pode permanecer exclusivamente no `.env`. Caso seja necessário editar pelo painel, salvar em coluna criptografada com cast `encrypted`, nunca em texto puro.

### `radio_integration_audits`

```text
id
radio_integration_id
user_id
action
target_type
target_id
request_summary
response_status
success
error_message
ip_address
user_agent
timestamps
```

### Opcional: snapshots

`radio_now_playing_snapshots` somente se houver necessidade analítica. Não gravar atualização a cada poucos segundos sem política de retenção.

---

## 9. Rotas

Criar `routes/azuracast.php` e carregar em `routes/web.php`.

```text
GET  /admin/radio/azuracast
GET  /admin/radio/azuracast/health
POST /admin/radio/azuracast/test
POST /admin/radio/azuracast/sync
POST /admin/radio/azuracast/station/start
POST /admin/radio/azuracast/station/stop
POST /admin/radio/azuracast/station/restart
GET  /admin/radio/azuracast/now-playing
GET  /admin/radio/azuracast/listeners
GET  /admin/radio/azuracast/history
GET  /admin/radio/azuracast/playlists
GET  /admin/radio/azuracast/media
GET  /admin/radio/azuracast/schedule
GET  /admin/radio/azuracast/streamers
```

Todas com:

```text
auth
roles:Super Admin,Admin
throttle
```

Comandos destrutivos ou operacionais devem exigir CSRF e POST.

---

## 10. Painel nativo da rádio

Atualizar:

```text
app/Services/RadioDashboardService.php
resources/views/admin/radio-dashboard/index.blade.php
```

Criar componentes Blade:

```text
resources/views/admin/radio-dashboard/_azuracast-status.blade.php
resources/views/admin/radio-dashboard/_now-playing.blade.php
resources/views/admin/radio-dashboard/_listeners.blade.php
resources/views/admin/radio-dashboard/_station-controls.blade.php
resources/views/admin/radio-dashboard/_recent-history.blade.php
resources/views/admin/radio-dashboard/_playlists.blade.php
resources/views/admin/radio-dashboard/_streamers.blade.php
```

### Layout

Usar a mesma linguagem visual do LuziCity:

- cards;
- espaçamentos;
- tipografia;
- botões;
- estados;
- responsividade;
- ícones existentes;
- sem copiar o CSS do AzuraCast.

### Cards obrigatórios

1. Motor AzuraCast:
   - conectado/desconectado;
   - versão;
   - latência;
   - última sincronização.
2. Emissora:
   - online/offline;
   - AutoDJ;
   - backend;
   - frontend.
3. No ar:
   - capa;
   - artista;
   - música;
   - álbum;
   - duração;
   - progresso;
   - DJ ao vivo.
4. Ouvintes:
   - atuais;
   - únicos;
   - pico.
5. Controles:
   - iniciar;
   - parar;
   - reiniciar.
6. Histórico recente.
7. Próximas faixas ou programação, quando fornecidas pela API.
8. Playlists.
9. DJs/streamers.
10. Links de escuta.

### Atualização em tempo real

Preferência:

1. WebSocket/SSE público do Now Playing, sem chave;
2. fallback para endpoint interno Laravel;
3. polling de 10–15 segundos;
4. backoff em falha.

A interface nunca deve chamar endpoints administrativos do AzuraCast diretamente.

---

## 11. Player público

Atualizar a página `/radio` para consumir os dados normalizados pelo LuziCity.

Requisitos:

- stream principal fornecido pelo AzuraCast;
- capa e metadados;
- botão play/pause;
- volume;
- indicação ao vivo;
- fallback de URL;
- acessibilidade;
- Media Session API;
- atualização de título e capa;
- não iniciar áudio automaticamente;
- manter chat, pedidos, publicidade e grade já existentes.

Não remover as configurações atuais. Criar fallback:

```text
AzuraCast ativo e saudável
    -> usar stream e metadados AzuraCast
senão
    -> usar audio_stream_url atual
```

---

## 12. Simetria entre Rádio e TV

O painel `/admin/tv-central` deve seguir a mesma estrutura visual do novo painel de rádio, mas sem fingir que o AzuraCast transmite vídeo.

Criar uma camada comum de componentes:

```text
resources/views/admin/broadcast/components/
├── status-card.blade.php
├── metric-card.blade.php
├── control-bar.blade.php
├── now-playing-card.blade.php
├── schedule-list.blade.php
├── health-badge.blade.php
└── empty-state.blade.php
```

### Rádio

- motor: AzuraCast;
- saída: Icecast/Shoutcast;
- automação: Liquidsoap;
- mídia: áudio;
- audiência: ouvintes.

### TV

- motor: LuziCity TV;
- transcodificação: FFmpeg;
- ingest: RTMP;
- saída: HLS/embed;
- mídia: vídeo;
- audiência: espectadores, quando o provedor disponibilizar.

Os dois painéis devem ter:

```text
Cabeçalho
Status do motor
Agora no ar
Audiência
Controles
Programação
Biblioteca
Saúde técnica
Logs recentes
```

---

## 13. Controles de TV em `/admin/tv-central`

A base atual já possui:

- `TvChannel`
- `TvBroadcast`
- `TvBroadcastService`
- `TvDashboardService`
- videoteca;
- roteiros;
- clips;
- FFmpeg;
- fila de renderização.

O Codex deve ampliar, não reconstruir.

Criar:

```text
app/Services/TV/TvEngineHealthService.php
app/Services/TV/TvStreamControlService.php
app/Services/TV/TvOutputService.php
app/Services/TV/TvAudienceService.php
app/Http/Controllers/AdminTvStreamControlController.php
routes/tv_control.php
```

### Controles intuitivos

- testar entrada;
- iniciar transmissão;
- parar transmissão;
- reiniciar encoder;
- copiar URL RTMP;
- copiar chave mascarada;
- testar saída HLS;
- abrir player;
- visualizar bitrate;
- FPS;
- resolução;
- codec;
- tempo no ar;
- estado do processo FFmpeg;
- logs sanitizados.

### Segurança

- chave RTMP criptografada;
- nunca mostrar chave completa;
- confirmação antes de parar;
- lock para impedir dois encoders da mesma transmissão;
- processo controlado por fila/supervisor em produção;
- sem executar comando shell concatenando entrada do usuário;
- usar Symfony Process com argumentos separados;
- allowlist de protocolos e codecs.

---

## 14. Servidor de vídeo

Não instalar dentro do container AzuraCast.

Preparar suporte configurável a:

- MediaMTX ou SRS para RTMP/HLS;
- FFmpeg como encoder;
- YouTube/Vimeo/RTMP externos como provedores alternativos.

Variáveis:

```dotenv
TV_ENGINE_ENABLED=false
TV_RTMP_SERVER=rtmp://127.0.0.1:1935/live
TV_HLS_BASE_URL=http://127.0.0.1:8888
TV_STREAM_KEY=
TV_FFMPEG_BINARY=ffmpeg
TV_FFPROBE_BINARY=ffprobe
TV_PROCESS_DRIVER=local
```

Para Windows local, permitir processo supervisionado pelo Laravel somente em desenvolvimento. Em produção, usar Supervisor/systemd/queue worker.

---

## 15. Sincronização de grade

Não duplicar automaticamente toda a grade entre os bancos.

Definir uma fonte de verdade:

- AzuraCast: playlists, AutoDJ, músicas e rotação de áudio;
- LuziCity: programas editoriais, locutores, grade pública, publicidade e conteúdo;
- TV LuziCity: canais, transmissões e vídeo.

Criar um serviço de mapeamento, sem apagar dados locais:

```text
RadioScheduleSyncService
```

A sincronização deve ser manual inicialmente, com prévia das mudanças.

---

## 16. Jobs e scheduler

Criar:

```text
app/Jobs/SyncAzuraCastStationJob.php
app/Jobs/CheckAzuraCastHealthJob.php
app/Jobs/CacheAzuraCastNowPlayingJob.php
```

Agendamento sugerido:

- health: a cada minuto;
- sync de dados administrativos: a cada 5 minutos;
- now playing: preferir realtime, não gravar a cada execução;
- limpeza de auditoria/snapshots conforme retenção.

Falha do AzuraCast nunca deve derrubar o painel Laravel.

---

## 17. Testes

### Unit

- cliente API;
- normalização;
- mascaramento de chave;
- circuit breaker;
- fallback;
- URLs de stream;
- comandos de TV.

### Feature

- autorização;
- painel com integração desativada;
- painel com API indisponível;
- painel com resposta válida;
- start/stop/restart;
- CSRF;
- throttle;
- logs;
- nenhuma chave no HTML.

### HTTP fake

Usar `Http::fake()` para todos os testes. Nenhum teste automatizado deve depender de uma instalação real.

### Testes manuais

- instalação limpa;
- reinício do Docker;
- AzuraCast offline;
- token inválido;
- estação inexistente;
- stream online;
- DJ conectado;
- AutoDJ;
- fallback antigo;
- TV com FFmpeg ausente;
- TV com RTMP indisponível.

---

## 18. Critérios de aceite

### Rádio

- AzuraCast instalado separadamente e iniciado por scripts.
- LuziCity mostra estado da emissora.
- Player público toca o stream.
- “No ar” atualiza em tempo real.
- Ouvintes e histórico aparecem.
- Start/stop/restart funcionam para administradores.
- Chave nunca aparece no navegador ou logs.
- Sem iframe como interface principal.
- Falha do AzuraCast não quebra o LuziCity.

### TV

- `/admin/tv-central` usa o mesmo padrão visual.
- Controles de stream funcionam com FFmpeg/RTMP.
- AzuraCast não é tratado como servidor de vídeo.
- Estado técnico é claro.
- Chaves são protegidas.
- Processos não duplicam.
- Player HLS ou embed pode ser testado no painel.

### Projeto

- SQLite preservado.
- `.env` não sobrescrito.
- migrations defensivas.
- rotas sem duplicidade.
- `php -l` passa.
- `php artisan test` passa.
- `npm run build` passa.
- documentação atualizada.

---

## 19. Ordem de execução no Codex

### Etapa A — Auditoria

Somente analisar e produzir relatório. Não modificar.

### Etapa B — Infraestrutura AzuraCast

Criar scripts PowerShell, exemplos e documentação.

### Etapa C — Cliente Laravel

Config, contrato, cliente, health e testes.

### Etapa D — Painel da rádio

Status, Now Playing, ouvintes, histórico e controles.

### Etapa E — Player público

Stream, metadados e fallback.

### Etapa F — Simetria TV

Componentes compartilhados e reorganização visual.

### Etapa G — Controles TV

FFmpeg, RTMP/HLS, health e segurança.

### Etapa H — Consolidação

Testes, documentação, build, auditoria de segurança e commit final.

Cada etapa deve ter um commit separado.

---

## 20. Prompt para o Codex

```text
Você está trabalhando no projeto Laravel LuziCity em:

D:\Skill\LuziCity

Objetivo:
Integrar o AzuraCast como motor nativo de rádio do LuziCity e ampliar a Central TV Web com controles reais de FFmpeg/RTMP/HLS, mantendo simetria visual entre os painéis.

Leia integralmente:
docs/integrations/AZURACAST-NATIVE-IMPLEMENTATION.md

Regras:
1. Audite a base real antes de alterar.
2. Não recrie módulos existentes.
3. Não use iframe como integração principal.
4. AzuraCast é serviço separado e acessado pela API.
5. O token fica somente no backend.
6. Não trate AzuraCast como servidor de vídeo.
7. Preserve SQLite e dados.
8. Não sobrescreva .env.
9. Faça migrations defensivas.
10. Use Laravel HTTP Client.
11. Use Http::fake nos testes.
12. Use Symfony Process de forma segura para TV.
13. Não concatene comandos shell com entradas do usuário.
14. Mantenha fallback para a configuração atual da rádio.
15. Não remova chat, pedidos, grade, podcasts ou publicidade.
16. Use componentes visuais compartilhados entre rádio e TV.
17. Cada etapa deve produzir commit separado.
18. Não faça push.
19. Ao final, execute:
   php artisan optimize:clear
   php artisan migrate
   php artisan route:list
   php artisan test
   npm run build
20. Informe arquivos, migrations, rotas, testes, comandos, riscos e pendências.

Comece apenas pela Etapa A:
- audite o projeto;
- confronte este documento com a estrutura real;
- produza `docs/integrations/AZURACAST-AUDIT.md`;
- não altere código funcional nesta primeira execução.
```
