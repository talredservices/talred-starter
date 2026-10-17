#!/usr/bin/env python3
"""Safely copy the Starter's Codex skills into a larger workspace."""

from __future__ import annotations

import argparse
import filecmp
import shutil
import sys
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[1]
SOURCE = REPO_ROOT / ".agents" / "skills"
SKILLS = (
    "talred-agile-delivery",
    "talred-api",
    "talred-client",
    "talred-feature-ddd",
    "talred-improve",
    "talred-themes",
    "identity-hosted-auth-pages",
)
SHARED_FILES = ("catalog.md",)


def same_tree(source: Path, destination: Path) -> bool:
    if source.is_symlink() or destination.is_symlink():
        return source.is_symlink() and destination.is_symlink() and source.resolve() == destination.resolve()
    if not source.is_dir() or not destination.is_dir():
        return False
    comparison = filecmp.dircmp(source, destination, ignore=[])
    if comparison.left_only or comparison.right_only or comparison.funny_files:
        return False
    if any(not filecmp.cmp(source / name, destination / name, shallow=False) for name in comparison.common_files):
        return False
    return all(same_tree(source / name, destination / name) for name in comparison.common_dirs)


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("command", choices=("install", "check"))
    parser.add_argument("--target", type=Path, default=Path.cwd(), help="workspace root (default: current directory)")
    parser.add_argument("--dry-run", action="store_true", help="show install actions without copying files")
    args = parser.parse_args()

    if args.command == "check" and args.dry_run:
        parser.error("--dry-run is only valid with install")

    target_root = args.target.expanduser().resolve()
    destination = target_root / ".agents" / "skills"
    conflicts: list[str] = []
    missing: list[str] = []

    for name in (*SKILLS, *SHARED_FILES):
        source = SOURCE / name
        dest = destination / name
        if not source.exists():
            print(f"ERROR missing source: {source}")
            return 2
        if not dest.exists():
            missing.append(name)
            action = "would copy" if args.dry_run else ("copying" if args.command == "install" else "missing")
            print(f"{action}: {dest}")
            if args.command == "install" and not args.dry_run:
                destination.mkdir(parents=True, exist_ok=True)
                if source.is_dir():
                    shutil.copytree(source, dest)
                else:
                    shutil.copy2(source, dest)
            continue
        identical = same_tree(source, dest) if source.is_dir() else filecmp.cmp(source, dest, shallow=False)
        if identical:
            print(f"current: {dest}")
            continue
        conflicts.append(name)
        print(f"CONFLICT: {dest} differs from {source}; left unchanged")

    if args.command == "check" and missing:
        print(f"check failed: {len(missing)} managed item(s) missing")
        return 1
    if conflicts:
        print(f"finished with {len(conflicts)} conflict(s); no conflicting item was overwritten")
        return 2
    if args.dry_run:
        print(f"dry run complete: {len(missing)} item(s) would be copied")
    elif args.command == "check":
        print("check passed: all managed skills match the Starter source")
    else:
        print(f"install complete: {len(missing)} item(s) copied")
    return 0


if __name__ == "__main__":
    sys.exit(main())
