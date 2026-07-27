# Rotas e API

Foram identificados 47 arquivos de rotas e 298 declarações diretas capturadas estaticamente. Rotas geradas por `resource`, grupos e closures podem expandir esse total em runtime.

## Arquivos de rotas

- `advertisers.php`
- `agency_approval.php`
- `agency_dashboard.php`
- `ai_agents.php`
- `ai_costs_logs.php`
- `ai_dashboard.php`
- `ai_editorial.php`
- `ai_memory.php`
- `ai_news.php`
- `ai_prompts.php`
- `ai_providers.php`
- `analytics.php`
- `analytics_privacy.php`
- `api.php`
- `audio_advertising.php`
- `azuracast.php`
- `campaigns.php`
- `commercial_finance.php`
- `console.php`
- `editorial_calendar.php`
- `editorial_pitches.php`
- `editorial_room.php`
- `editorial_sources.php`
- `editorial_verification.php`
- `media_kit.php`
- `news_narrations.php`
- `news_workflow.php`
- `paywall.php`
- `podcasts.php`
- `print_editions.php`
- `print_templates.php`
- `queue_monitor.php`
- `radio_dashboard.php`
- `radio_structure.php`
- `rss_pre_pitches.php`
- `rss_trends.php`
- `sponsored_content.php`
- `subscribers.php`
- `subscription_benefits.php`
- `subscription_payments.php`
- `subscription_plans.php`
- `tv.php`
- `tv_dashboard.php`
- `video_clips.php`
- `video_scripts.php`
- `videos.php`
- `web.php`

## Mapa estático

