LUZICITY — ALTERAR MASTER PARA SUPER ADMIN

1. Extraia os dois arquivos na raiz do projeto:
   D:\Skill\LuziCity

2. Confirme que estejam ao lado do arquivo artisan:
   - ALTERAR-MASTER-PARA-SUPER-ADMIN.bat
   - ALTERAR-MASTER-PARA-SUPER-ADMIN.php

3. Execute:
   ALTERAR-MASTER-PARA-SUPER-ADMIN.bat

O patch:
- cria backup do SQLite;
- renomeia Master para Super Admin;
- preserva vínculos e permissões;
- une os papéis caso os dois já existam;
- atribui Super Admin ao usuário elvis@luzicity.com.br;
- limpa caches do Laravel e do Spatie.

Depois acesse:
http://127.0.0.1:9001/admin
