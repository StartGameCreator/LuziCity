# LuziCity — Plano Organizado de Desenvolvimento Pendente
## Documento para execução no Work / Codex

**Base atual confirmada**
- Fase 10.0 — Fundação IA Editorial: instalada.
- Fase 10.1 — Motor Editorial IA: instalada e consolidada.
- Projeto principal: `D:\Skill\LuziCity`
- Laravel: `http://127.0.0.1:9001`
- Vite: `http://127.0.0.1:5174`

---

# REGRAS OBRIGATÓRIAS PARA TODOS OS PATCHES

Cada etapa deve ser entregue em um ZIP separado, contendo:

```text
NOME-DO-PATCH.zip
├── INSTALAR-*.bat
├── ABRIR-BACKUP-PARA-ROLLBACK.bat
├── LEIA-ME.txt
├── CHANGELOG.md
├── app/
├── database/
├── resources/
├── routes/
├── public/
└── scripts/
```

O instalador deve:

1. Usar a raiz `D:\Skill\LuziCity`.
2. Verificar se `artisan` existe.
3. Criar backup com data e hora em:
   `D:\Skill\LuziCity\backups\antes-<fase>-AAAAMMDD-HHMMSS`
4. Copiar apenas os arquivos necessários.
5. Aplicar alterações em arquivos existentes com script PHP defensivo.
6. Não duplicar rotas, imports, menus ou campos.
7. Executar:
   ```bat
   php artisan optimize:clear
   php artisan migrate --force
   php artisan route:list
   php artisan view:clear
   php artisan view:cache
   php artisan optimize:clear
   ```
8. Parar com erro quando uma etapa crítica falhar.
9. Nunca apagar o banco SQLite.
10. Nunca sobrescrever `.env`.
11. Não publicar automaticamente conteúdo de IA.
12. Exigir revisão humana antes de qualquer publicação.
13. Manter compatibilidade com SQLite.
14. Validar sintaxe de todos os arquivos PHP com `php -l`.
15. Criar rollback seguro ou abrir a pasta correta de backup.

---

# FASE 10.2 — CENTRAL EDITORIAL IA

## Fase 10.2.1 — Dashboard Editorial IA

### Objetivo
Criar o painel central da IA Editorial.

### Criar
```text
app/Http/Controllers/AdminAiDashboardController.php
app/Services/AI/AiEditorialMetricsService.php
resources/views/admin/ai/dashboard.blade.php
routes/ai_dashboard.php
database/migrations/2026_XX_XX_XXXXXX_add_metrics_to_ai_executions.php
```

### Funcionalidades
- Execuções hoje, semana e mês.
- Execuções com sucesso e erro.
- Provedor mais utilizado.
- Tempo médio de resposta.
- Tokens de entrada e saída.
- Custo estimado.
- Notícias geradas.
- Últimas execuções.
- Últimos erros.
- Filtro por período.
- Filtro por provedor.
- Filtro por usuário.
- Atalhos para:
  - gerar notícia;
  - prompts;
  - provedores;
  - memória editorial;
  - custos;
  - logs.

### Banco
Adicionar defensivamente em `ai_executions`, quando ainda não existirem:
- `input_tokens`
- `output_tokens`
- `total_tokens`
- `estimated_cost`
- `duration_ms`
- `model`
- `status_code`
- `error_type`

### Rotas
```php
GET /admin/ia
GET /admin/ia/dashboard
```

### Permissões
- Super Admin
- Admin
- Jornalista: somente leitura das próprias execuções, conforme decisão do projeto.

### Aceite
- Dashboard abre sem erro com banco vazio.
- Métricas não quebram quando campos forem nulos.
- Filtros funcionam.
- Sem consultas N+1.
- Menu administrativo aponta para a Central Editorial IA.

---

## Fase 10.2.2 — Biblioteca e Versionamento de Prompts

