# Deploy

Para a preparação integral de um servidor novo — sistema operacional, PHP,
Nginx, SQLite, Redis, Supervisor, Docker, AzuraCast, SSL e backups — consulte
`docs/operations/INSTALACAO-COMPLETA-SERVIDORES.md`.

## Ambientes

Use arquivos de ambiente derivados de `deploy/env.staging.example` e
`deploy/env.production.example`. Segredos nunca entram no Git. Staging deve usar
banco, storage, filas, cache, URLs e credenciais separados de produção.

## CI/CD

O workflow `.github/workflows/ci-cd.yml` executa migrações em banco limpo, Pint,
suíte completa e build. Push em `develop` entrega staging após aprovação do
environment. Produção exige execução manual e aprovação configurada no GitHub.

Configure os secrets `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_KEY` e `DEPLOY_PATH`
em cada environment.

## Deploy seguro

```bash
scripts/release.sh /var/www/luzicity production
```

O release é montado em um diretório novo e ativado por troca atômica do link
`current`. O script executa preflight, backup com restauração verificada, modo manutenção,
`migrate --force --isolated`, caches e restart dos workers. A opção `--isolated`
impede duas instâncias de migrarem simultaneamente.

Migrações de produção devem ser compatíveis com a release anterior: primeiro
adicionar campos/tabelas, depois publicar o código consumidor e somente em uma
release futura remover estruturas antigas.

## Releases e rollback

Mantenha releases em `/var/www/luzicity/releases/ID` e o link
`/var/www/luzicity/current`. Para voltar o código:

```bash
scripts/rollback.sh /var/www/luzicity ID_ANTERIOR
```

O rollback não executa `migrate:rollback` automaticamente, pois isso pode destruir
dados escritos pela nova release. Se uma migração precisar ser revertida, restaure
o backup em uma instância separada, valide-o e planeje uma migração corretiva.

Após deploy ou rollback, confirme `/up`, `/health/ready`, workers, scheduler,
logs estruturados e uma navegação de smoke test.
