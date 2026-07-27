#!/usr/bin/env bash
set -Eeuo pipefail

application_root="${1:-}"
environment="${2:-}"

if [[ -z "$application_root" || ( "$environment" != "staging" && "$environment" != "production" ) ]]; then
  echo "Uso: scripts/release.sh /var/www/luzicity staging|production"
  exit 2
fi

current="${application_root}/current"
shared="${application_root}/shared"
release_id="$(date -u +%Y%m%d%H%M%S)"
release="${application_root}/releases/${release_id}"

[[ -L "$current" && -f "${shared}/.env" ]] || {
  echo "Estrutura current/shared invalida."
  exit 1
}

repository="$(git -C "$current" remote get-url origin)"
branch="$(git -C "$current" rev-parse --abbrev-ref HEAD)"
git clone --depth 1 --branch "$branch" "$repository" "$release"

rm -rf "${release}/storage"
ln -s "${shared}/storage" "${release}/storage"
ln -s "${shared}/.env" "${release}/.env"

cd "$release"
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
scripts/deploy.sh "$environment"

ln -sfn "$release" "${application_root}/current.next"
mv -Tf "${application_root}/current.next" "$current"

find "${application_root}/releases" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' \
  | sort -rn \
  | tail -n +6 \
  | cut -d' ' -f2- \
  | xargs -r rm -rf --

echo "Release ${release_id} ativada."