| Arquivo | Método | URI declarada |
|---|---|---|
| `advertisers.php` | `GET` | `/` |
| `advertisers.php` | `GET` | `/novo` |
| `advertisers.php` | `POST` | `/` |
| `advertisers.php` | `GET` | `/{advertiser}` |
| `advertisers.php` | `GET` | `/{advertiser}/editar` |
| `advertisers.php` | `PUT` | `/{advertiser}` |
| `advertisers.php` | `POST` | `/{advertiser}/contatos` |
| `advertisers.php` | `POST` | `/{advertiser}/enderecos` |
| `advertisers.php` | `POST` | `/{advertiser}/historico` |
| `advertisers.php` | `POST` | `/{advertiser}/documentos` |
| `agency_approval.php` | `GET` | `/` |
| `agency_approval.php` | `POST` | `/item/{article}` |
| `agency_approval.php` | `POST` | `/pre-pauta/{prePitch}` |
| `agency_dashboard.php` | `GET` | `/` |
| `agency_dashboard.php` | `PUT` | `/fontes/{feed}/politica` |
| `ai_agents.php` | `GET` | `/` |
| `ai_agents.php` | `POST` | `/pautas/{pitch}/etapas` |
| `ai_agents.php` | `PATCH` | `/etapas/{step}/decidir` |
| `ai_costs_logs.php` | `GET` | `/custos` |
| `ai_costs_logs.php` | `GET` | `/logs` |
| `ai_costs_logs.php` | `GET` | `/logs/{execution}` |
| `ai_dashboard.php` | `GET` | `/ia` |
| `ai_dashboard.php` | `GET` | `/ia/dashboard` |
| `ai_editorial.php` | `GET` | `/ia-editorial` |
| `ai_editorial.php` | `PUT` | `/ia-editorial/provedores/{provider}` |
| `ai_editorial.php` | `PUT` | `/ia-editorial/prompts/{template}` |
| `ai_memory.php` | `GET` | `/` |
| `ai_memory.php` | `POST` | `/perfis` |
| `ai_memory.php` | `GET` | `/perfis/{profile}` |
| `ai_memory.php` | `PUT` | `/perfis/{profile}` |
| `ai_memory.php` | `POST` | `/perfis/{profile}/termos` |
| `ai_memory.php` | `PUT` | `/termos/{term}` |
| `ai_memory.php` | `DELETE` | `/termos/{term}` |
| `ai_memory.php` | `POST` | `/perfis/{profile}/regras` |
| `ai_memory.php` | `PUT` | `/regras/{rule}` |
| `ai_memory.php` | `DELETE` | `/regras/{rule}` |
| `ai_news.php` | `GET` | `/` |
| `ai_news.php` | `POST` | `/gerar` |
| `ai_news.php` | `PUT` | `/memoria-editorial` |
| `ai_prompts.php` | `GET` | `/` |
| `ai_prompts.php` | `GET` | `/novo` |
| `ai_prompts.php` | `POST` | `/` |
| `ai_prompts.php` | `GET` | `/{prompt}` |
| `ai_prompts.php` | `GET` | `/{prompt}/editar` |
| `ai_prompts.php` | `PUT` | `/{prompt}` |
| `ai_prompts.php` | `POST` | `/{prompt}/duplicar` |
| `ai_prompts.php` | `PATCH` | `/{prompt}/alternar` |
| `ai_prompts.php` | `POST` | `/{prompt}/versoes/{version}/restaurar` |
| `ai_prompts.php` | `GET` | `/{prompt}/comparar/versoes` |
| `ai_providers.php` | `GET` | `/` |
| `ai_providers.php` | `GET` | `/{provider}/editar` |
| `ai_providers.php` | `PUT` | `/{provider}` |
| `ai_providers.php` | `POST` | `/{provider}/testar` |
| `analytics.php` | `POST` | `/analytics/coletar` |
| `analytics.php` | `GET` | `/admin/analytics` |
| `analytics_privacy.php` | `GET` | `/privacidade/analytics` |
| `analytics_privacy.php` | `POST` | `/privacidade/analytics/consentimento` |
| `analytics_privacy.php` | `POST` | `/privacidade/analytics/opt-out` |
| `api.php` | `GET` | `/docs` |
| `api.php` | `GET` | `/docs/openapi.yaml` |
| `api.php` | `GET` | `/docs/guide.md` |
| `api.php` | `GET` | `/news` |
| `api.php` | `GET` | `/categories` |
| `api.php` | `GET` | `/videos` |
| `api.php` | `GET` | `/podcasts` |
| `api.php` | `GET` | `/events` |
| `api.php` | `POST` | `/auth/tokens` |
| `api.php` | `POST` | `/mobile/auth/tokens` |
| `api.php` | `GET` | `/auth/me` |
| `api.php` | `DELETE` | `/auth/tokens/current` |
| `api.php` | `GET` | `/feed` |
| `api.php` | `GET` | `/search` |
| `api.php` | `GET` | `/favorites` |
| `api.php` | `GET` | `/profile` |
| `api.php` | `POST` | `/favorites/{news:slug}` |
| `api.php` | `DELETE` | `/favorites/{news:slug}` |
| `api.php` | `POST` | `/notifications/devices` |
| `api.php` | `DELETE` | `/notifications/devices` |
| `api.php` | `PATCH` | `/profile` |
| `audio_advertising.php` | `POST` | `/radio/publicidade/{campaign}/reproducao` |
| `audio_advertising.php` | `GET` | `/` |
| `audio_advertising.php` | `POST` | `/spots` |
| `audio_advertising.php` | `POST` | `/campanhas` |
| `azuracast.php` | `GET` | `/radio/estado` |
| `azuracast.php` | `GET` | `/health` |
| `azuracast.php` | `POST` | `/test` |
| `azuracast.php` | `POST` | `/station/{action}` |
| `campaigns.php` | `GET` | `/` |
| `campaigns.php` | `GET` | `/nova` |
| `campaigns.php` | `POST` | `/` |
| `campaigns.php` | `GET` | `/{campaign}/editar` |
| `campaigns.php` | `PUT` | `/{campaign}` |
| `campaigns.php` | `POST` | `/{campaign}/aprovar` |
| `campaigns.php` | `GET` | `/publicidade/{campaign}/impressao.gif` |
| `campaigns.php` | `GET` | `/publicidade/{campaign}/clique` |
| `commercial_finance.php` | `GET` | `/` |
| `commercial_finance.php` | `POST` | `/` |
| `commercial_finance.php` | `GET` | `/{invoice}` |
| `commercial_finance.php` | `POST` | `/{invoice}/pagamentos` |
| `commercial_finance.php` | `POST` | `/{invoice}/renovar` |
| `commercial_finance.php` | `POST` | `/{invoice}/cancelar` |
| `editorial_calendar.php` | `GET` | `/` |
| `editorial_calendar.php` | `POST` | `/eventos` |
| `editorial_calendar.php` | `POST` | `/sugestoes` |
| `editorial_pitches.php` | `GET` | `/` |
| `editorial_pitches.php` | `GET` | `/nova` |
| `editorial_pitches.php` | `POST` | `/` |
| `editorial_pitches.php` | `GET` | `/{pitch}/editar` |
| `editorial_pitches.php` | `PUT` | `/{pitch}` |
| `editorial_pitches.php` | `PATCH` | `/{pitch}/mover` |
| `editorial_room.php` | `GET` | `/admin/redacao` |
| `editorial_sources.php` | `POST` | `/pautas/{pitch}` |
| `editorial_sources.php` | `POST` | `/{source}/capturar` |
| `editorial_sources.php` | `POST` | `/{source}/afirmacoes` |
| `editorial_verification.php` | `GET` | `/pautas/{pitch}` |
| `editorial_verification.php` | `POST` | `/afirmacoes/{claim}` |
| `media_kit.php` | `GET` | `/midia-kit.pdf` |
| `media_kit.php` | `GET` | `/` |
| `media_kit.php` | `POST` | `/formatos` |
| `media_kit.php` | `PUT` | `/formatos/{format}` |
| `media_kit.php` | `POST` | `/propostas` |
| `media_kit.php` | `GET` | `/propostas/{proposal}` |
| `media_kit.php` | `POST` | `/propostas/{proposal}/aprovar` |
| `media_kit.php` | `GET` | `/propostas/{proposal}/pdf` |
| `news_narrations.php` | `GET` | `/noticias/{news}/audio` |
| `news_narrations.php` | `GET` | `/` |
| `news_narrations.php` | `POST` | `/vozes` |
| `news_narrations.php` | `POST` | `/gerar` |
| `news_narrations.php` | `PATCH` | `/{narration}/revisao` |
| `news_workflow.php` | `GET` | `/{news}/fluxo` |
| `news_workflow.php` | `POST` | `/{news}/fluxo` |
| `paywall.php` | `GET` | `/` |
| `paywall.php` | `PUT` | `/categorias/{category}` |
| `podcasts.php` | `GET` | `/podcasts` |
| `podcasts.php` | `GET` | `/podcasts/{series}/feed.xml` |
| `podcasts.php` | `GET` | `/` |
| `podcasts.php` | `POST` | `/series` |
| `podcasts.php` | `POST` | `/series/{series}/episodios` |
| `print_editions.php` | `GET` | `/` |
| `print_editions.php` | `GET` | `/nova` |
| `print_editions.php` | `POST` | `/` |
| `print_editions.php` | `GET` | `/{printEdition}/editar` |
| `print_editions.php` | `GET` | `/{printEdition}/pdf` |
| `print_editions.php` | `GET` | `/{printEdition}/previa` |
| `print_editions.php` | `POST` | `/{printEdition}/revisao` |
| `print_editions.php` | `POST` | `/{printEdition}/aprovar` |
| `print_editions.php` | `POST` | `/{printEdition}/reabrir` |
| `print_editions.php` | `PUT` | `/{printEdition}` |
| `print_editions.php` | `DELETE` | `/{printEdition}` |
| `print_templates.php` | `GET` | `/` |
| `print_templates.php` | `GET` | `/novo` |
| `print_templates.php` | `POST` | `/` |
| `print_templates.php` | `GET` | `/{printTemplate}/editar` |
| `print_templates.php` | `PUT` | `/{printTemplate}` |
| `print_templates.php` | `DELETE` | `/{printTemplate}` |
| `queue_monitor.php` | `GET` | `/` |
| `queue_monitor.php` | `POST` | `/falhas/reprocessar-todas` |
| `queue_monitor.php` | `POST` | `/falhas/limpar-antigas` |
| `queue_monitor.php` | `POST` | `/falhas/{uuid}/reprocessar` |
| `queue_monitor.php` | `DELETE` | `/falhas/{uuid}` |
| `radio_dashboard.php` | `GET` | `/admin/radio-central` |
| `radio_structure.php` | `GET` | `/` |
| `radio_structure.php` | `PUT` | `/emissora` |
| `radio_structure.php` | `POST` | `/locutores` |
| `radio_structure.php` | `POST` | `/programas` |
| `radio_structure.php` | `POST` | `/grade` |
| `rss_pre_pitches.php` | `GET` | `/` |
| `rss_pre_pitches.php` | `POST` | `/de-artigo/{article}` |
| `rss_trends.php` | `GET` | `/tendencias-rss` |
| `sponsored_content.php` | `GET` | `/` |
| `sponsored_content.php` | `POST` | `/{article}/aprovar` |
| `sponsored_content.php` | `POST` | `/{article}/revogar` |
| `subscribers.php` | `GET` | `/` |
| `subscribers.php` | `POST` | `/cancelar` |
| `subscribers.php` | `GET` | `/admin/assinaturas/assinantes` |
| `subscription_benefits.php` | `GET` | `/` |
| `subscription_benefits.php` | `POST` | `/{benefit}/resgatar` |
| `subscription_benefits.php` | `GET` | `/` |
| `subscription_benefits.php` | `POST` | `/` |
| `subscription_benefits.php` | `PUT` | `/{benefit}` |
| `subscription_payments.php` | `POST` | `/pagamentos/webhook/mercado-pago` |
| `subscription_payments.php` | `POST` | `/minha-assinatura/pagar` |
| `subscription_payments.php` | `GET` | `/minha-assinatura/retorno/{status}` |
| `subscription_payments.php` | `POST` | `/admin/assinaturas/pagamentos/{payment}/reembolsar` |
| `subscription_plans.php` | `GET` | `/assinaturas/planos` |
| `subscription_plans.php` | `GET` | `/` |
| `subscription_plans.php` | `POST` | `/` |
| `subscription_plans.php` | `PUT` | `/{plan}` |
| `tv.php` | `GET` | `/tv` |
| `tv.php` | `GET` | `/` |
| `tv.php` | `POST` | `/canais` |
| `tv.php` | `POST` | `/canais/{channel}/transmissoes` |
| `tv_dashboard.php` | `GET` | `/admin/tv-central` |
| `video_clips.php` | `GET` | `/shorts/{clip}` |
| `video_clips.php` | `GET` | `/` |
| `video_clips.php` | `POST` | `/` |
| `video_clips.php` | `POST` | `/{clip}/repetir` |
| `video_clips.php` | `PATCH` | `/{clip}/revisao` |
| `video_scripts.php` | `GET` | `/` |
| `video_scripts.php` | `POST` | `/` |
| `video_scripts.php` | `PATCH` | `/{script}/revisao` |
| `video_scripts.php` | `GET` | `/{script}/teleprompter` |
| `videos.php` | `GET` | `/videos` |
| `videos.php` | `GET` | `/videos/{video}` |
| `videos.php` | `GET` | `/` |
| `videos.php` | `POST` | `/categorias` |
| `videos.php` | `POST` | `/series` |
| `videos.php` | `POST` | `/itens` |
| `videos.php` | `POST` | `/playlists` |
| `web.php` | `GET` | `/sitemap.xml` |
| `web.php` | `GET` | `/.well-known/assetlinks.json` |
| `web.php` | `GET` | `/.well-known/apple-app-site-association` |
| `web.php` | `GET` | `/robots.txt` |
| `web.php` | `GET` | `/health/ready` |
| `web.php` | `GET` | `/` |
| `web.php` | `GET` | `/offline` |
| `web.php` | `GET` | `/firebase-messaging-sw.js` |
| `web.php` | `POST` | `/push/subscriptions` |
| `web.php` | `DELETE` | `/push/subscriptions` |
| `web.php` | `GET` | `/buscar` |
| `web.php` | `GET` | `/buscar/sugestoes` |
| `web.php` | `GET` | `/quem-somos` |
| `web.php` | `GET` | `/fotos-eventos` |
| `web.php` | `GET` | `/radio` |
| `web.php` | `POST` | `/radio/pedidos` |
| `web.php` | `GET` | `/cidades/{city}` |
| `web.php` | `GET` | `/noticias/{news:slug}` |
| `web.php` | `GET` | `/classificados-veiculos` |
| `web.php` | `GET` | `/classificados-veiculos/{vehicle}` |
| `web.php` | `GET` | `/imoveis` |
| `web.php` | `GET` | `/imoveis/{property}` |
| `web.php` | `GET` | `/login` |
| `web.php` | `POST` | `/login` |
| `web.php` | `POST` | `/register` |
| `web.php` | `GET` | `/login/{provider}` |
| `web.php` | `GET` | `/login/{provider}/callback` |
| `web.php` | `POST` | `/logout` |
| `web.php` | `GET` | `/dashboard` |
| `web.php` | `POST` | `/ai-writing` |
| `web.php` | `GET` | `/classificados-veiculos/anunciar/novo` |
| `web.php` | `POST` | `/classificados-veiculos/anunciar` |
| `web.php` | `GET` | `/imoveis/anunciar/novo` |
| `web.php` | `POST` | `/imoveis/anunciar` |
| `web.php` | `GET` | `/` |
| `web.php` | `GET` | `/saude-do-sistema` |
| `web.php` | `GET` | `/notificacoes-push` |
| `web.php` | `POST` | `/notificacoes-push/enviar` |
| `web.php` | `GET` | `/users` |
| `web.php` | `PUT` | `/users/{user}` |
| `web.php` | `GET` | `/social-links` |
| `web.php` | `PUT` | `/social-links` |
| `web.php` | `GET` | `/social-login` |
| `web.php` | `PUT` | `/social-login` |
| `web.php` | `GET` | `/tracking-pixels` |
| `web.php` | `PUT` | `/tracking-pixels` |
| `web.php` | `GET` | `/company-info` |
| `web.php` | `PUT` | `/company-info` |
| `web.php` | `GET` | `/sites` |
| `web.php` | `POST` | `/sites` |
| `web.php` | `PUT` | `/sites/{site}` |
| `web.php` | `GET` | `/upload-diagnostico` |
| `web.php` | `GET` | `/site-content` |
| `web.php` | `PUT` | `/site-content` |
| `web.php` | `GET` | `/configuracoes/ia` |
| `web.php` | `PUT` | `/ia` |
| `web.php` | `POST` | `/ia/testar` |
| `web.php` | `GET` | `/categories` |
| `web.php` | `POST` | `/categories` |
| `web.php` | `PUT` | `/categories/{category}` |
| `web.php` | `GET` | `/tags` |
| `web.php` | `POST` | `/tags` |
| `web.php` | `PUT` | `/tags/{tag}` |
| `web.php` | `GET` | `/rss-feeds` |
| `web.php` | `POST` | `/rss-feeds` |
| `web.php` | `POST` | `/rss-feeds/refresh` |
| `web.php` | `PUT` | `/rss-feeds/{rssFeed}` |
| `web.php` | `GET` | `/importacao-rss` |
| `web.php` | `POST` | `/importacao-rss/importar` |
| `web.php` | `PUT` | `/importacao-rss/{article}` |
| `web.php` | `GET` | `/radio` |
| `web.php` | `PUT` | `/radio` |
| `web.php` | `GET` | `/media-banners` |
| `web.php` | `PUT` | `/media-banners/transmissao-home` |
| `web.php` | `POST` | `/media-banners` |
| `web.php` | `PUT` | `/media-banners/{mediaBanner}` |
| `web.php` | `GET` | `/classificados-veiculos` |
| `web.php` | `PUT` | `/classificados-veiculos/configuracao` |
| `web.php` | `POST` | `/classificados-veiculos/logos` |
| `web.php` | `PUT` | `/classificados-veiculos/{vehicle}` |
| `web.php` | `GET` | `/imoveis` |
| `web.php` | `PUT` | `/imoveis/{property}` |
| `web.php` | `GET` | `/admin/global` |
| `web.php` | `GET` | `/` |
| `web.php` | `GET` | `/create` |
| `web.php` | `POST` | `/` |
| `web.php` | `GET` | `/{news}/edit` |
| `web.php` | `PUT` | `/{news}` |
| `web.php` | `POST` | `/{news}/distribuir` |

## API v1

A API é registrada em `routes/api.php` e usa autenticação por token e abilities nos endpoints protegidos. Consulte também `docs/api/openapi.yaml` no projeto original.

## Recomendações

- Execute `php artisan route:list` após qualquer mudança.
- Não duplicar prefixos ou nomes de rota.
- Comandos operacionais devem usar POST/PUT/DELETE, CSRF, autorização e rate limit.
- Manter respostas públicas cacheáveis separadas das administrativas.
