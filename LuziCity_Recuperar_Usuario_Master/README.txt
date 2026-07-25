# LuziCity — Recuperação do usuário Master

## O que este patch faz

- cria um backup do `database/database.sqlite`, quando o arquivo existir;
- limpa os caches do Laravel;
- cria ou atualiza o papel `Master` no Spatie Permission;
- vincula ao papel `Master` todas as permissões já existentes no banco;
- remove o usuário antigo `elvis@luzicity.com.br`;
- recria o usuário:
  - nome: `Elvis`
  - e-mail: `elvis@luzicity.com.br`
  - senha: `Start@Game357`
  - ativo: sim
  - e-mail verificado: sim
- atribui o papel `Master`;
- limpa os caches novamente.

## Instalação

1. Extraia os dois arquivos na raiz:

   `D:\Skill\LuziCity`

2. Confirme que ficaram ao lado do arquivo `artisan`:

   - `RECUPERAR-USUARIO-MASTER.bat`
   - `RECUPERAR-USUARIO-MASTER.php`

3. Execute:

   `RECUPERAR-USUARIO-MASTER.bat`

## Credenciais

- E-mail: `elvis@luzicity.com.br`
- Senha: `Start@Game357`

## Segurança

Depois de recuperar o acesso, altere a senha dentro do sistema.
