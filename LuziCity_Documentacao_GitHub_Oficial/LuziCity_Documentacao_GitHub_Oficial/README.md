# LuziCity

Plataforma multimídia de comunicação desenvolvida em Laravel 13. O LuziCity reúne portal de notícias, redação assistida por IA, rádio web, podcasts, TV web, publicidade, assinaturas, analytics, API pública, multisite, aplicativos e produção de jornal em PDF.

> Estado auditado: checkpoint `LuziCity(6).zip`, branch `feature/azuracast-native-integration`, em 27/07/2026.

## Visão geral

O sistema usa o Laravel como núcleo de domínio e administração. A interface é construída principalmente com Blade, CSS e JavaScript empacotados pelo Vite. O banco local atual é SQLite. Filas, cache e integrações externas são configuráveis por ambiente.

### Principais módulos

- CMS editorial, notícias, categorias, tags e versões.
- Central Editorial IA, biblioteca de prompts, memória editorial, provedores, custos e auditoria.
- Sala de redação com pautas, agentes, fontes, verificação, aprovação e calendário.
- Agência assistida por RSS, similaridade, tendências, pré-pautas e fila de aprovação.
- Rádio web, grade, locutores, pedidos, podcasts, narração e publicidade em áudio.
- Integração nativa em desenvolvimento com AzuraCast.
- TV web, canais, transmissões, videoteca, roteiros e recortes.
- Gestão comercial, anunciantes, campanhas, mídia kit, financeiro e conteúdo patrocinado.
- Assinaturas, paywall, benefícios e pagamentos.
- Analytics próprio com privacidade e retenção.
- API pública v1, tokens, webhooks, multisite e recursos mobile.
- Edições impressas, templates e geração de PDF.
- Segurança, observabilidade, backups, filas e verificações de implantação.

## Stack auditada

- PHP `^8.3`
- Laravel `13.18.1`
- Laravel Socialite `5.28.0`
- Spatie Laravel Permission `6.25.0`
- PHPUnit `11.5`
- Vite `5.x`
- Blade + JavaScript + CSS
- SQLite no ambiente atual

## Execução local

```powershell
cd D:\Skill\LuziCity
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=9001
```

Em outra janela:

```powershell
cd D:\Skill\LuziCity
npm run dev
```

Serviços auxiliares, quando necessários:

```powershell
php artisan queue:work
php artisan schedule:work
```

A aplicação fica disponível em `http://127.0.0.1:9001` e o painel em `http://127.0.0.1:9001/admin`.

## Instalação

Consulte [Instalação](docs/02-INSTALACAO.md) e [Configuração](docs/03-CONFIGURACAO.md).

## Documentação

- [Visão geral](docs/00-VISAO-GERAL.md)
- [Auditoria do checkpoint](docs/01-AUDITORIA-CHECKPOINT.md)
- [Instalação](docs/02-INSTALACAO.md)
- [Configuração](docs/03-CONFIGURACAO.md)
- [Arquitetura](docs/04-ARQUITETURA.md)
- [Módulos](docs/05-MODULOS.md)
- [Banco de dados](docs/06-BANCO-DE-DADOS.md)
- [Rotas e API](docs/07-ROTAS-E-API.md)
- [Rádio e AzuraCast](docs/08-RADIO-E-AZURACAST.md)
- [TV e vídeo](docs/09-TV-E-VIDEO.md)
- [IA e redação](docs/10-IA-E-REDACAO.md)
- [Segurança](docs/11-SEGURANCA.md)
- [Operação e deploy](docs/12-OPERACAO-E-DEPLOY.md)
- [Testes](docs/13-TESTES.md)
- [Contribuição](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)

## Situação do repositório auditado

O checkpoint contém alterações ainda não consolidadas no Git, arquivos não rastreados e artefatos locais. Antes de publicar, siga o checklist em [Auditoria do checkpoint](docs/01-AUDITORIA-CHECKPOINT.md). Em especial, não envie `.env`, bancos locais, backups, `vendor`, `node_modules` ou pacotes ZIP internos.

## Licença

O `composer.json` declara MIT, mas o checkpoint não contém um arquivo `LICENSE`. Defina formalmente a licença antes de tornar o repositório público.
