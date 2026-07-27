# Backups e restauracao

## Politica

O agendador cria diariamente, às 02:30, um ZIP contendo o banco, o storage e um
manifesto SHA-256. Cada backup é verificado imediatamente e arquivos com mais de
30 dias são removidos às 05:00.

Para produção, configure `BACKUP_DISK=s3` (ou outro disco remoto). Manter o backup
somente no mesmo servidor não protege contra perda da máquina.

## Operacao

```bash
php artisan luzicity:backup --verify
php artisan luzicity:backup-verify backups/luzicity-AAAA-MM-DD_HH-MM-SS-id.zip
php artisan luzicity:backup-prune --days=30
```

Em SQLite, a verificação abre uma cópia temporária restaurada e executa
`PRAGMA integrity_check`. Em MySQL e PostgreSQL, o comando valida o ZIP, todos os
checksums e o dump não vazio. Os utilitários `mysqldump` ou `pg_dump` precisam
estar disponíveis no servidor.

## Restauracao de produção

1. Coloque a aplicação em manutenção e interrompa os workers.
2. Baixe o ZIP e execute `luzicity:backup-verify`.
3. Extraia o dump e a pasta `storage/` em uma área temporária.
4. Importe o banco em uma instância vazia e valide a aplicação.
5. Troque banco/storage somente após a validação e reinicie os workers.

Nunca restaure diretamente sobre a base ativa sem uma cópia de segurança recente.
