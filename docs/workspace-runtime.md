# Shared workspace Identity release images

This optional workflow requires the sibling `local-dev-ops` repository. Public
clones can use standalone Docker or the native setup without it.

## Release-image mode

Use this mode to run the Starter against the published Talred Identity images. The checked-in selection example pins all three images by manifest digest. Development mode builds Identity from its canonical sibling source. Release mode runs Identity without mounting its source; the Compose overlay mounts the Starter-owned hosted-auth integration bundle into the Identity hosts that need it.

Select the published Identity release and start the Starter services from the workspace root:

```bash
cp talred-starter/runtime/identity/.env.release.example talred-starter/runtime/identity/.env.release
docker compose --env-file local-dev-ops/local/.env \
  --env-file talred-starter/runtime/identity/.env.release \
  -f local-dev-ops/local/compose.yaml \
  -f local-dev-ops/local/compose.identity-release.yaml \
  pull --include-deps starter-identity-api starter-identity-console
docker compose --env-file local-dev-ops/local/.env \
  --env-file talred-starter/runtime/identity/.env.release \
  -f local-dev-ops/local/compose.yaml \
  -f local-dev-ops/local/compose.identity-release.yaml \
  up -d starter-identity-api starter-identity-console zolta-starter-web starter-api
```

Keep the same two environment files and Compose files for subsequent release-mode operations. To build a local Identity release from source instead, run `identity/scripts/build-local-release talred-starter/runtime/identity/.env.release` before Compose; this replaces the local selection with local image IDs. Use the base Compose file alone to select canonical-source development mode. Existing project IDs, callbacks, database, and runtime volumes are preserved.

The local Starter overlay runs the Identity server and Console images; its release selection also records the Nginx digest for Identity deployments that use that front end. These packages are public and do not require a registry login. If a saved GHCR login causes `docker compose pull` to return `denied`, retry the pull with an empty temporary Docker config so your saved credentials remain untouched:

```bash
STARTER_IDENTITY_PULL_CONFIG="$(mktemp -d)"
DOCKER_CONFIG="$STARTER_IDENTITY_PULL_CONFIG" docker compose --env-file local-dev-ops/local/.env \
  --env-file talred-starter/runtime/identity/.env.release \
  -f local-dev-ops/local/compose.yaml \
  -f local-dev-ops/local/compose.identity-release.yaml \
  pull --include-deps starter-identity-api starter-identity-console
```

The public image pins in `runtime/identity/.env.release.example` were published from Identity commit `0fb11ef1b570b88dd6f63cdc8d04d0e305f6a89d`. Update the pins from the corresponding GHCR package manifests when adopting a newer Identity release. Local image IDs are immutable local artifacts, not published GHCR releases; a local build does not update the published pins.
