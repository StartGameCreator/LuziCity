# Instalação completa do LuziCity em servidores

Versão do documento: 1.0  
Ambiente-alvo: Ubuntu Server 24.04 LTS  
Aplicação: Laravel 13, PHP 8.3+, SQLite, Redis, Nginx e Supervisor  
Rádio: AzuraCast stable em Docker

## 1. Objetivo

Este documento descreve a implantação completa do LuziCity em produção,
incluindo:

- aplicação Laravel em `https://luzicity.com.br`;
- painel administrativo nativo em `https://luzicity.com.br/admin`;
- administração nativa da rádio em `https://luzicity.com.br/admin/radio`;
- player público em `https://luzicity.com.br/radio`;
- motor privado AzuraCast em `https://azuracast.luzicity.com.br`;
- filas, scheduler, Redis, FFmpeg, backups, SSL, deploy e rollback.

O AzuraCast é um serviço independente. O navegador acessa o LuziCity e o
Laravel acessa a API do AzuraCast. A API Key nunca deve ser colocada em
JavaScript, HTML, repositório Git ou logs.

## 2. Arquitetura

```text
Internet
   |
   +-- https://luzicity.com.br
   |       |
   |       +-- Nginx
   |       +-- PHP-FPM / Laravel
   |       +-- SQLite
   |       +-- Redis
   |       +-- Workers / Scheduler / FFmpeg
   |
   +-- https://azuracast.luzicity.com.br
           |
           +-- Nginx reverse proxy
           +-- AzuraCast Docker :8080
           +-- MariaDB/Redis/Liquidsoap internos
           +-- Icecast / portas de emissoras
```

Pode-se usar um único servidor no início. Em escala maior, o Laravel e o
AzuraCast podem ser movidos para máquinas diferentes sem alterar as rotas
públicas do LuziCity; basta atualizar `AZURACAST_BASE_URL`.

## 3. Requisitos de infraestrutura

### 3.1 Servidor único recomendado

- Ubuntu Server 24.04 LTS x86_64;
- 4 vCPU;
- 8 GB de RAM no mínimo;
- 16 GB de RAM quando houver renderização frequente de vídeo;
- 100 GB SSD no mínimo;
- armazenamento adicional para biblioteca de áudio e vídeo;
- IP público fixo;
- acesso root ou usuário com `sudo`;
- backup externo S3 compatível.

### 3.2 DNS

Crie registros `A` ou `AAAA`:

```text
luzicity.com.br             -> IP_DO_SERVIDOR
www.luzicity.com.br         -> IP_DO_SERVIDOR
azuracast.luzicity.com.br   -> IP_DO_SERVIDOR
```

Espere a propagação antes de emitir certificados TLS.

### 3.3 Portas

Libere no firewall:

| Porta | Uso | Exposição |
|---|---|---|
| 22/TCP | SSH | somente IPs administrativos |
| 80/TCP | HTTP/Certbot | pública |
| 443/TCP | HTTPS | pública |
| 2022/TCP | SFTP AzuraCast | somente equipe, se necessário |
| 9100–9199/TCP | streams/DJs AzuraCast | conforme necessidade |

Não exponha diretamente as portas `8080`, `8443`, `3306` ou `6379`.

Exemplo com UFW:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from IP_ADMINISTRATIVO to any port 22 proto tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 9100:9199/tcp
sudo ufw enable
sudo ufw status
```

## 4. Preparação do sistema

```bash
sudo apt update
sudo apt full-upgrade -y
sudo timedatectl set-timezone America/Sao_Paulo
sudo apt install -y \
  nginx git curl unzip acl supervisor redis-server ffmpeg certbot \
  python3-certbot-nginx sqlite3 \
  php8.3-fpm php8.3-cli php8.3-common php8.3-curl php8.3-gd \
  php8.3-intl php8.3-mbstring php8.3-sqlite3 php8.3-xml php8.3-zip \
  php8.3-bcmath php8.3-redis