### Objetivo
Transformar `ai_prompt_templates` em biblioteca editorial completa.

### Criar
```text
app/Http/Controllers/AdminAiPromptController.php
app/Models/AiPromptVersion.php
app/Services/AI/AiPromptVersionService.php
resources/views/admin/ai/prompts/index.blade.php
resources/views/admin/ai/prompts/form.blade.php
resources/views/admin/ai/prompts/show.blade.php
resources/views/admin/ai/prompts/versions.blade.php
routes/ai_prompts.php
database/migrations/2026_XX_XX_XXXXXX_create_ai_prompt_versions_table.php
```

### Funcionalidades
- Listar prompts.
- Criar prompt.
- Editar prompt.
- Ativar/desativar.
- Duplicar.
- Testar prompt.
- Versionar automaticamente a cada alteração.
- Comparar versões.
- Restaurar versão.
- Categorizar por finalidade:
  - notícia completa;
  - título;
  - resumo;
  - SEO;
  - revisão;
  - tradução;
  - redes sociais;
  - roteiro;
  - áudio;
  - vídeo.
- Variáveis permitidas e documentadas.
- Marcação do prompt padrão por recurso.

### Banco — `ai_prompt_versions`
- `id`
- `ai_prompt_template_id`
- `version`
- `system_prompt`
- `user_prompt`
- `variables`
- `change_notes`
- `created_by`
- timestamps

### Segurança
- Escapar HTML exibido.
- Não permitir variáveis arbitrárias executáveis.
- Validar placeholders.
- Registrar usuário que alterou ou restaurou.

### Aceite
- Editar cria nova versão.
- Restaurar não apaga histórico.
- Prompt ativo é usado pelo motor editorial.
- Teste do prompt não cria notícia automaticamente.

---

## Fase 10.2.3 — Memória Editorial Avançada

### Objetivo
Expandir `ai_editorial_profiles` para controlar identidade e regras editoriais.

### Criar
```text
app/Http/Controllers/AdminAiEditorialMemoryController.php
app/Models/AiEditorialTerm.php
app/Models/AiEditorialRule.php
app/Services/AI/AiEditorialMemoryService.php
resources/views/admin/ai/memory/index.blade.php
resources/views/admin/ai/memory/terms.blade.php
resources/views/admin/ai/memory/rules.blade.php
routes/ai_memory.php
database/migrations/2026_XX_XX_XXXXXX_create_ai_editorial_memory_tables.php
```

### Funcionalidades
- Perfil editorial padrão.
- Perfis por categoria.
- Tom editorial.
- Público-alvo.
- Região prioritária.
- Termos preferenciais.
- Termos proibidos.
- Nomes e grafias oficiais.
- Regras jurídicas.
- Regras de atribuição de fontes.
- Regras contra sensacionalismo.
- Tratamento de menores.
- Tratamento de vítimas.
- Política para acusações e investigações.
- Política para conteúdo político.
- Política de correções.
- Instruções específicas por categoria.

### Banco
`ai_editorial_rules`
- profile_id
- name
- rule_type
- instruction
- priority
- active

`ai_editorial_terms`
- profile_id
- term
- replacement
- type: preferred, forbidden, spelling
- context
- active

### Integração
O `AiNewsGenerator` deve consultar a memória compilada e anexá-la ao prompt.

### Aceite
- Termo proibido gera alerta.
- Grafia oficial é aplicada ou sinalizada.
- Regras aparecem nas notas de revisão.
- Nenhuma regra publica ou altera artigo sem aprovação humana.

---

## Fase 10.2.4 — Gerenciador de Provedores e Limites

### Objetivo
Completar o gerenciamento de ChatGPT, Gemini e Copilot.

