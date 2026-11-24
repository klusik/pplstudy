# Patch Notes

## 0.3.0 - 2026-11-24

### Added

- Czech PPL(A) meteorology textbook, covering the topics of all 129 published meteorology questions in the existing bank, with 14 chapters, 23 original SVG diagrams, worked examples, tables, self-check exercises and chapter-level sources.
- Reusable authenticated theory-course registry and reading layout; a textbook button on the study picker, meteorology dashboard card and meteorology question pages. Other subjects retain their existing modes.
- Links to current Czech meteorological products and operational guidance; explicit distinction between study approximations and actual operational limits.
- Automated coverage, package asset, route, access-control and existing-learning-mode regression checks.

### Database and deployment

- Application schema remains generation 4; the question-bank contents and schema are unchanged. Existing 0.2.0 installations can use the web updater. No migration, import, installer rerun or FTP overlay is needed.


## 0.2.0 - 2026-11-24

### Added

- Administrator-only **Aktualizace** page for checking and installing stable GitHub releases, including forced same-version repair.
- Anonymous HTTPS download from the public `klusik/pplstudy` repository, pinned release identity, provider SHA-256, bounded archive staging and verified per-file inventory.
- Separate update-job lock and exclusive activation gate, verified original backups, atomic file replacement and automatic recovery of interrupted activation.
- GitHub Actions checks PHP 8.1 syntax, question-bank invariants, package integrity and updater regressions, then publishes a versioned ZIP on main.

### Database

- No database migration. Application schema remains generation 4; question-bank schema and compilation are unchanged.
- The updater does not import or overwrite live questions, accounts, source PDFs or local configuration.

### Deployment

- Existing 0.1.x installations require one complete FTP/hosting-file-manager overlay of the 0.2.0 distribution ZIP. Preserve `config.php`, runtime cache and uploaded documents.
- The web updater requires PHP cURL and ZipArchive, outbound HTTPS, and writable managed files/cache.
- The first updater supports same-schema stable releases only; downgrade, arbitrary commits, unlisted-file deletion and automated visitor updates are not included.


## 0.1.1 - 2026-09-04

### Fixed

- Made `config.php` `base_url` authoritative for application-generated and canonical URLs on shared hosting.
- Prevented WEDOS-style internal `SCRIPT_NAME` paths such as `/subdom/ppl` from leaking into installer and application redirects.
- Canonicalize requests that arrive with a leaked hosting-internal prefix back to the configured public URL while preserving the application route and query string.
- Changed fresh-installer base URL detection to derive the public path from browser-visible `REQUEST_URI` instead of hosting-internal `SCRIPT_NAME`.
- The locked installer now uses configured `base_url` for its canonical installer location and **Open admin** link.
- Tightened base-path stripping to require a complete path-segment match.
- Normalized previously stored post-login return targets so stale `/subdom/ppl/...` session paths cannot reintroduce the hosting alias; installer/login targets fall back safely.

### Database

- No database migration. Application schema remains generation 4.

### Deployment

- Overlay the affected files on 0.1.0. Preserve the existing `config.php`.

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
