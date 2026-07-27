# Segurança

## Controles observados

Middlewares:

- `AuditAdminMutation.php`
- `AuthenticateApiToken.php`
- `CachePublicResponse.php`
- `EnsureApiTokenAbility.php`
- `EnsureUserHasAnyRole.php`
- `RateLimitAdminMutations.php`
- `RecordRequestTelemetry.php`
- `ResolveCurrentSite.php`
- `SecurityHeaders.php`
- `ValidateUploadedFiles.php`

O bootstrap aplica resolução de site, validação de uploads, rate limit de mutações administrativas, auditoria, cabeçalhos de segurança e telemetria. A API possui autenticação por token e verificação de abilities.

## Autenticação e autorização

- Sessão Laravel para área web.
- Login social via Socialite.
- Papéis e permissões via Spatie.
- Tokens para API pública/protegida.
- CSRF habilitado, com exceção explícita para webhook de pagamento.

## Segredos

- `.env` nunca deve ser versionado.
- Chaves de IA, social login, Firebase, Mercado Pago e AzuraCast devem ser rotacionáveis.
- Secrets persistidos devem usar criptografia e casts adequados.
- Logs e auditorias não podem conter tokens completos.

## Uploads e embeds

O projeto possui validação de uploads, sanitização de embeds e proteção de URLs públicas. Preserve allowlists de MIME, tamanho, protocolo e host.

## Multisite

Toda consulta de dados isolados deve respeitar o site atual. Testes de isolamento devem permanecer obrigatórios.

## Publicação segura no GitHub

Antes do push:

```powershell
git ls-files .env
git grep -n -I -E "(API_KEY|SECRET|TOKEN|PASSWORD)="
```

Revise manualmente cada resultado e use ferramentas de secret scanning do GitHub.