### Criar
```text
app/Http/Controllers/AdminAiProviderController.php
app/Services/AI/AiProviderHealthService.php
app/Services/AI/AiProviderQuotaService.php
resources/views/admin/ai/providers/index.blade.php
resources/views/admin/ai/providers/form.blade.php
resources/views/admin/ai/providers/test.blade.php
routes/ai_providers.php
database/migrations/2026_XX_XX_XXXXXX_extend_ai_providers_for_limits.php
```

### Campos
- nome;
- slug;
- ativo;
- prioridade;
- modelo;
- URL base;
- timeout;
- tentativas;
- limite diário;
- limite mensal;
- custo por milhão de tokens de entrada;
- custo por milhão de tokens de saída;
- última verificação;
- estado da conexão;
- mensagem da última falha.

### Chaves
- Nunca gravar chave em texto puro no banco.
- Preferir referências às configurações já existentes ou valor criptografado.
- Nunca mostrar a chave completa na tela ou log.
- Nunca incluir chave em backup textual ou mensagem de erro.

### Funcionalidades
- Ativar/desativar.
- Definir prioridade.
- Testar conexão.
- Ver modelo ativo.
- Ver consumo diário/mensal.
- Bloquear execução quando limite for atingido.
- Fallback opcional para o próximo provedor.
- Circuit breaker após falhas repetidas.

### Aceite
- Teste de conexão não gera conteúdo.
- Chave nunca aparece em HTML ou logs.
- Provedor desativado não pode ser chamado.
- Limite atingido retorna mensagem compreensível.
- Fallback não duplica cobrança nem execução registrada.

---

## Fase 10.2.5 — Custos, Logs e Auditoria

### Objetivo
Criar rastreabilidade completa das ações de IA.

### Criar
```text
app/Http/Controllers/AdminAiCostController.php
app/Http/Controllers/AdminAiLogController.php
app/Services/AI/AiCostCalculator.php
app/Services/AI/AiAuditService.php
resources/views/admin/ai/costs/index.blade.php
resources/views/admin/ai/logs/index.blade.php
resources/views/admin/ai/logs/show.blade.php
routes/ai_costs_logs.php
database/migrations/2026_XX_XX_XXXXXX_create_ai_audit_events_table.php
```

### Relatórios
- custo por dia;
- custo por mês;
- custo por usuário;
- custo por provedor;
- custo por recurso;
- tokens por recurso;
- tempo médio;
- taxa de erro;
- ranking de prompts.

### Auditoria
Registrar:
- usuário;
- ação;
- provedor;
- modelo;
- template;
- parâmetros seguros;
- resultado;
- erro;
- IP;
- user agent;
- data/hora;
- artigo associado.

### Privacidade
- Não salvar chave.
- Permitir mascarar dados pessoais.
- Não registrar texto integral da fonte em logs comuns.
- Guardar payload completo somente quando necessário e com controle de acesso.

### Aceite
- Relatório funciona sem preço configurado.
- Valores usam decimal, não float.
- Exportação CSV opcional.
- Logs com paginação e filtros.
- Somente Admin/Super Admin visualiza custos globais.

---

## Fase 10.2.6 — Consolidação da Central Editorial IA

### Objetivo
Unificar os submódulos em uma única experiência.

### Entregas
- Menu “Central Editorial IA”.
- Dashboard inicial.
- Navegação lateral ou abas:
  - Visão geral;
  - Gerar notícia;
  - Prompts;
  - Memória;
  - Provedores;
  - Custos;
  - Logs.
- Permissões consolidadas.
- Testes Feature.
- Revisão de rotas.
- Correção de inconsistências.
- Documentação da Fase 10.2.
- Patch de consolidação e compatibilidade.

### Aceite final da Fase 10.2
- Todos os submódulos acessíveis.
- Nenhuma rota duplicada.
- Nenhuma migration conflitante.
- Compatibilidade com Fases 10.0 e 10.1.
- Projeto inicia normalmente.
- `php artisan test` passa.
- `npm run build` passa.
- Geração continua exigindo revisão humana.

---

# FASE 10.3 — SALA DE REDAÇÃO INTELIGENTE

