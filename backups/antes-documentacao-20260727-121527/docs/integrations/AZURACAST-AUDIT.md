# Auditoria da integração nativa AzuraCast

Data da auditoria: 26/07/2026  
Branch: `feature/azuracast-native-integration`  
Escopo: Etapa A — análise sem alteração de código funcional

## Resumo executivo

O LuziCity já possui uma base funcional de rádio e TV que deve ser ampliada, não
substituída. A integração AzuraCast é tecnicamente viável pelo padrão Backend for
Frontend proposto: o Laravel consumirá a API privada, normalizará os dados e
entregará apenas informações seguras ao painel e ao player público.

Ainda não existe integração AzuraCast, infraestrutura Docker, cliente REST,
persistência de integração ou controles reais do motor de rádio. O ambiente
Windows auditado também não possui o comando `docker` disponível no PATH; por
isso, a instalação real não pode começar antes da instalação/inicialização do
Docker Desktop com WSL2.

O arquivo `AzuraCast-main.zip` contém uma árvore de código-fonte do AzuraCast,
incluindo backend, frontend, vendor, Dockerfile e controladores REST. Ele é útil
como referência técnica, mas não deve ser copiado para dentro do Laravel. Para a
implantação, os scripts devem usar o instalador/repositório oficial no canal
stable, conforme o plano.

## Estado atual da rádio

### Domínio e persistência

Já existem:

- `RadioStation`, com nome, descrição, stream, logo e estado;
- `RadioHost`, para locutores;
- `RadioProgram`, ligado à emissora e ao locutor;
- `RadioScheduleSlot`, com dia e horários;
- podcasts, narrações, spots, campanhas e métricas de reprodução;
- pedidos/chat da rádio;
- configurações legadas no grupo `radio` da tabela `settings`.

O `RadioOnAirService` calcula o programa atual exclusivamente pela grade local.
Essa lógica deve continuar como fonte editorial do LuziCity. Dados de faixa,
AutoDJ, DJ e ouvintes virão do AzuraCast sem substituir a grade local.

### Interfaces e rotas

Rotas existentes relevantes:

- `GET /radio`;
- `GET /admin/radio`;
- `PUT /admin/radio`;
- `GET /admin/radio-central`;
- CRUD parcial em `/admin/radio/estrutura`;
- módulos de podcasts, narração e publicidade.

O painel `/admin/radio-central` consolida números locais, custos e filas, mas não
possui estado de motor, ouvintes, histórico, controles ou atualização em tempo
real. A página `/radio` já contém player HTML5, chat, pedidos, publicidade, grade,
cobertura externa e modos de economia de dados. Esses recursos devem ser
preservados e o stream AzuraCast deve entrar como fonte preferencial com fallback.

### Segurança atual

As rotas administrativas exigem autenticação e papéis `Super Admin` ou `Admin`.
O projeto já dispõe de:

- auditoria de mutações administrativas;
- rate limiting;
- headers de segurança;
- campos criptografados em outros módulos;
- cache e filas monitoradas.

Não existe ainda uma política específica para host AzuraCast, mascaramento de
token, circuit breaker ou auditoria semântica de start/stop/restart.

## Estado atual da TV

Já existem:

- `TvChannel` e `TvBroadcast`;
- providers YouTube, Vimeo, RTMP, HLS e embed;
- `TvBroadcastService` para seleção da transmissão ao vivo e URL de player;
- Central TV, videoteca, roteiros, recortes e fila `video-render`;
- FFmpeg seguro para renderização de clips via `Symfony Process` com argumentos
  separados;
- chave RTMP oculta no modelo e cast `encrypted`.

A Central TV informa disponibilidade do FFmpeg e conteúdo operacional, mas não
controla processos de encoder nem testa ingest/saída. Faltam health de FFmpeg e
RTMP/HLS, locks de processo, start/stop/restart, telemetria de bitrate/FPS/codec e
logs sanitizados.

AzuraCast não deve participar do fluxo de vídeo. A simetria entre rádio e TV deve
ser somente visual e operacional, por componentes Blade compartilhados.

## Infraestrutura e ambiente

Constatações:

- aplicação Laravel local em SQLite;
- LuziCity esperado em `127.0.0.1:9001`;
- não existe pasta `infrastructure/azuracast`;
- não existem variáveis `AZURACAST_*` ou `TV_ENGINE_*`;
- Docker CLI não está instalado ou não está disponível no PATH;
- não foi criada a pasta externa `D:\Skill\LuziCity-Services\AzuraCast`;
- o `.env` não foi alterado;
- nenhuma porta ou container foi criado nesta etapa.

