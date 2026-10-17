#!/usr/bin/env bash
set -Eeuo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if [[ ! -f "$root/.env" ]]; then
  printf 'No Starter Docker .env exists; there is no standalone stack to stop.\n'
  exit 0
fi

docker compose --env-file "$root/.env" -f "$root/docker/compose.yaml" down
printf 'Starter containers stopped. Named volumes and local credentials were preserved.\n'
