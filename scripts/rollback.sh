#!/usr/bin/env bash
set -Eeuo pipefail

application_root="${1:-}"
release_name="${2:-}"

if [[ -z "$application_root" || -z "$release_name" ]]; then
  echo "Uso: scripts/rollback.sh /var/www/luzicity RELEASE_ANTERIOR"
  exit 2
fi

target="${application_root}/releases/${release_name}"
current="${application_root}/current"

if [[ ! -d "$target" || ! -L "$current" ]]; then
  echo "Release ou link current invalido."
  exit 1
fi

ln -sfn "$target" "${application_root}/current.next"
mv -Tf "${application_root}/current.next" "$current"

cd "$current"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up

echo "Rollback de codigo concluido para ${release_name}. O banco nao foi revertido."
