# Observabilidade

## Logs estruturados

Em produção, configure `LOG_CHANNEL=stack` e `LOG_STACK=daily,json`. Cada requisição
gera um evento `http.request` com request ID, rota, status, duração, memória, usuário
e exceção. O mesmo `X-Request-ID` é devolvido na resposta para correlação.

## Métricas e alertas

O middleware registra volume, erros, latência e consumo de memória. O painel
**Saúde do Sistema** mostra a última hora e alerta para taxa de erro elevada,
requisições lentas e jobs no dead-letter. A retenção padrão é 30 dias.

```bash
php artisan luzicity:metrics-prune --days=30
```

## Health checks

- `/up`: liveness do processo Laravel.
- `/health/ready`: prontidão de banco, cache, filas e storage.
- `/admin/saude-do-sistema`: diagnóstico detalhado para administradores.

Configure o balanceador para consultar `/up` frequentemente e `/health/ready`
antes de enviar tráfego a uma nova instância.
