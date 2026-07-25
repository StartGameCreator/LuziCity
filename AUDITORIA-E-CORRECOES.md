# LuziCityLaravel13 — correções de segurança

Correções aplicadas nesta versão:

- proteção centralizada das rotas administrativas por perfis;
- rota de IA movida para área autenticada e limitada por frequência;
- autorização por contexto na geração de texto por IA;
- limitação de tentativas de login e cadastro;
- bloqueio de login tradicional e social para contas desativadas;
- limitação de frequência em pedidos de rádio, testes de IA e importações RSS;
- remoção do `.env` e dos logs do pacote distribuível;
- regras adicionais no `.gitignore` para arquivos operacionais.

Após substituir os arquivos, execute:

```powershell
php artisan optimize:clear
php artisan route:list
php artisan test
```