```

Confirme:

```bash
php -v
php -m | grep -E 'curl|gd|intl|mbstring|pdo_sqlite|redis|xml|zip'
ffmpeg -version
redis-cli ping
nginx -v
```

O resultado do Redis deve ser `PONG`.

### 4.1 Composer

```bash
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
ACTUAL_CHECKSUM="$(php -r 'echo hash_file("sha384", "composer-setup.php");')"
test "$EXPECTED_CHECKSUM" = "$ACTUAL_CHECKSUM"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
composer --version
```

### 4.2 Node.js

Use Node.js 20 LTS ou superior compatível:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node --version
npm --version
```

### 4.3 Docker

Instale o Docker pelo repositório oficial:

```bash
curl -fsSL https://get.docker.com -o /tmp/get-docker.sh
sudo sh /tmp/get-docker.sh
sudo usermod -aG docker "$USER"
rm /tmp/get-docker.sh
```

Saia da sessão SSH e entre novamente. Depois valide:

```bash
docker version
docker compose version
docker run --rm hello-world
```

## 5. Usuário e diretórios da aplicação

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
sudo mkdir -p /var/www/luzicity/{releases,shared/storage,shared/database}
sudo chown -R deploy:www-data /var/www/luzicity
sudo chmod 2775 /var/www/luzicity/shared
sudo chmod 2775 /var/www/luzicity/shared/storage
sudo chmod 2770 /var/www/luzicity/shared/database
```

Crie o banco SQLite compartilhado:

```bash
sudo -u deploy touch /var/www/luzicity/shared/database/database.sqlite
sudo chown deploy:www-data /var/www/luzicity/shared/database/database.sqlite
sudo chmod 660 /var/www/luzicity/shared/database/database.sqlite
```

O banco deve ficar em `shared/database`, nunca dentro de uma release. Assim,
trocas e rollbacks de código não substituem os dados.

## 6. Primeira instalação do código

Como usuário `deploy`:

```bash
sudo -iu deploy
git clone URL_DO_REPOSITORIO /var/www/luzicity/releases/primeira
rm -rf /var/www/luzicity/releases/primeira/storage
ln -s /var/www/luzicity/shared/storage /var/www/luzicity/releases/primeira/storage
ln -s /var/www/luzicity/releases/primeira /var/www/luzicity/current
cp /var/www/luzicity/current/.env.example /var/www/luzicity/shared/.env
ln -s /var/www/luzicity/shared/.env /var/www/luzicity/current/.env
cd /var/www/luzicity/current
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
exit
```

## 7. Configuração `.env`

Edite `/var/www/luzicity/shared/.env`. Use permissões restritas:

```bash
sudo chown deploy:www-data /var/www/luzicity/shared/.env
sudo chmod 640 /var/www/luzicity/shared/.env
sudo -u deploy nano /var/www/luzicity/shared/.env
```

Configuração mínima:

```dotenv
APP_NAME=LuziCity
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://luzicity.com.br
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

LOG_CHANNEL=stack
LOG_STACK=daily,json
LOG_LEVEL=info

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/luzicity/shared/database/database.sqlite

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_DOMAIN=.luzicity.com.br
SESSION_SECURE_COOKIE=true

CACHE_STORE=redis
CACHE_PREFIX=luzicity_prod_
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_QUEUE_RETRY_AFTER=900
REDIS_QUEUE_BLOCK_FOR=5

FILESYSTEM_DISK=local
BACKUP_DISK=s3
BACKUP_PATH=backups
BACKUP_RETENTION_DAYS=30
BACKUP_INCLUDE_STORAGE=true

MAIL_MAILER=smtp
MAIL_HOST=SERVIDOR_SMTP
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contato@luzicity.com.br
MAIL_FROM_NAME=LuziCity

