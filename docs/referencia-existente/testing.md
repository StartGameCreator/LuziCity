# Estratégia de testes

## Camadas

- **Unit:** regras puras, segurança, modelos e versionamento de cache.
- **Feature:** rotas, formulários, APIs e fluxos de cada módulo.
- **Integration:** banco, observers, cache, filas, storage e serviços externos simulados.
- **Authorization:** matriz de acesso anônimo, usuário editorial e administrador.
- **Smoke:** liveness, readiness e API pública após cada deploy.
- **Carga:** benchmark local controlado com orçamento de erro e latência p95.

## Comandos

```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --testsuite=Integration
php artisan test --testsuite=Authorization
php artisan test --testsuite=Smoke
php artisan luzicity:load-test --requests=100 --max-p95=500
php artisan luzicity:smoke --base-url=https://staging.luzicity.com.br
```

O teste de carga é bloqueado em produção sem `--force`. Testes destrutivos e de
integração devem usar banco e credenciais exclusivos de teste.

O CI executa todas as camadas, build, smoke local e uma carga curta. Depois do
deploy, repita o smoke contra a URL real antes de promover staging para produção.
