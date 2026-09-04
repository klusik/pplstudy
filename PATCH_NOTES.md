# Patch Notes

## 0.1.0 - 2026-09-04

First public release of PPL Study. Earlier development milestones were commits, not published versions; their accumulated work is represented by this single release.

### Added

- Framework-free PHP 8.1+ application for ordinary shared hosting.
- Invitation-only learner and administrator accounts with one-time temporary passwords, mandatory permanent-password setup, role management, activation controls, CSRF protection, throttling, and audit logging.
- Public subject overview plus authenticated study cards, learning and quiz modes, randomized and timed drills, search, automatic scoring, stars, and browser-local progress.
- Administrator workflows for users, questions, source documents, settings, question-bank import, migrations, and system diagnostics.
- Transactional, versioned question-bank importer and four deterministic database migrations.
- Complete bank of 896 active questions across nine Czech PPL(A) subjects.
- Source-exact wording, four published choices in source order, authoritative correct answers, and curated explanations for all 896 questions.
- Project-owned SVG study redraws for attachment-dependent questions.
- Offline question-bank build, curation, validation, and deployment tooling.

### Changed

- Consolidated all pre-release development work under version `0.1.0`; removed the fictional `0.1.1` through `0.12.0` release history.
- Source PDFs are intentionally excluded from version control and deployment packages. Administrators may associate locally supplied PDFs through **Admin > Zdroje**.
- Generated deployment output, local configuration, runtime cache, and tooling artifacts are excluded from version control.

### Fixed

- Release metadata, application constants, documentation, and package naming now agree on `0.1.0`.
- Removed local deployment PDFs, including a personal payment-confirmation document, from the release tree.

### Database

- Fresh installations apply migrations `202609030001_initial_schema` through `202609040004_question_curation`.
- This is the first release; there is no supported upgrade path from an earlier published version.

### Deployment

- Build `PPL-Study-0.1.0-deploy.zip` with `deploy.bat` or `deploy.sh`.
- The release archive excludes `config.php`, runtime cache, generated archives, Git metadata, tooling caches, and source PDFs.
- Install into an empty database through `install.php`.

### Verification

- `bash scripts/check_syntax.sh`
- `php scripts/validate_question_bank.php`
- Fresh-install and administrator acceptance cases in `TESTING.md` against a disposable MySQL or MariaDB database before publication.
