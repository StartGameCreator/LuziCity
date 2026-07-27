# Visão geral do LuziCity

## Propósito

O LuziCity é uma plataforma de mídia local e regional que centraliza produção editorial, publicação, distribuição, rádio, TV, publicidade, assinaturas e medição de audiência. O projeto evoluiu de um portal de notícias para um ecossistema multimídia administrado por uma única aplicação Laravel.

## Dimensão do checkpoint

| Elemento | Quantidade auditada |
|---|---:|
| Models | 97 |
| Controllers | 104 |
| Services | 52 |
| Middlewares | 10 |
| Commands | 10 |
| Jobs | 5 |
| Migrations | 82 |
| Arquivos de rotas | 47 |
| Views Blade | 122 |
| Testes PHP | 80 |

## Áreas funcionais

1. **Conteúdo público:** home, notícias, cidades, busca, rádio, TV, podcasts, classificados e imóveis.
2. **Administração:** usuários, configurações, conteúdo, saúde do sistema e sites.
3. **Redação:** notícias, pautas, fontes, verificação, aprovação e calendário.
4. **IA:** provedores, prompts, memória, agentes, métricas, custos e auditoria.
5. **Agência assistida:** RSS, coleta, similaridade, tendências, pré-pautas e aprovação.
6. **Broadcast:** rádio, podcasts, narração, publicidade em áudio, TV e vídeo.
7. **Comercial:** anunciantes, campanhas, mídia kit, financeiro e patrocínio.
8. **Receita recorrente:** planos, paywall, assinantes, benefícios e pagamentos.
9. **Dados e distribuição:** analytics, API v1, webhooks, multisite e mobile.
10. **Produção e operação:** PDF impresso, segurança, filas, cache, observabilidade e backups.

## Princípios observados

- Separação de controllers e services em módulos críticos.
- Autorização por papéis usando Spatie Permission.
- SQLite preservado no ambiente atual.
- Revisão humana obrigatória nos fluxos editoriais assistidos por IA.
- Integrações externas configuradas por variáveis de ambiente.
- Endpoints públicos e administrativos separados.
- Evolução por migrations incrementais.
