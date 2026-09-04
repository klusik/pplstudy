# AGENTS.md

## Project principles

This is a framework-free PHP 8.1+ application designed for ordinary shared hosting. Preserve the modular ownership described in `ARCHITECTURE.md` and `CODEMAP.md`.

## Editing rules

- Keep existing file docblocks and function docstrings.
- Do not move SQL into views or duplicate service SQL in controllers.
- Keep root entry points thin.
- Keep administrator mutations behind administrator authorization and CSRF.
- Keep study-card content behind an active authenticated account with completed permanent-password setup.
- Keep account creation invitation-only through administrator workflows.
- Escape user-controlled or administrator-editable output with `e()`.
- Keep numeric/input limits centralized in `app/configuration_defaults.php` unless they are immutable source-bank invariants.
- Do not add Composer or npm runtime dependencies without an explicit architectural decision.
- Do not make the server parse PDFs during normal requests or installation.
- Keep the source-derived correct answer authoritative. Generated study choices must stay editable and clearly modeled as project study data.
- Treat `data/question-bank.json` as source-derived data, not as application code.
- Add new schema changes as new migration files. Do not rewrite an already released migration.
- Keep `config.php`, local cache, and generated deploy ZIPs out of version control.

## Required checks before handoff

```bash
bash scripts/check_syntax.sh
php scripts/validate_question_bank.php
```

If a database migration or importer behavior changed, also run the acceptance cases in `TESTING.md` against a disposable MySQL or MariaDB database.

## Release files

Update together when application version changes:

- `app/bootstrap.php`
- `README.md`
- `PATCH_NOTES.md`
- `release-metadata.json`

## Source-bank changes

When updating the compilation:

1. retain the source PDF outside the deployed project
2. run `scripts/build_question_bank.py`
3. run `scripts/validate_question_bank.php`
4. review subject/card count diff
5. inspect attachment references
6. regenerate and review study choices, then test import on a disposable database
7. update `data/README.md` source metadata if needed
