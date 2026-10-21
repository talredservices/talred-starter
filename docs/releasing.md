# Beta release checklist

Talred Starter is intended to be released as a public reference project. Treat
the first `0.x` release as a beta: it is suitable for evaluation and adaptation,
and its APIs and setup details may still change. Do not describe the sample
stack as production-hardened.

## Before tagging

- Release from the reviewed `main` commit after its CI run succeeds.
- Confirm the repository contains the complete Starter tree, including the
  standalone `docker/` setup, the host-run guide, the Tasks Laravel API, the Nuxt
  app, and Starter-owned Identity page layers.
- Verify a clean public clone using both paths in
  [developer setup](developer-setup.md): native Identity source and standalone
  Docker. Use disposable local data for this check.
- Run the repository checks (`./scripts/ci pre-push`) through
  `../local-dev-ops/scripts/with-stack-paused --` in the canonical workspace.
  The wrapper protects the shared workspace Compose services; it does not
  manage the independent Starter Docker stack.
- Validate the selected public Identity image digests with
  `python3 scripts/validate-identity-release.py`. Review the Identity commit
  pin and the Console build context together; Starter must use Identity source
  for development and the published, content-addressed images for release mode.
- Review the Git diff and tracked-secret scan. Keep local `.env` files,
  databases, runtime credentials, and generated build output out of the tag.
  Run `python3 scripts/check-release-tree.py` to include untracked candidate
  source in this review. The web audit must follow a production build: it
  verifies the runtime artifact as well as the exact, expiring tooling
  exceptions in [the dependency policy](security/dependency-policy.md).
- Check the package visibility and repository access settings in GitHub. The
  image workflow can publish packages, but this repository does not establish
  that those packages are public.

For isolated Docker verification in the shared workspace, prepare configuration
with `./docker/up.sh --prepare-only`, run the Compose build through
`with-stack-paused`, then start with `./docker/up.sh --no-build`. Keep runtime
startup outside the build wrapper. Use a distinct Compose project and ports;
never clear existing developer data to simulate a fresh installation.

## Tag and publish

1. Choose the next beta tag using SemVer prerelease form, for example
   `v0.1.0-beta.1`; do not reuse a published tag.
2. Create an annotated tag on the exact reviewed `main` commit and push the tag.
3. The `Container images` workflow currently publishes automatically on pushes
   to `main` and `develop`, and can be dispatched manually for another ref.
   For a beta tag, dispatch it for that tag after verifying the tag points to
   the reviewed commit. It publishes the web app, Tasks API, and its Nginx image with the ref name and commit SHA.
4. Wait for the image workflow to finish. Inspect the resulting manifests and
   confirm unauthenticated pulls work before announcing published images.
5. Create GitHub release notes for the tag. Summarize supported local run
   modes, required Identity pairing, known limitations, and migration notes.

The Identity images have their own source repository and release process. Do
not rebuild or retag Identity images from Starter. Starter's Console is built
from the selected public Identity source plus the Starter-owned page layers.

## After publication

- Pull each Starter image by the published digest and check its health endpoint
  or startup behavior in an isolated local environment.
- Repeat the documented fresh-clone setup from a clean directory. A successful
  workflow run alone does not verify database initialization, registration,
  invitations, or browser login.
- Record the tag, source commit, image digests, verification evidence, and any
  remaining limitations in the release notes.
