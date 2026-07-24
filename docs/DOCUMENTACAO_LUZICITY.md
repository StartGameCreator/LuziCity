# Luzicity - Documentacao do Projeto

Atualizado em: 09/07/2026

## 1. Visao Geral

Luzicity e uma plataforma de noticias, radio web, classificados, imoveis, RSS, publicidade, login social e apoio editorial com IA.

O projeto atual de trabalho esta em:

```text
C:\Users\Elvis\Documents\Codex\2026-06-26\tipografia-moderna-leitura-confort-vel-foco\LuziCityLaravel13
```

Endereco local padrao:

```text
http://127.0.0.1:9001
```

Painel administrativo:

```text
http://127.0.0.1:9001/admin
```

Banco atual:

```text
database/database.sqlite
```

## 2. Tecnologias

- Laravel 13
- PHP 8.5 local
- SQLite local
- Blade
- Laravel Socialite
- Spatie Laravel Permission
- IA editorial com ChatGPT, Gemini e Copilot configuraveis
- RSS com importacao para banco
- Layout responsivo, mobile-first, com modo claro/escuro

## 3. Como Rodar

Na pasta `LuziCityLaravel13`, use os arquivos BAT:

```text
1_instalar_dependencias_laravel13.bat
2_iniciar_luzicity_laravel13_9001.bat
6_iniciar_somente_servidor_9001.bat
7_testar_rss_e_internet.bat
```

Fluxo recomendado:

1. Execute `1_instalar_dependencias_laravel13.bat` apenas quando precisar instalar dependencias.
2. Execute `2_iniciar_luzicity_laravel13_9001.bat` para iniciar o site.
3. Abra `http://127.0.0.1:9001`.
4. Se quiser apenas religar o servidor, use `6_iniciar_somente_servidor_9001.bat`.
5. Para diagnosticar RSS/internet, use `7_testar_rss_e_internet.bat`.

## 4. Acessos Principais

Area publica:

```text
/                         Home de noticias
/login                    Login e cadastro
/quem-somos               Quem Somos
/radio                    Radio Web e Luzicity Messenger
/cidades/{cidade}         Noticias por cidade
/classificados-veiculos   Classificados de veiculos
/imoveis                  Compra, venda e aluguel de imoveis
```

Area logada:

```text
/dashboard
/classificados-veiculos/anunciar/novo
/imoveis/anunciar/novo
```

Area administrativa:

```text
/admin
/admin/saude-do-sistema
/admin/users
/admin/social-links
/admin/social-login
/admin/tracking-pixels
/admin/company-info
/admin/site-content
/admin/ia
/admin/categories
/admin/tags
/admin/rss-feeds
/admin/importacao-rss
/admin/radio
/admin/media-banners
/admin/classificados-veiculos
/admin/imoveis
/admin/news
```

## 5. Perfis de Usuario

O sistema usa permissoes com papeis:

- Super Admin
- Admin
- Jornalista
- Colunista
- Anunciante
- Patrocinador
- Usuario comum

Regras importantes:

- Assinante nao ve blocos Google Ads.
- Patrocinador tambem tem experiencia sem Google Ads.
- Patrocinadores locais continuam aparecendo, pois sao publicidade direta do portal.
- Administradores controlam usuarios, conteudo, RSS, radio, banners e configuracoes.

## 6. Modulos Funcionais

### Home

A home exibe:

- Menu superior estilo Windows 11
- Modo claro/escuro
- Botao de instalacao PWA
- Cidades
- Redes sociais
- Loja
- Comercio Local
- Quem Somos
- Carrosseis de YouTube/Facebook
- Publicidade Google Ads
- Noticias editoriais
- RSS importado
- Fotos de eventos
- Classificados de veiculos
- Imoveis
- Patrocinadores locais
- Rodape com dados da empresa

### Login Social

Provedores previstos:

- Microsoft
- Apple
- Google
- Facebook
- Instagram
- TikTok

Configuracao:

```text
Backend > Login Social
```

Cada provedor pode receber Client ID, Client Secret, URL de retorno e status ativo/inativo.

### Links do Site

Configuracao:

```text
Backend > Links do Site
```

Controla:

- Facebook
- Instagram
- TikTok
- YouTube
- Kwai
- Rumble
- DLive
- Apple Music
- Deezer
- Loja
- Comercio Local

### IA Editorial

Configuracao:

```text
Backend > IA
```

Suporta:

- ChatGPT
- Gemini
- Copilot

Uso:

- Noticias
- Quem Somos
- Anuncios de veiculos
- Anuncios de imoveis

Observacao: se uma API responder erro de quota, chave invalida ou bloqueio de rede, o painel deve mostrar o erro de forma clara.

### RSS

Configuracoes:

