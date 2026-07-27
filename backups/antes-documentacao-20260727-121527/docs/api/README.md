# API pública LuziCity v1

Base local: `https://seu-dominio.example/api/v1`.

## Conteúdo público

```bash
curl -H "Accept: application/json" \
  "https://seu-dominio.example/api/v1/news?per_page=20&page=1"
```

Os endpoints `news`, `categories`, `videos`, `podcasts` e `events` são públicos e limitados a 120 requisições por minuto por IP.

## Token e escopos

Emita um token com credenciais de uma conta ativa:

```bash
curl -X POST "https://seu-dominio.example/api/v1/auth/tokens" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"usuario@example.com","password":"senha","name":"Meu aplicativo","abilities":["profile:read"],"expires_in_days":30}'
```

O campo `token` só é devolvido nessa resposta. Envie-o como `Authorization: Bearer <token>`.

```bash
curl "https://seu-dominio.example/api/v1/auth/me" \
  -H "Accept: application/json" -H "Authorization: Bearer lzc_TOKEN"

curl -X DELETE "https://seu-dominio.example/api/v1/auth/tokens/current" \
  -H "Accept: application/json" -H "Authorization: Bearer lzc_TOKEN"
```

Escopos disponíveis:

- `content:read`: reservado para integrações de conteúdo.
- `profile:read`: permite consultar `/auth/me`.

## Paginação

Use `page` e `per_page`; `per_page` aceita de 1 a 100. Coleções retornam:

```json
{
  "data": [],
  "links": {"first": "...", "last": "...", "prev": null, "next": null},
  "meta": {"current_page": 1, "from": null, "last_page": 1, "per_page": 20, "to": null, "total": 0}
}
```

## Erros

- `401`: token ausente, inválido, expirado ou revogado.
- `403`: token válido sem o escopo exigido.
- `404`: rota ou recurso inexistente.
- `422`: credenciais inválidas ou campos inválidos; `errors` detalha os campos.
- `429`: limite de requisições excedido; consulte `Retry-After`.
- `500`: erro interno inesperado.

Exemplo de validação:

```json
{
  "message": "The abilities field is required.",
  "errors": {"abilities": ["The abilities field is required."]}
}
```

O contrato completo está em `/api/v1/docs/openapi.yaml`.

## API Mobile

Tokens do aplicativo usam `mobile:read` para consultas e `mobile:write` para alterações.

- `GET /mobile/feed`
- `GET /mobile/search?q=termo`
- `GET /mobile/favorites`
- `POST|DELETE /mobile/favorites/{slug}`
- `POST|DELETE /mobile/notifications/devices`
- `GET|PATCH /mobile/profile`

O feed, a busca, os favoritos e os dispositivos de notificação são isolados pelo domínio/site atual.
