#!/usr/bin/env python3
"""Build the managed PPL Study release ZIP and its SHA-256 file inventory.

Author: Rudolf Klusal
The release package is derived exclusively from tracked regular files.
"""

import argparse
import hashlib
import json
import re
import subprocess
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile

ROOT = Path(__file__).resolve().parents[1]
EXACT_PATHS = {
    ".htaccess", "index.php", "install.php", "reset.php", "config.example.php",
    "public/index.php", "release-metadata.json", "README.md", "PATCH_NOTES.md",
    "ARCHITECTURE.md", "CODEMAP.md", "DATABASE.md", "TESTING.md", "LICENSE",
    "app/.htaccess", "database/.htaccess", "data/.htaccess", "data/README.md",
    "data/source-manifest.json", "data/question-bank.json",
    "data/source-documents/.htaccess", "data/source-documents/README.md",
}
MANAGED_PATTERN = re.compile(
    r"(?:app/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.php"
    r"|database/migrations/[A-Za-z0-9_-]+\.php"
    r"|public/assets/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9._-]+\.(?:css|js|svg|png|jpe?g|webp|ico|woff2?)"
    r"|data/(?:curation|source-exact)/[A-Za-z0-9_-]+\.json)"
)


def managed_path(path: str) -> bool:
    """Match the PHP updater's explicit deployment inventory."""
    return path in EXACT_PATHS or MANAGED_PATTERN.fullmatch(path) is not None


def build_package(destination: Path) -> None:
    """Package immutable checked-out release files; fail on inventory drift."""
    metadata = json.loads((ROOT / "release-metadata.json").read_text(encoding="utf-8"))
    version = metadata["current_version"]
    if re.fullmatch(r"(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)", version) is None:
        raise ValueError("Invalid release version")
    schema = metadata["releases"][version]["application_schema"]
    bootstrap = (ROOT / "app/bootstrap.php").read_text(encoding="utf-8")
    if f"const APP_VERSION = '{version}';" not in bootstrap or f"const APP_SCHEMA_VERSION = {schema};" not in bootstrap:
        raise ValueError("Release metadata disagrees with bootstrap")

    tracked = subprocess.check_output(["git", "ls-files", "-z"], cwd=ROOT).decode("utf-8").split("\0")
    files = sorted(path for path in tracked if path and managed_path(path))
    required = EXACT_PATHS | {"app/bootstrap.php", "app/update_gate.php", "app/services/updater.php"}
    if not required.issubset(files):
        raise ValueError(f"Missing managed files: {sorted(required - set(files))}")
    if len(files) > 1000 or len({path.lower() for path in files}) != len(files):
        raise ValueError("Invalid managed inventory size or duplicate paths")

    # Reject newly introduced runtime files that the installed updater cannot
    # safely understand, instead of silently producing a broken release.
    for path in tracked:
        if path.startswith(("app/", "public/", "database/migrations/")) and not managed_path(path):
            raise ValueError(f"Unmanaged runtime path: {path}")

    inventory = []
    total = 0
    destination.parent.mkdir(parents=True, exist_ok=True)
    temporary = destination.with_suffix(".tmp")
    try:
        with ZipFile(temporary, "w", compression=ZIP_DEFLATED, compresslevel=9) as package:
            for relative in files:
                source = ROOT / relative
                if not source.is_file() or source.is_symlink() or any(
                    parent.is_symlink() for parent in source.parents if parent != ROOT and ROOT in parent.parents
                ):
                    raise ValueError(f"Unsafe source file: {relative}")
                content = source.read_bytes()
                total += len(content)
                if total > 80 * 1024 * 1024:
                    raise ValueError("Expanded package exceeds updater limit")
                inventory.append({"path": relative, "size": len(content), "sha256": hashlib.sha256(content).hexdigest()})
                package.writestr(relative, content)
            manifest = {"format": 1, "project": "PPL Study", "version": version, "schema": schema, "files": inventory}
            package.writestr("update-manifest.json", json.dumps(manifest, ensure_ascii=False, separators=(",", ":")))
        if temporary.stat().st_size > 25 * 1024 * 1024:
            raise ValueError("Compressed package exceeds updater limit")
        temporary.replace(destination)
    finally:
        temporary.unlink(missing_ok=True)
    print(f"Built {destination}: {len(files)} verified files, {total} uncompressed bytes")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("destination", type=Path)
    build_package(parser.parse_args().destination.resolve())
