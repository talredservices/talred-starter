#!/usr/bin/env bash
set -Eeuo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
env_file="$root/.env"
if [[ ! -f "$env_file" ]]; then
  printf 'No local credentials exist yet. Start the stack with ./docker/up.sh first.\n' >&2
  exit 1
fi

dotenv_get() {
  local key="$1"
  awk -v key="$key" 'index($0, key "=") == 1 { value = substr($0, length(key) + 2); if (value ~ /^".*"$/) value = substr(value, 2, length(value) - 2); print value; exit }' "$env_file"
}

printf 'Identity Console: http://localhost:%s/admin/sign-in\n' "$(dotenv_get IDENTITY_CONSOLE_PORT)"
printf 'Starter:         http://localhost:%s\n\n' "$(dotenv_get STARTER_WEB_PORT)"
printf 'Identity owner (Console administrator)\n'
printf '  Email:    %s\n' "$(dotenv_get STARTER_IDENTITY_OWNER_EMAIL)"
printf '  Password: %s\n' "$(dotenv_get STARTER_IDENTITY_OWNER_PASSWORD)"
