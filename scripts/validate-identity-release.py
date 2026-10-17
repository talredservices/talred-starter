#!/usr/bin/env python3
"""Validate distribution metadata; registry readiness is a separate gate."""
import json
import re
from pathlib import Path

root = Path(__file__).resolve().parents[1]
contract = json.loads((root / "identity-release.json").read_text())
if contract.get("version") != 1 or contract.get("repository") != "https://github.com/talredservices/identity":
    raise SystemExit("Identity release owner/version is invalid")
expected = {part: f"ghcr.io/talredservices/identity-{part}" for part in ("server", "nginx", "console")}
if contract.get("images") != expected or contract.get("publishedReleaseRequiredForDeployment") is not True:
    raise SystemExit("Identity distribution contract is invalid")
selection = root / contract["selection"]
example = selection.with_name(selection.name + ".example")
selections = [path for path in (selection, example) if path.is_file()]
if selections:
    for path in selections:
        values = dict(line.split("=", 1) for line in path.read_text().splitlines() if line and not line.startswith("#"))
        for part, repository in expected.items():
            value = values.get(f"TALRED_IDENTITY_{part.upper()}_IMAGE", "")
            if not re.fullmatch(r"sha256:[a-f0-9]{64}|" + re.escape(repository) + r"@sha256:[a-f0-9]{64}", value):
                raise SystemExit(f"Identity {part} image must be content-addressed in {path.name}")
    print("Identity image selections are content-addressed")
else:
    print("Identity distribution metadata valid; no local or example image selection is present")
print("Registry access and upstream CI readiness are not established by this check")
