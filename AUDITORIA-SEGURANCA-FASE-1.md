# LuziCityLaravel13 — Auditoria de Segurança — Fase 1

## Correção aplicada: XSS persistente em códigos incorporados

Foi criado `App\Services\Security\EmbedCodeSanitizer` para permitir somente `iframe` HTTPS de provedores autorizados:

- YouTube / YouTube Privacy
- Facebook
- TikTok
- DLive

O sanitizador remove scripts, elementos HTML não autorizados, URLs `javascript:`, eventos como `onload`/`onclick` e atributos desconhecidos.

A proteção foi aplicada em duas camadas:

1. **Na gravação**, nos controllers de classificados, imóveis, rádio e banners.
2. **Na exibição**, nas views públicas, protegendo também registros antigos já existentes no banco.

## Arquivos principais

- `app/Services/Security/EmbedCodeSanitizer.php`
- `app/Http/Controllers/VehicleClassifiedController.php`
- `app/Http/Controllers/RealEstateController.php`
- `app/Http/Controllers/AdminRadioController.php`
- `app/Http/Controllers/AdminMediaBannerController.php`
- views públicas de home, rádio, veículos e imóveis
- `tests/Unit/Security/EmbedCodeSanitizerTest.php`

## Validação

- Sintaxe PHP dos arquivos alterados: aprovada.
- Foram adicionados quatro testes unitários de segurança.
- Neste ambiente de empacotamento, o PHPUnit não iniciou por ausência local das extensões DOM, mbstring e xmlwriter. No ambiente Windows informado pelo usuário, a suíte Laravel já executa normalmente.

## Comandos após aplicar

```powershell
php artisan optimize:clear
php artisan test
```
