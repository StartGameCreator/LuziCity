# Operação e deploy

## Serviços de runtime

- servidor web/PHP;
- worker de filas;
- scheduler;
- banco SQLite atual;
- FFmpeg para mídia;
- AzuraCast em Docker quando habilitado;
- serviços externos de IA, push, pagamentos e login social.

## Comandos úteis

```powershell
php artisan optimize:clear
php artisan migrate --force
php artisan route:list
php artisan view:cache
php artisan test
npm run build
```

## Comandos operacionais disponíveis

O projeto contém comandos para backup, verificação de backup, limpeza por retenção, deploy check, smoke test, load test, auditoria de banco e limpeza de analytics expirado.

## Health checks

- `/up`: health básico do Laravel.
- `/health/ready`: readiness com throttle.
- painel administrativo de saúde do sistema.
- observabilidade e métricas de requisição configuráveis.

## Backup

Faça backup de:

- banco;
- `storage/app/public` e mídia privada necessária;
- `.env` em cofre seguro;
- configurações externas;
- volumes do AzuraCast separadamente.

Teste a restauração. Backup não validado não deve ser considerado confiável.

## Produção

- `APP_ENV=production`
- `APP_DEBUG=false`
- HTTPS obrigatório
- workers supervisionados
- scheduler via cron ou serviço
- logs com retenção
- backups automáticos
- permissões mínimas de filesystem
- segredos fora do repositório