Antes da Etapa B, o usuário deverá instalar/iniciar Docker Desktop com backend
WSL2. Os scripts devem detectar esse estado e encerrar com mensagem clara, sem
fazer instalação silenciosa nem remover volumes.

## Compatibilidade com a API observada no ZIP

A árvore fornecida confirma controladores para:

- Now Playing e arte;
- dashboard e dados da estação;
- listeners e histórico;
- playlists e fila;
- arquivos/mídia;
- mounts e HLS;
- streamers;
- logs;
- controles de serviço;
- OpenAPI público.

Os caminhos e formatos exatos não devem ser fixados apenas pela estrutura do ZIP.
Na Etapa C, o cliente deverá encapsular as rotas da versão instalada e normalizar
respostas, permitindo ajustes sem afetar controllers ou views do LuziCity.

## Mapeamento proposto

| Responsabilidade | Fonte de verdade |
|---|---|
| Músicas, playlists, AutoDJ e rotação | AzuraCast |
| Stream, mounts, DJ conectado e ouvintes | AzuraCast |
| Programas, locutores e grade pública | LuziCity |
| Chat, pedidos e publicidade | LuziCity |
| Podcasts e narrações editoriais | LuziCity |
| Canais, vídeo, RTMP/HLS e FFmpeg | LuziCity TV |

`RadioScheduleSyncService` deve inicialmente produzir apenas uma prévia manual.
Não deve excluir nem sobrescrever automaticamente horários locais.

## Lacunas para as próximas etapas

### Etapa B — infraestrutura

- criar scripts PowerShell idempotentes;
- validar Docker Desktop, daemon e Compose;
- instalar externamente no canal stable;
- reservar HTTP 8080, HTTPS 8443 e SFTP 2022 após teste de portas;
- criar start, stop, status, update e backup;
- registrar logs sanitizados;
- nunca remover volumes em update normal.

### Etapa C — cliente Laravel

- adicionar configuração e contrato;
- implementar cliente HTTP, normalizadores, retry GET e circuit breaker;
- validar/permitir apenas base URL administrativa configurada;
- criar health, station, now playing, playlists, mídia, grade e streamers;
- criar migrations defensivas e casts criptografados;
- registrar provider no container;
- cobrir tudo com `Http::fake()`.

### Etapas D e E — rádio

- integrar os dados ao `RadioDashboardService`;
- criar componentes nativos e controles POST;
- criar endpoint interno público somente para dados de escuta;
- usar polling com backoff e preparar SSE/WebSocket público;
- preservar o player, chat, pedidos, grade e anúncios existentes;
- aplicar fallback para `RadioStation::stream_url` e
  `Setting::radioSettings()['audio_stream_url']`.

### Etapas F e G — TV

- criar componentes compartilhados de broadcast;
- manter AzuraCast fora da TV;
- ampliar serviços existentes com health e controle seguro;
- usar locks e filas/supervisor em produção;
- adicionar configuração MediaMTX/SRS sem instalá-los dentro do AzuraCast.

## Riscos principais

1. **Docker indisponível:** bloqueia instalação local até Docker Desktop/WSL2.
2. **Versão da API:** endpoints podem variar; toda dependência deve ficar no
   cliente AzuraCast.
3. **Segredos:** token e chaves nunca podem chegar ao HTML, JavaScript ou logs.
4. **SSRF:** a base URL é privilegiada e deve ser editável somente por Super
   Admin, com validação de esquema e host.
5. **Indisponibilidade:** chamadas externas não podem quebrar painel ou player;
   cache, timeout, circuit breaker e fallback são obrigatórios.
6. **Estado duplicado:** a grade local e playlists AzuraCast têm finalidades
   diferentes e não devem ser sincronizadas destrutivamente.
7. **Processos de TV:** iniciar FFmpeg pelo request web não é aceitável em
   produção; usar job e supervisor.
8. **Árvore de trabalho existente:** o repositório já contém muitas alterações
   não commitadas de fases anteriores. Commits desta integração devem incluir
   somente arquivos do escopo AzuraCast/TV correspondente.

## Decisões da auditoria

- preservar SQLite e todas as tabelas existentes;
- não extrair o ZIP para o projeto;
- não modificar `.env`;
- não usar iframe para painel;
- manter AzuraCast como serviço independente;
- manter TV no LuziCity;
- começar a implementação pela infraestrutura, depois cliente e somente então UI;
- exigir testes automatizados sem dependência de AzuraCast real.

## Critério de saída da Etapa A

- base de rádio, TV, filas, scheduler, FFmpeg, segurança e testes auditada;
- conflitos e lacunas documentados;
- branch dedicada criada;
- nenhuma migration, rota, configuração funcional ou dado alterado;
- ambiente identificado como bloqueado para instalação real até Docker estar
  disponível.

