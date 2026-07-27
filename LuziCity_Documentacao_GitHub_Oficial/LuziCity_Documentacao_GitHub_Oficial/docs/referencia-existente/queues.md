# Operação das filas LuziCity

O sistema funciona com `database` ou `redis`. Em produção, prefira Redis:

```env
QUEUE_CONNECTION=redis
REDIS_QUEUE_RETRY_AFTER=900
REDIS_QUEUE_BLOCK_FOR=5
QUEUE_FAILED_DRIVER=database-uuids
```

O `retry_after` deve permanecer acima do maior timeout de job (renderização de vídeo: 660 segundos).

Inicie workers separados para evitar que renderizações longas bloqueiem tarefas rápidas:

```bash
php artisan queue:work --queue=default,rss,webhooks,audio --tries=3 --timeout=300 --sleep=1
php artisan queue:work --queue=video-render --tries=3 --timeout=660 --sleep=2
```

Use Supervisor, systemd ou o gerenciador de processos da hospedagem para reiniciar workers. Após cada deploy:

```bash
php artisan queue:restart
```

O painel `/admin/sistema/filas` mostra pendências, atividade e dead-letter. A limpeza de registros com mais de 30 dias é agendada diariamente pelo scheduler.
