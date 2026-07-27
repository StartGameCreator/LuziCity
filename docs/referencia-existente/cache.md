# Cache da LuziCity

## Produção

Use Redis para compartilhar o cache entre todas as instâncias:

```dotenv
CACHE_STORE=redis
REDIS_CACHE_CONNECTION=cache
REDIS_CACHE_LOCK_CONNECTION=default
CACHE_PREFIX=luzicity_prod_cache_
LUZICITY_HOME_CACHE_TTL=5
LUZICITY_PUBLIC_CACHE_TTL=60
```

O driver `database` continua compatível para instalações de instância única.

## Camadas

- A home mantém consultas de notícias, categorias, banners e RSS em cache.
- A API pública mantém cada recurso, página e conjunto de parâmetros em uma chave própria.
- Respostas públicas da API recebem cache HTTP compartilhável, ETag e `stale-while-revalidate`.
- O HTML da home recebe cache HTTP privado e ETag, evitando compartilhar tokens CSRF.

## Invalidação

Alterações em notícias, categorias, banners, RSS, configurações, vídeos, podcasts ou eventos
incrementam versões de cache. As chaves antigas expiram naturalmente, sem depender de
operações globais como `cache:clear`.

Após alterações de configuração no deploy:

```bash
php artisan config:cache
```
