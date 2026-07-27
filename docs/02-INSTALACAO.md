# Instalação local

## Requisitos

- Windows 10/11 ou Linux.
- PHP 8.3 ou superior, com extensões exigidas pelo Laravel e SQLite.
- Composer 2.
- Node.js e npm.
- Git.
- SQLite para o perfil atual.
- FFmpeg/FFprobe para funções de áudio e vídeo que dependam deles.
- Docker Desktop e WSL2 apenas para serviços externos como AzuraCast.

## Instalação limpa

```powershell
git clone <URL-DO-REPOSITORIO> LuziCity
cd LuziCity
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

Crie o banco SQLite:

```powershell
New-Item -ItemType File -Force database\database.sqlite
```

No `.env`:

```dotenv
DB_CONNECTION=sqlite
```

Execute:

```powershell
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=9001
```

## Desenvolvimento com Vite

```powershell
npm run dev
```

## Filas e scheduler

```powershell
php artisan queue:work
php artisan schedule:work
```

## Validação

```powershell
php artisan route:list
php artisan test
npm run build
```

## Observação sobre credenciais iniciais

Nunca publique usuário ou senha administrativos no README. Use seeders específicos de ambiente ou variáveis temporárias e altere a senha no primeiro acesso.