AZURACAST_ENABLED=true
AZURACAST_BASE_URL=https://azuracast.luzicity.com.br
AZURACAST_API_KEY=
AZURACAST_STATION_ID=
AZURACAST_STATION_SHORTCODE=luzicity
AZURACAST_TIMEOUT=10
AZURACAST_VERIFY_SSL=true
AZURACAST_CACHE_SECONDS=10
```

Copie também as variáveis necessárias de:

- publicidade e pixels;
- provedores sociais;
- Firebase;
- Mercado Pago;
- S3;
- provedores de IA;
- aplicativos Android/iOS.

Nunca reutilize credenciais de staging em produção.

Gere a chave:

```bash
cd /var/www/luzicity/current
php artisan key:generate
```

Não altere `APP_KEY` depois que o sistema começar a armazenar dados
criptografados.

## 8. Inicialização do Laravel

```bash
cd /var/www/luzicity/current
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan luzicity:create-admin admin@luzicity.com.br
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Permissões:

```bash
sudo chown -R deploy:www-data /var/www/luzicity/shared/storage
sudo find /var/www/luzicity/shared/storage -type d -exec chmod 2775 {} \;
sudo find /var/www/luzicity/shared/storage -type f -exec chmod 664 {} \;
sudo chown deploy:www-data /var/www/luzicity/shared/database/database.sqlite
sudo chmod 660 /var/www/luzicity/shared/database/database.sqlite
```

## 9. PHP-FPM

Crie `/etc/php/8.3/fpm/pool.d/luzicity.conf`:

```ini
[luzicity]
user = www-data
group = www-data
listen = /run/php/php8.3-fpm-luzicity.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500

php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 70M
php_admin_value[memory_limit] = 512M
php_admin_value[max_execution_time] = 120
php_admin_value[date.timezone] = America/Sao_Paulo
```

```bash
sudo php-fpm8.3 -t
sudo systemctl restart php8.3-fpm
sudo systemctl enable php8.3-fpm
```

## 10. Nginx do LuziCity

Crie `/etc/nginx/sites-available/luzicity`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name luzicity.com.br www.luzicity.com.br;

    root /var/www/luzicity/current/public;
    index index.php;
    client_max_body_size 64m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm-luzicity.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(?:css|js|jpg|jpeg|png|gif|svg|webp|ico|woff2?)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
        try_files $uri /index.php?$query_string;
    }

    access_log /var/log/nginx/luzicity-access.log;
    error_log /var/log/nginx/luzicity-error.log;
}
```

Ative:

```bash
sudo ln -s /etc/nginx/sites-available/luzicity /etc/nginx/sites-enabled/luzicity
sudo nginx -t
sudo systemctl reload nginx
```

## 11. Instalação do AzuraCast

Use `/var/azuracast`, separado do Laravel:

```bash
sudo mkdir -p /var/azuracast
sudo chown "$USER":"$USER" /var/azuracast
cd /var/azuracast
curl -fsSL https://raw.githubusercontent.com/AzuraCast/AzuraCast/main/docker.sh -o docker.sh
chmod a+x docker.sh
./docker.sh install
```

Durante o instalador:

- escolha o canal `Stable`;
- HTTP: `8080`;
- HTTPS interno: `8443`;
- SFTP: `2022`;
- portas automáticas: `9100` até `9199`;
- não reutilize portas do Nginx ou do PHP.

Ao concluir, abra temporariamente `http://IP_DO_SERVIDOR:8080/setup` somente se
a porta estiver restrita ao IP administrativo. Preferencialmente configure
primeiro o reverse proxy e use HTTPS.

### 11.1 Nginx do AzuraCast

Crie `/etc/nginx/sites-available/azuracast`:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    '' close;
}

server {
    listen 80;
    listen [::]:80;
    server_name azuracast.luzicity.com.br;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_read_timeout 3600;
        proxy_send_timeout 3600;
        proxy_buffering off;
    }

    access_log /var/log/nginx/azuracast-access.log;
    error_log /var/log/nginx/azuracast-error.log;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/azuracast /etc/nginx/sites-enabled/azuracast
sudo nginx -t
sudo systemctl reload nginx
```

### 11.2 SSL

```bash
sudo certbot --nginx \
  -d luzicity.com.br \
  -d www.luzicity.com.br \
  -d azuracast.luzicity.com.br
sudo certbot renew --dry-run
```

Depois do SSL, bloqueie acesso público direto à porta 8080.

### 11.3 Configuração inicial

Em `https://azuracast.luzicity.com.br`:

1. crie o Super Administrador;
2. configure a URL pública do AzuraCast;
3. crie a emissora;
4. use o shortcode `luzicity`;
5. configure AutoDJ, playlists e mounts;
6. gere uma API Key com permissões administrativas necessárias;
7. anote o ID numérico da emissora;
8. coloque ID, shortcode e chave apenas no `.env` do Laravel.

Depois:

```bash
cd /var/www/luzicity/current
php artisan optimize:clear
php artisan config:cache
```

Valide:

```bash
curl -I https://azuracast.luzicity.com.br
curl -s https://azuracast.luzicity.com.br/api/nowplaying/luzicity
curl -s https://luzicity.com.br/radio/estado
```

O endpoint do LuziCity não deve retornar a API Key.

## 12. Supervisor e filas

Crie `/etc/supervisor/conf.d/luzicity.conf`:

```ini
[program:luzicity-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/luzicity/current/artisan queue:work redis --queue=default,rss,webhooks,audio --sleep=1 --tries=3 --timeout=300
directory=/var/www/luzicity/current
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/luzicity/shared/storage/logs/worker-default.log
stopwaitsecs=360

[program:luzicity-video]
command=php /var/www/luzicity/current/artisan queue:work redis --queue=video-render --sleep=2 --tries=3 --timeout=660
directory=/var/www/luzicity/current
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/luzicity/shared/storage/logs/worker-video.log
stopwaitsecs=720
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

## 13. Scheduler

Como o usuário do servidor web:

```bash
sudo crontab -u www-data -e
```

Adicione:

```cron
* * * * * cd /var/www/luzicity/current && php artisan schedule:run >> /dev/null 2>&1
```

Valide:

```bash
cd /var/www/luzicity/current
php artisan schedule:list
```

O scheduler executa importações RSS, métricas, limpeza de filas, analytics e
backups.

## 14. Backups

### 14.1 LuziCity

```bash
cd /var/www/luzicity/current
php artisan luzicity:backup --verify
php artisan luzicity:backup-prune --days=30
```

Use armazenamento externo:

```dotenv
BACKUP_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=
```

Teste restauração em outra máquina pelo menos mensalmente.

### 14.2 AzuraCast

```bash
cd /var/azuracast
mkdir -p backups
./docker.sh backup "/var/azuracast/backups/azuracast-$(date +%Y%m%d-%H%M%S).zip"
```

Copie o backup para armazenamento externo. Nunca use `docker compose down -v`
nem `./docker.sh uninstall` em operação normal.

## 15. Deploy de novas versões

O repositório possui deploy por releases:

```bash
/var/www/luzicity/current/scripts/release.sh /var/www/luzicity production
```

Antes do primeiro uso, confirme:

- `/var/www/luzicity/current` é link simbólico;
- `/var/www/luzicity/shared/.env` existe;
- `shared/storage` e `shared/database` estão persistentes;
- `DB_DATABASE` usa caminho absoluto;
- usuário `deploy` pode escrever em `releases`;
- `www-data` pode escrever no banco e storage;
- Node e Composer estão disponíveis ao usuário `deploy`.

Após o deploy:

```bash
cd /var/www/luzicity/current
php artisan storage:link
php artisan queue:restart
sudo supervisorctl status
curl -fsS https://luzicity.com.br/up
curl -fsS https://luzicity.com.br/health/ready
php artisan luzicity:smoke --base-url=https://luzicity.com.br
```

## 16. Rollback

Liste releases:

```bash
ls -1 /var/www/luzicity/releases
```

Volte o código:

```bash
/var/www/luzicity/current/scripts/rollback.sh \
  /var/www/luzicity \
  ID_DA_RELEASE_ANTERIOR