## Fase 10.3.1 — Pautas e Quadro Editorial

### Criar
- Cadastro de pautas.
- Status:
  - ideia;
  - em pesquisa;
  - em redação;
  - em revisão;
  - aprovada;
  - agendada;
  - publicada;
  - descartada.
- Prioridade.
- Categoria.
- Responsável.
- Prazo.
- Fontes.
- Checklist.
- Relação com notícia.

### Banco sugerido
- `editorial_pitches`
- `editorial_pitch_sources`
- `editorial_pitch_tasks`
- `editorial_pitch_comments`

### Interface
Quadro Kanban editorial, com alternativa em lista para dispositivos móveis.

---

## Fase 10.3.2 — Agentes Editoriais

### Agentes
- Editor-chefe.
- Repórter.
- Pesquisador.
- Verificador.
- Reescritor.
- Revisor.
- Especialista SEO.
- Social media.

### Regras
- Agentes não publicam.
- Cada agente produz uma etapa.
- Todas as etapas ficam registradas.
- Saídas podem ser aceitas, rejeitadas ou refeitas.
- Editor humano controla a sequência.

### Banco sugerido
- `ai_agents`
- `ai_agent_runs`
- `ai_agent_steps`

---

## Fase 10.3.3 — Pesquisa e Fontes

### Funcionalidades
- Adicionar URLs e documentos.
- Extrair metadados.
- Resumir fontes.
- Identificar contradições.
- Relacionar afirmações às fontes.
- Classificar confiabilidade.
- Exigir pelo menos uma fonte para conteúdos factuais, conforme perfil.

### Segurança
- Proteção contra SSRF.
- Bloqueio de IPs internos.
- Limite de tamanho e tempo.
- Sanitização do conteúdo.
- Não reproduzir material protegido integralmente.

---

## Fase 10.3.4 — Verificação Editorial

### Funcionalidades
- Lista de afirmações factuais.
- Estado:
  - confirmado;
  - não confirmado;
  - conflitante;
  - opinião;
  - requer revisão.
- Fonte vinculada.
- Alertas de datas, nomes, números e citações.
- Comparação entre texto e fonte.
- Relatório de verificação.

### Observação
O sistema deve sinalizar; não deve afirmar “verdadeiro” automaticamente quando não houver evidência suficiente.

---

## Fase 10.3.5 — Revisão, Aprovação e Publicação

### Fluxo
```text
Rascunho IA
→ Revisão do jornalista
→ Verificação
→ Revisão editorial
→ Aprovação
→ Agendamento
→ Publicação
```

### Funcionalidades
- Aprovar.
- Rejeitar.
- Solicitar alterações.
- Histórico de revisões.
- Comparação entre versões.
- Registro de quem aprovou.
- Agendamento.
- Bloqueio de autopublicação.

---

## Fase 10.3.6 — Calendário Editorial

### Funcionalidades
- Visão mensal, semanal e diária.
- Notícias agendadas.
- Pautas.
- Eventos locais.
- Datas comemorativas.
- Responsáveis.
- Alertas de prazo.
- Sugestões de pauta por IA, sempre como sugestão.

---

## Fase 10.3.7 — Consolidação da Sala de Redação

### Entregas
- Dashboard da redação.
- Kanban.
- Agentes.
- Fontes.
- Verificação.
- Aprovação.
- Calendário.
- Testes e documentação.

---

# FASE 10.4 — AGÊNCIA AUTÔNOMA ASSISTIDA

A palavra “autônoma” deve signific automação assistida, nunca publicação irrestrita.

## Fase 10.4.1 — Fontes RSS
- Cadastro de feeds.
- Categorias.
- Frequência.
- Ativar/desativar.
- Última coleta.
- Falhas.
- Deduplicação.

