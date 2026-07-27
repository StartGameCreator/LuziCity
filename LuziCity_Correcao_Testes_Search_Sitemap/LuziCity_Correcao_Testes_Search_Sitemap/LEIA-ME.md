# LuziCity — Correção dos testes Search e Sitemap

## Diagnóstico

Os testes:

- `Tests\Feature\Search\UnifiedSearchTest`
- `Tests\Feature\Seo\SitemapTest`

executam consultas na tabela `news_articles` usando SQLite em memória (`:memory:`), mas essas classes não inicializam as migrations antes das requisições.

O patch adiciona o trait Laravel:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
```

e, dentro de cada classe:

```php
use RefreshDatabase;
```

Nenhum arquivo funcional da aplicação, migration, banco real ou `.env` é alterado.

## Instalação

Execute:

```bat
INSTALAR-CORRECAO-TESTES.bat
```

Raiz padrão:

```text
D:\Skill\LuziCity
```

## Validação

O instalador executa:

```powershell
php artisan optimize:clear
php artisan test --filter=UnifiedSearchTest
php artisan test --filter=SitemapTest
php artisan test
```

## Backup

Os dois testes originais são copiados para:

```text
D:\Skill\LuziCity\backups\antes-correcao-testes-search-sitemap-AAAAMMDD-HHMMSS
```