```

O rollback de código não reverte migrations. Não execute
`php artisan migrate:rollback` automaticamente em produção. Restaure banco e
storage somente a partir de backup verificado e após ensaio em ambiente
separado.

## 17. Atualização do AzuraCast

```bash
cd /var/azuracast
./docker.sh backup "/var/azuracast/backups/pre-update-$(date +%Y%m%d-%H%M%S).zip"
./docker.sh update-self
./docker.sh update
docker compose ps
```

Confirme depois:

```bash
curl -fsS https://azuracast.luzicity.com.br/api/nowplaying/luzicity
curl -fsS https://luzicity.com.br/radio/estado
```

## 18. Segurança

- `APP_DEBUG=false` em produção;
- `.env` com modo `640`;
- API Key do AzuraCast somente no backend;
- SSH apenas com chave, sem senha e sem root remoto;
- Redis, SQLite, MariaDB e portas internas fora da Internet;
- HTTPS obrigatório;
- atualizações de segurança automáticas ou janela mensal;
- backups externos criptografados;
- contas administrativas com senhas únicas;
- rotação de API Keys após suspeita de vazamento;
- não registrar tokens, senhas ou stream keys em logs;
- limitar `/setup` e painéis administrativos durante a implantação;
- monitorar espaço em disco, especialmente mídia e Docker.

Instale atualizações automáticas:

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

## 19. Observabilidade

Endpoints:

- `https://luzicity.com.br/up`;
- `https://luzicity.com.br/health/ready`;
- `https://luzicity.com.br/admin/saude-do-sistema`;
- `https://luzicity.com.br/admin/radio`;
- `https://luzicity.com.br/radio/estado`;
- `https://azuracast.luzicity.com.br/api/nowplaying/luzicity`.

Logs:

```bash
tail -f /var/www/luzicity/shared/storage/logs/laravel.log
tail -f /var/log/nginx/luzicity-error.log
tail -f /var/log/nginx/azuracast-error.log
sudo journalctl -u php8.3-fpm -f
sudo journalctl -u redis-server -f
sudo supervisorctl tail -f luzicity-default:luzicity-default_00
cd /var/azuracast && docker compose logs -f --tail=100
```

## 20. Checklist de aceite

### Aplicação

- [ ] `APP_ENV=production` e `APP_DEBUG=false`;
- [ ] HTTPS válido;
- [ ] migrations concluídas;
- [ ] login do Super Admin funcionando;
- [ ] storage público acessível;
- [ ] `/up` retorna 200;
- [ ] `/health/ready` retorna sucesso;
- [ ] workers ativos;
- [ ] scheduler executando;
- [ ] backup remoto verificado.

### Rádio

- [ ] AzuraCast em canal stable;
- [ ] `azuracast.luzicity.com.br` com HTTPS;
- [ ] emissora e shortcode criados;
- [ ] stream tocando;
- [ ] `/radio/estado` retorna `source: azuracast`;
- [ ] `/admin/radio` mostra conectado;
- [ ] `/radio` exibe faixa e ouvintes;
- [ ] start/stop/restart autorizados;
- [ ] API Key ausente do HTML e JSON público;
- [ ] fallback testado com AzuraCast parado.

### Operação

- [ ] deploy por release testado;
- [ ] rollback de código testado;
- [ ] restauração do SQLite testada;
- [ ] restauração do AzuraCast testada;
- [ ] alertas de disco, memória e indisponibilidade configurados.

## 21. Comandos rápidos

```bash
# Estado geral
sudo systemctl status nginx php8.3-fpm redis-server
sudo supervisorctl status
cd /var/azuracast && docker compose ps

# Limpar/recriar caches Laravel
cd /var/www/luzicity/current
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar workers
php artisan queue:restart

# Testes antes do deploy
php artisan test
npm run build

# Saúde
curl -fsS https://luzicity.com.br/up
curl -fsS https://luzicity.com.br/health/ready
curl -fsS https://luzicity.com.br/radio/estado
```

## 22. Referências internas

- `docs/operations/deploy.md`;
- `docs/operations/backups.md`;
- `docs/operations/queues.md`;
- `docs/operations/cache.md`;
- `docs/operations/observability.md`;
- `docs/operations/testing.md`;
- `infrastructure/azuracast/README.md`;
- `deploy/env.production.example`;
- `.github/workflows/ci-cd.yml`.