## Fase 10.4.2 — Coletor RSS
- Jobs em fila.
- Scheduler.
- Normalização.
- Limites.
- Registro de origem.
- Proteção contra duplicação.
- Nunca copiar artigo integral automaticamente.

## Fase 10.4.3 — Detecção de Duplicidade e Similaridade
- Hash de URL.
- Hash de título.
- Similaridade textual.
- Agrupamento por assunto.
- Notícia principal e fontes relacionadas.

## Fase 10.4.4 — Tendências e Alertas
- Assuntos mais recorrentes.
- Crescimento de menções.
- Tendências por local/categoria.
- Alertas no painel.
- Sugestões de pauta.

## Fase 10.4.5 — Geração de Pré-pautas
- Transformar item coletado em pré-pauta.
- Resumo.
- Fontes.
- perguntas a apurar;
- riscos;
- relevância local;
- recomendação editorial.
- Nunca criar notícia publicada automaticamente.

## Fase 10.4.6 — Fila de Aprovação
- Itens coletados.
- Pré-pautas.
- Rascunhos.
- Aprovar para redação.
- Rejeitar.
- Arquivar.
- Bloquear autopublicação.

## Fase 10.4.7 — Consolidação da Agência Assistida
- Scheduler.
- Queue.
- Dashboard.
- Logs.
- Limites.
- Política de fontes.
- Testes.

---

# FASE 11 — RÁDIO E ÁUDIO

## 11.1 Estrutura da Rádio
- emissora;
- programas;
- locutores;
- grade;
- player ao vivo;
- status on-air.

## 11.2 Podcasts
- séries;
- episódios;
- capa;
- áudio;
- descrição;
- RSS de podcast;
- publicação.

## 11.3 Texto para Áudio
- narração de notícias;
- vozes configuráveis;
- fila;
- revisão;
- armazenamento;
- custo.

## 11.4 Publicidade em Áudio
- spots;
- campanhas;
- programação;
- relatórios.

## 11.5 Consolidação Rádio
- painel;
- player;
- grade;
- podcasts;
- áudio IA;
- testes.

---

# FASE 12 — TV WEB E VÍDEO

## 12.1 Canais e Transmissões
- YouTube Live;
- Vimeo;
- RTMP;
- embed;
- agenda;
- ao vivo.

## 12.2 Videoteca
- vídeos;
- séries;
- playlists;
- categorias;
- miniaturas;
- legendas.

## 12.3 Roteiro por IA
- roteiro baseado em notícia;
- teleprompter;
- cenas;
- duração;
- revisão humana.

## 12.4 Shorts e Reels
- recortes;
- legendas;
- proporções;
- fila de renderização.

## 12.5 Consolidação TV Web
- painel;
- player;
- programação;
- vídeos;
- integrações.

---

# FASE 13 — MONETIZAÇÃO E COMERCIAL

## 13.1 Gestão de Anunciantes
- empresas;
- contatos;
- contratos;
- documentos;
- histórico.

## 13.2 Campanhas
- banners;
- posições;
- período;
- segmentação;
- limite de impressões;
- cliques.

## 13.3 Mídia Kit
- formatos;
- preços;
- propostas;
- PDF;
- aprovação.

## 13.4 Financeiro Comercial
- cobranças;
- vencimentos;
- pagamentos;
- renovações;
- inadimplência.

## 13.5 Conteúdo Patrocinado
- identificação obrigatória;
- aprovação;
- período;
- anunciante;
- relatórios.

---

# FASE 14 — ASSINATURAS E CLUBE

## 14.1 Planos
- gratuito;
- premium;
- VIP;
- empresarial.

## 14.2 Paywall
- artigos exclusivos;
- limite mensal;
- prévia;
- regras por categoria.

## 14.3 Assinantes
- cadastro;
- assinatura;
- status;
- histórico;
- cancelamento.

## 14.4 Benefícios
- conteúdo;
- eventos;
- cupons;
- newsletter;
- podcasts.

