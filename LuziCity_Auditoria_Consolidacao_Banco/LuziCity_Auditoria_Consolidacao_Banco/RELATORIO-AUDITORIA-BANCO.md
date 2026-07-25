# Auditoria do banco — LuziCity

## Base examinada

- 77 tabelas no SQLite enviado.
- 53 migrations registradas como executadas.
- Estrutura já contempla IA Editorial, redação, RSS assistido, rádio, podcasts, áudio, TV e vídeo.

## Diagnóstico principal

O banco não estava “parado” nas fases antigas: as migrations mais recentes foram registradas. O problema real é de **consolidação estrutural** após crescimento acelerado:

1. Muitos módulos foram adicionados em sequência, sem um registro central da versão estrutural.
2. A mídia permanece espalhada em campos `*_path`, sem catálogo central reutilizável.
3. Existe auditoria específica de IA, mas não uma auditoria geral do sistema.
4. Consultas operacionais futuras precisam de índices compostos adicionais.
5. SQLite depende de `foreign_keys` ativo em cada conexão; o projeto já usa `DB_FOREIGN_KEYS=true` por padrão, mas isso deve ser verificado no ambiente real.
6. Algumas tabelas de apoio possuem FKs, mas poucos índices explícitos nos campos de navegação e ordenação.

## Ajustes aplicados pelo patch

### Novas tabelas

- `database_schema_registry`: registra módulos e versões estruturais.
- `media_assets`: catálogo central de imagens, áudios, vídeos e documentos.
- `mediables`: associação polimórfica de mídia com qualquer conteúdo.
- `system_audit_logs`: trilha geral de auditoria, separada da auditoria de IA.

### Índices adicionados

Índices compostos para notícias, pautas, execuções de IA, RSS, podcasts, rádio, TV, vídeos, narrações e push.

### Ferramenta de auditoria

Novo comando:

```bat
php artisan luzicity:database-audit
```

Saída JSON:

```bat
php artisan luzicity:database-audit --json
```

A ferramenta verifica:

- tabelas essenciais;
- quantidade de migrations;
- estado das foreign keys no SQLite;
- registros órfãos em relacionamentos críticos;
- slugs duplicados;
- estado geral do banco.

## Decisões de segurança

- O patch não apaga tabelas existentes.
- O patch não altera `.env`.
- O patch não remove dados.
- O patch não converte automaticamente os caminhos antigos para `media_assets`; essa migração deve ser feita em etapa posterior, módulo por módulo.
- O `down()` remove apenas as quatro tabelas novas. Índices adicionais são mantidos, pois são seguros e melhoram desempenho.

## Próxima etapa recomendada

Após instalar, executar:

```bat
cd /d D:\Skill\LuziCity
php artisan luzicity:database-audit
```

Se o resultado indicar `foreign_keys desativado`, confirmar no `.env`:

```env
DB_FOREIGN_KEYS=true
```

Depois reiniciar o Laravel.
