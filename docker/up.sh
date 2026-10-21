#!/usr/bin/env bash
set -Eeuo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$root"

prepare_only=false
skip_build=false
for argument in "$@"; do
  case "$argument" in
    --prepare-only) prepare_only=true ;;
    --no-build) skip_build=true ;;
    -h|--help)
      printf 'Usage: ./docker/up.sh [--prepare-only] [--no-build]\n\n'
      printf '  --prepare-only  Generate local configuration and validate Compose; do not start or build.\n'
      printf '  --no-build      Start using images already built for this Compose project.\n'
      exit 0 ;;
    *) printf 'Unknown option: %s\n' "$argument" >&2; exit 64 ;;
  esac
done

for tool in docker openssl; do
  command -v "$tool" >/dev/null 2>&1 || {
    printf 'Required command not found: %s\n' "$tool" >&2
    exit 1
  }
done
docker compose version >/dev/null

if [[ ! -f .env ]]; then
  cp docker/.env.example .env
fi
chmod 600 .env

dotenv_get() {
  local key="$1"
  awk -v key="$key" 'index($0, key "=") == 1 { value = substr($0, length(key) + 2); if (value ~ /^".*"$/) value = substr(value, 2, length(value) - 2); print value; exit }' .env
}

dotenv_set() {
  local key="$1" value="$2" temp
  temp="$(mktemp "${TMPDIR:-/tmp}/talred-starter-env.XXXXXX")"
  awk -v key="$key" 'index($0, key "=") != 1 { print }' .env > "$temp"
  printf '%s="%s"\n' "$key" "$value" >> "$temp"
  chmod 600 "$temp"
  mv "$temp" .env
}

dotenv_default() {
  local key="$1" value="$2"
  if [[ -z "$(dotenv_get "$key")" ]]; then
    dotenv_set "$key" "$value"
  fi
}

dotenv_remove() {
  local key temp
  for key in "$@"; do
    temp="$(mktemp "${TMPDIR:-/tmp}/talred-starter-env.XXXXXX")"
    awk -v key="$key" 'index($0, key "=") != 1 { print }' .env > "$temp"
    chmod 600 "$temp"
    mv "$temp" .env
  done
}

dotenv_remove STARTER_IDENTITY_AGENT_EMAIL STARTER_IDENTITY_AGENT_PASSWORD

while IFS='=' read -r key value; do
  [[ "$key" == TALRED_IDENTITY_* ]] || continue
  value="${value%$'\r'}"
  [[ -n "$value" ]] || continue
  dotenv_default "$key" "$value"
done < runtime/identity/.env.release.example

identity_release="$(dotenv_get TALRED_IDENTITY_RELEASE)"
[[ "$identity_release" =~ ^[a-f0-9]{40}$ ]] || {
  printf 'The pinned Identity release must be a 40-character commit SHA.\n' >&2
  exit 1
}
dotenv_default TALRED_IDENTITY_SOURCE_CONTEXT "https://github.com/talredservices/identity.git#${identity_release}"

random_hex() {
  openssl rand -hex "$1"
}

random_uuid() {
  local hex
  hex="$(random_hex 16)"
  printf '%s-%s-%s-%s-%s' "${hex:0:8}" "${hex:8:4}" "${hex:12:4}" "${hex:16:4}" "${hex:20:12}"
}

dotenv_default MYSQL_PASSWORD "$(random_hex 32)"
dotenv_default LOCAL_UID "$(id -u)"
dotenv_default LOCAL_GID "$(id -g)"
dotenv_default MYSQL_ROOT_PASSWORD "$(random_hex 32)"
dotenv_default IDENTITY_APP_KEY "base64:$(openssl rand -base64 32 | tr -d '\n')"
dotenv_default STARTER_API_APP_KEY "base64:$(openssl rand -base64 32 | tr -d '\n')"
dotenv_default STARTER_IDENTITY_OWNER_PASSWORD "$(random_hex 32)"
dotenv_default STARTER_IDENTITY_CONSOLE_CLIENT_ID "$(random_uuid)"
dotenv_default STARTER_IDENTITY_CONSOLE_CLIENT_SECRET "$(random_hex 32)"
dotenv_default STARTER_IDENTITY_CONSOLE_SESSION_PASSWORD "$(random_hex 48)"
dotenv_default STARTER_WEB_SESSION_PASSWORD "$(random_hex 48)"
dotenv_default STARTER_IDENTITY_HOSTED_APPLICATIONS_TOKEN "$(random_hex 32)"
dotenv_default STARTER_IDENTITY_SESSION_SECRET "$(random_hex 48)"

compose() {
  # Keep Compose's base directory at docker/, where the relative build and
  # bind-mount paths in compose.yaml resolve back into the Starter repository.
  docker compose --env-file "$root/.env" -f "$root/docker/compose.yaml" "$@"
}

compose config -q
if [[ "$prepare_only" == true ]]; then
  printf 'Local configuration prepared. No images built or services started.\n'
  exit 0
fi
printf 'Pulling the pinned public Identity Server and Nginx images...\n'
pull_config="$(mktemp -d "${TMPDIR:-/tmp}/talred-starter-docker-config.XXXXXX")"
chmod 700 "$pull_config"
trap 'rm -rf "$pull_config"' EXIT
DOCKER_CONFIG="$pull_config" compose pull identity-init identity-api identity-nginx
rm -rf "$pull_config"
trap - EXIT
if [[ "$skip_build" != true ]]; then
  printf 'Building the Starter apps and the Identity Console with Starter auth layers...\n'
  compose build identity-console starter-api web
fi
printf 'Starting isolated databases and the Identity API/Console...\n'
compose up -d --no-build --wait --wait-timeout 180 mysql redis
compose up -d --no-build --wait --wait-timeout 180 identity-api identity-nginx
printf 'Provisioning Starter, Fresh SaaS, and Tasks through the Identity management API...\n'
compose run --rm provisioner
printf 'Applying the generated Identity onboarding configuration...\n'
compose up -d --no-build --wait --wait-timeout 180 identity-api identity-nginx
printf 'Starting the Starter apps...\n'
compose up -d --no-build --wait --wait-timeout 300 identity-console starter-api web

printf '\nTalred Starter is ready.\n'
printf 'Starter:        http://localhost:%s\n' "$(dotenv_get STARTER_WEB_PORT)"
printf 'Identity:       http://localhost:%s/admin/sign-in\n' "$(dotenv_get IDENTITY_CONSOLE_PORT)"
printf 'Identity API:   http://localhost:%s\n' "$(dotenv_get IDENTITY_API_PORT)"
printf 'Tasks API:      http://localhost:%s\n' "$(dotenv_get STARTER_API_PORT)"
printf 'Run ./docker/credentials.sh to display the local Identity Console owner credentials.\n'