## 14.5 Pagamentos
- gateway;
- webhook idempotente;
- reembolso;
- cobrança recorrente;
- segurança PCI por terceirização.

---

# FASE 15 — ANALYTICS PRÓPRIO

## 15.1 Coleta
- página;
- sessão;
- origem;
- campanha;
- dispositivo;
- tempo de leitura.

## 15.2 Privacidade
- consentimento;
- anonimização;
- retenção;
- LGPD;
- opt-out.

## 15.3 Dashboard
- visitantes;
- páginas;
- notícias;
- autores;
- origem;
- conversões.

## 15.4 Analytics Editorial
- leitura;
- abandono;
- compartilhamento;
- desempenho por categoria;
- desempenho por horário.

---

# FASE 16 — API PÚBLICA E INTEGRAÇÕES

## 16.1 API Versionada
```text
/api/v1/news
/api/v1/categories
/api/v1/videos
/api/v1/podcasts
/api/v1/events
```

## 16.2 Autenticação
- tokens;
- escopos;
- rate limit;
- revogação.

## 16.3 Webhooks
- notícia publicada;
- notícia atualizada;
- evento publicado;
- podcast publicado.

## 16.4 Documentação
- OpenAPI;
- exemplos;
- erros;
- paginação.

---

# FASE 17 — MULTISITE

## 17.1 Estrutura de Sites
- site;
- domínio;
- logo;
- tema;
- cidade;
- configurações.

## 17.2 Isolamento
- dados por site;
- usuários por site;
- permissões;
- mídia;
- anúncios.

## 17.3 Conteúdo Compartilhado
- distribuir notícia;
- copiar;
- referenciar;
- atribuir origem;
- impedir duplicação acidental.

## 17.4 Administração Global
- painel central;
- sites;
- usuários;
- custos;
- saúde;
- auditoria.

---

# FASE 18 — APLICATIVOS

## 18.1 API Mobile
- autenticação;
- feed;
- busca;
- favoritos;
- notificações;
- perfil.

## 18.2 Android
- app;
- push;
- deep links;
- offline.

## 18.3 iOS
- app;
- push;
- deep links;
- offline.

## 18.4 Desktop/PWA
- instalação;
- cache;
- atualização;
- notificações.

---

# FASE 19 — JORNAL IMPRESSO E PDF

## 19.1 Edições
- título;
- data;
- seções;
- notícias selecionadas;
- ordem.

## 19.2 Templates
- capa;
- páginas internas;
- anúncios;
- créditos.

## 19.3 Geração de PDF
- A4;
- tabloide;
- revista;
- sangria;
- imagens em alta resolução.

## 19.4 Revisão
- prévia;
- paginação;
- alertas de texto excedente;
- aprovação final.

---

# FASE 20 — HARDENING E PRODUÇÃO

## 20.1 Segurança
- revisão de permissões;
- CSRF;
- XSS;
- SSRF;
- upload;
- rate limit;
- auditoria;
- secrets.

## 20.2 Filas
- Redis ou driver compatível;
- retries;
- dead-letter;
- monitoramento.

## 20.3 Cache
- páginas;
- consultas;
- API;
- invalidação.

## 20.4 Backups
- banco;
- storage;
- retenção;
- restauração testada.

## 20.5 Observabilidade
- logs estruturados;
- métricas;
- alertas;
- health checks.

## 20.6 Deploy
- produção;
- staging;
- CI/CD;
- migrations seguras;
- rollback.

## 20.7 Testes
- Unit;
- Feature;
- integração;
- autorização;
- carga;
- smoke test.

---

# ORDEM RECOMENDADA PARA O CODEX

Execute nesta ordem:

```text
1.  Fase 10.2.1 — Dashboard Editorial IA
2.  Fase 10.2.2 — Biblioteca de Prompts
3.  Fase 10.2.3 — Memória Editorial Avançada
4.  Fase 10.2.4 — Gerenciador de Provedores
5.  Fase 10.2.5 — Custos, Logs e Auditoria
6.  Fase 10.2.6 — Consolidação
7.  Fase 10.3.1 — Pautas e Kanban
8.  Fase 10.3.2 — Agentes
9.  Fase 10.3.3 — Pesquisa e Fontes
10. Fase 10.3.4 — Verificação
11. Fase 10.3.5 — Aprovação
12. Fase 10.3.6 — Calendário
13. Fase 10.3.7 — Consolidação
14. Fase 10.4 — Agência Assistida
15. Fases 11 a 20
```

Não começar Rádio, TV, aplicativos ou multisite antes de consolidar 10.2, 10.3 e 10.4.

---

# PROMPT BASE PARA COLAR NO WORK / CODEX

```text
Você está trabalhando no projeto Laravel LuziCity localizado em:

D:\Skill\LuziCity

Antes de alterar qualquer arquivo:

1. Examine a estrutura real do projeto.
2. Leia as migrations, models, controllers, routes e views existentes.
3. Verifique as Fases 10.0 e 10.1 já instaladas.
4. Não suponha nomes de tabelas, colunas, rotas, layouts ou classes.
5. Não duplique recursos existentes.
6. Preserve SQLite.
7. Não altere .env.
8. Não apague banco ou dados.
9. Não publique conteúdo de IA automaticamente.
10. Exija revisão humana.
11. Faça backup dos arquivos alterados.
12. Crie migrations defensivas usando Schema::hasTable e Schema::hasColumn quando necessário.
13. Crie testes.
14. Valide PHP com php -l.
15. Execute optimize:clear, migrate, route:list, view:cache e testes.
16. Entregue tudo em patch ZIP instalável por BAT.

Tarefa atual:
[COLE AQUI A FASE OU SUBFASE DESTE DOCUMENTO]

Ao terminar, informe:
- arquivos criados;
- arquivos alterados;
- migrations;
- rotas;
- testes;
- comandos executados;
- riscos ou pendências;
- caminho do ZIP gerado.
```

---

# CHECKLIST DE ENTREGA DE CADA SUBFASE

```text
[ ] Estrutura real examinada
[ ] Backup criado
[ ] Migration defensiva
[ ] Model
[ ] Controller
[ ] Service
[ ] Form Requests, quando aplicável
[ ] Policies/middlewares
[ ] Rotas sem duplicação
[ ] Views responsivas
[ ] Menu atualizado
[ ] Logs seguros
[ ] Chaves protegidas
[ ] Testes Unit/Feature
[ ] php -l
[ ] artisan optimize:clear
[ ] artisan migrate --force
[ ] artisan route:list
[ ] artisan view:cache
[ ] artisan test
[ ] npm run build, quando houver frontend
[ ] Instalador BAT
[ ] Backup/rollback
[ ] README
[ ] CHANGELOG
[ ] ZIP final
```

---

# ITENS QUE NÃO DEVEM SER FEITOS AGORA

- Não reconstruir o CMS do zero.
- Não trocar Laravel.
- Não trocar SQLite antes de existir necessidade real de produção.
- Não instalar um framework frontend novo apenas para um painel.
- Não criar publicação autônoma sem aprovação.
- Não armazenar chaves de API em texto puro.
- Não copiar integralmente notícias de terceiros.
- Não criar dezenas de migrations conflitantes de uma só vez.
- Não alterar todos os módulos em um único patch.
- Não começar multisite antes da estabilização da redação e da IA.

---

# PRIMEIRA TAREFA PARA O WORK / CODEX

Começar por:

```text
FASE 10.2.1 — Dashboard Editorial IA
```

Entregar como:

```text
LuziCity_Fase10_2_1_Dashboard_Editorial_IA.zip
```

O patch deve ser independente, instalável, reversível e compatível com as Fases 10.0 e 10.1 já instaladas.
