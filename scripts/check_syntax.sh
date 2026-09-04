#!/usr/bin/env bash
# Project: PPL Study
# Purpose: Fast release-time syntax and bank validation.
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if ! command -v php >/dev/null 2>&1; then
  echo "php not found on PATH" >&2
  exit 1
fi

while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
  printf 'OK PHP %s\n' "$file"
done < <(find . -type f -name '*.php' -not -path './cache/*' -print0 | sort -z)

php scripts/validate_question_bank.php
python3 -m py_compile scripts/build_question_bank.py

echo "All fast checks passed."
