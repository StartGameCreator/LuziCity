#!/usr/bin/env bash
set -Eeuo pipefail

environment="${1:-}"
if [[ "$environment" != "staging" && "$environment" != "production" ]]; then
  echo "Uso: scripts/deploy.sh staging|production"
  exit 2
fi

php artisan luzicity:deploy-check --environment="$environment"
php artisan luzicity:backup --verify
php artisan down --retry=60 --refresh=15

restore_service() {
  php artisan up || true
}
trap restore_service EXIT

php artisan migrate --force --isolated
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
php artisan schedule:interrupt

php artisan up
trap - EXIT

echo "Deploy ${environment} concluido."
