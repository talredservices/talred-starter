#!/usr/bin/env python3
"""Scan the actual candidate, including new files, without printing secret values."""
from pathlib import Path
import re
import subprocess
import sys

root = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else Path(__file__).resolve().parents[1]
paths = subprocess.check_output(
    ["git", "ls-files", "-z", "--cached", "--others", "--exclude-standard"], cwd=root
).decode().split("\0")
secret = re.compile(rb"AKIA[0-9A-Z]{16}|-----BEGIN [A-Z ]*PRIVATE KEY-----|github_pat_[A-Za-z0-9_]{20,}|gh[pousr]_[A-Za-z0-9_]{20,}|xox[baprs]-[A-Za-z0-9-]{20,}")
failures = []
count = 0
for name in sorted(set(paths)):
    path = root / name
    if not name or not path.is_file():
        continue
    count += 1
    local_env = path.name.startswith(".env") and not path.name.endswith(".example")
    runtime = any(part in path.parts for part in ("node_modules", "vendor", ".zolta", ".ai", ".nuxt", ".output"))
    runtime = runtime or path.suffix in (".sqlite", ".sqlite3")
    runtime = runtime or (("/bootstrap/cache/" in "/" + name or "/storage/framework/" in "/" + name or "/storage/logs/" in "/" + name) and path.name != ".gitignore")
    if local_env or runtime:
        failures.append(f"Local/runtime file in release candidate: {name}")
    elif path.is_symlink():
        failures.append(f"Review source symlink before release: {name}")
    elif secret.search(path.read_bytes()):
        failures.append(f"High-confidence secret pattern in candidate: {name}")
if failures:
    print("\n".join(failures), file=sys.stderr)
    raise SystemExit(1)
print(f"Release candidate scan passed ({count} files, including untracked source).")
