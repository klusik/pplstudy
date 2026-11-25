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
python3 -m py_compile scripts/apply_curation.py

if command -v node >/dev/null 2>&1; then
  node --check public/assets/theory/meteorology/lab-engine.js
  node --check public/assets/theory/meteorology/lab-physics.js
  node --check public/assets/theory/meteorology/lab-weather.js
  node scripts/test_meteorology_labs.js
elif [[ "${CI:-}" == "true" ]]; then
  echo "Node.js is required for laboratory tests in CI" >&2
  exit 1
else
  echo "Node.js unavailable; laboratory checks skipped locally."
fi

echo "All fast checks passed."
