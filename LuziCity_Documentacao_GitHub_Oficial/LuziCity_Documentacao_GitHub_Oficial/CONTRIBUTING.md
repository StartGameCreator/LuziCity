# Contribuindo com o LuziCity

## Fluxo

1. Crie uma issue ou descreva claramente o problema.
2. Crie uma branch a partir do checkpoint estável.
3. Faça alterações pequenas e coesas.
4. Inclua migration defensiva quando houver banco.
5. Inclua testes.
6. Atualize a documentação.
7. Execute lint, testes e build.
8. Abra pull request sem segredos ou artefatos locais.

## Convenções

- Controllers finos; lógica complexa em services.
- Validação em Form Requests quando aplicável.
- Policies/middlewares para autorização.
- Jobs para operações demoradas.
- Não duplicar rotas, imports, menus ou campos.
- Preservar SQLite até decisão arquitetural formal.
- Não permitir autopublicação de IA.
- Não registrar dados sensíveis.

## Validação mínima

```powershell
php artisan optimize:clear
php artisan migrate
php artisan route:list
php artisan test
npm run build
```

## Commits

Use mensagens objetivas, por exemplo:

```text
feat(radio): integrar status da estação AzuraCast
fix(tv): impedir encoder duplicado
chore(docs): atualizar instalação de produção
```
