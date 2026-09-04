#!/usr/bin/env bash
# Project: PPL Study
# Purpose: Build a clean release ZIP without deployment secrets or local cache.
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VERSION="$(sed -n 's/.*"current_version"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' "$ROOT/release-metadata.json" | head -n 1)"
VERSION="${VERSION:-unknown}"
OUT="${1:-$ROOT/PPL-Study-${VERSION}-deploy.zip}"

if command -v php >/dev/null 2>&1; then
  bash "$ROOT/scripts/check_syntax.sh"
  php "$ROOT/scripts/validate_question_bank.php"
fi

rm -f "$OUT"
cd "$ROOT"
zip -qr "$OUT" . \
  -x 'config.php' \
  -x 'cache/*' \
  -x 'deploy/*' \
  -x '*.zip' \
  -x '*.pdf' \
  -x '.git/*' \
  -x '.DS_Store' \
  -x '__pycache__/*' \
  -x 'scripts/__pycache__/*' \
  -x '.pytest_cache/*' \
  -x '*.pyc'
printf 'Created %s\n' "$OUT"