```text
Backend > RSS
Backend > Importacao RSS
```

Fluxo correto:

1. Cadastrar fonte RSS.
2. Clicar em `Importar e atualizar RSS agora`.
3. Conferir as noticias em `Importacao RSS`.
4. Editar imagem da noticia quando necessario.
5. Home exibe as noticias importadas do banco.

Se as noticias nao atualizarem, verificar:

- Internet da maquina
- Porta HTTPS 443
- Firewall/antivirus/proxy
- Arquivo `7_testar_rss_e_internet.bat`

### Radio Web

Area publica:

```text
/radio
```

Recursos:

- Video/audio do locutor
- Controles para economizar dados
- Luzicity Messenger
- Salas por regiao/tema
- Falar para todos
- Falar reservadamente
- Envio de foto somente dentro da sala
- Monitoria visivel nas regras da sala
- Bipe ao receber mensagem marcada para o usuario
- Atualizacao do chat a cada 5 segundos

Configuracao:

```text
Backend > Radio
```

### Classificados de Veiculos

Area publica:

```text
/classificados-veiculos
```

Recursos:

- Carros
- Motos
- Embarcacoes nauticas
- Logos de marcas
- Busca por marca/tipo/filtros
- Anuncio logado
- Upload por smartphone
- Iframe YouTube/Facebook por anuncio
- Copy com IA
- Limite de anuncios configuravel
- Area de patrocinadores/lojas locais

Configuracao:

```text
Backend > Veiculos
```

### Imoveis

Area publica:

```text
/imoveis
```

Recursos:

- Compra
- Venda
- Aluguel
- Anuncio logado
- Fotos
- Video/iframe horizontal ou vertical
- Copy com IA
- Moderacao no backend

Configuracao:

```text
Backend > Imoveis
```

### Banners e Midia

Configuracao:

```text
Backend > Banners
```

Controla:

- Carrossel YouTube
- Facebook Reels
- Transmissao especial ao vivo
- Banners de veiculos
- Banners editoriais

### Publicidade

Tipos:

- Google Ads
- Patrocinadores locais
- Lojas locais
- Banners pequenos
- Banners flutuantes/visuais no padrao do site

Regra:

- Google Ads fica oculto para assinantes e patrocinadores logados.
- Patrocinadores locais continuam visiveis.

### Pixels

Configuracao:

```text
Backend > Pixels
```

Suporta:

- Meta Pixel
- TikTok Pixel

### Empresa e Rodape

Configuracao:

```text
Backend > Empresa
```

Campos:

- Copyright
- CNPJ
- Telefone
- WhatsApp
- E-mail
- Endereco

## 7. Estrutura de Pastas

```text
LuziCityLaravel13/
app/
  Http/Controllers/
  Models/
  Services/
bootstrap/
config/
database/
  migrations/
  database.sqlite
Modules/
public/
resources/
  views/
routes/
storage/
tests/
```

## 8. Banco de Dados

Tabelas principais:

- users
- social_accounts
- subscriptions
- journalist_profiles
- columnist_profiles
- advertiser_profiles
- settings
- categories
- tags
- news_articles
- media_banners
- rss_feeds
- rss_imported_articles
- radio_requests
- vehicle_listings
- real_estate_listings
- roles / permissions

## 9. Comandos Uteis

Criar ou atualizar administrador:

```text
php -c .\php-luzicity.ini artisan luzicity:create-admin elvis@luzicity.com.br --name="Elvis Luzicity" --password="Start@Game357"
```

Limpar cache:

```text
php -c .\php-luzicity.ini artisan optimize:clear
```

Importar RSS:

```text
php -c .\php-luzicity.ini artisan luzicity:import-rss --limit=12
```

Sincronizar logos de marcas:

```text
php -c .\php-luzicity.ini artisan luzicity:sync-vehicle-brand-logos
```

## 10. Checklist de Publicacao Futura

Antes de publicar em hospedagem:

- Trocar SQLite por MySQL/PostgreSQL, se a hospedagem exigir.
- Configurar dominio.
- Configurar SSL.
- Ajustar `APP_URL`.
- Cadastrar credenciais reais de login social.
- Cadastrar chaves reais de IA.
- Configurar Google Ads.
- Cadastrar pixels.
- Criar rotina agendada para importar RSS.
- Configurar backup automatico do banco e uploads.
- Revisar politica de privacidade, termos de uso e regras do chat.

## 11. Observacoes Importantes

- O projeto atual esta funcionando sem XAMPP.
- O PHP usado e o instalado em `C:\tools\php85`.
- O banco esta dentro da pasta do projeto.
- Se a internet do navegador funcionar mas RSS/IA falharem, testar a porta 443 com os BATs de diagnostico.
- A pasta `LuziCityLaravel13` e a versao atual de continuidade.
