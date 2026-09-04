# Patch Notes

## 0.3.1 - 2026-09-04

### Fixed

- fixed System Health database inspection with native PDO prepares on MySQL/MariaDB
- replaced the unsupported prepared `SHOW TABLES LIKE ?` metadata statement with exact `information_schema` lookups
- column inspection now uses the same exact metadata-query path
- pending migration status is visible again after database inspection completes

### Upgrade

Upload the affected 0.3.1 files and reload **Admin > System Health**. No new database migration is included in this patch. If `202609030003_user_accounts` is still listed as pending, run it there. If it already ran during the previous attempt, System Health will report no pending migrations.

## 0.3.0 - 2026-09-03

### Added

- invitation-only learner profiles managed by administrators
- shared learner/administrator login at `/login`
- User and Administrator account roles
- administrator user list, account creation, role assignment, activation, and password reset
- strict one-time temporary-password login with immediate credential consumption
- mandatory permanent-password setup before study content becomes available
- authenticated learner profile and voluntary password change
- migration `202609030003_user_accounts`
- compatibility path that lets an existing 0.2.0 administrator reach System Health before applying the new migration
- user-account diagnostics and active-administrator checks

### Changed

- the public dashboard keeps subject and exam metadata visible while question content stays inside authenticated study routes
- `/study`, `/question/{sequence}`, `/drill`, `/search`, and `/api/quiz` now require an active learner account
- administrator authentication uses the same account/session model as learner access
- account role and active state are refreshed from the database during authenticated requests
- the installer creates the initial administrator with a permanent password
- account recovery restores an active permanent Administrator account on the expanded schema
- deployment/project documentation now uses PPL Study terminology throughout

### Upgrade

After uploading 0.3.0 over 0.2.0, sign in with the existing administrator, open **Admin > System Health**, and run `202609030003_user_accounts`. Existing administrator credentials remain valid. Learner accounts can then be created in **Admin > Uživatelé**.

## 0.2.0 - 2026-09-03

### Added

- four editable study choices for all 880 cards
- automatic scoring on individual cards and custom drills
- paper-mode answer collection with scoring and wrong-answer review at completion
- generated-versus-edited choice tracking in the database and Admin card editor
- question-bank schema version 2 with strict four-choice validation
- incremental database migration `202609030002_question_choices`
- deterministic offline study-choice generator integrated into PDF regeneration
- path-safe Windows PowerShell deployment workflow behind `deploy.bat`

### Changed

- single-card study now starts with visible answer choices
- attachment questions expose their attachment link before answering
- custom drill reveals feedback and explanation after selection
- paper drill keeps correctness feedback for the result screen
- `deploy.bat` packages without bootstrapping PHP or requiring a database connection

### Upgrade

After uploading 0.2.0 over an existing 0.1.x installation, run the pending migration in **Admin > System Health**, then import the bundled bank in **Admin > Import**.

## 0.1.1 - 2026-09-03

### Changed

- rewrote the public hero and explanatory copy in direct, positive language
- simplified drill descriptions around the actual open-answer workflow
- rewrote the source and methodology page around the content and study features
- improved empty-state copy for progress, audit logs, and migrations
- aligned installer and recovery messages with the same direct style
- preserved the complete 880-card question bank unchanged

## 0.1.0 - 2026-09-03

Initial project release.

### Added

- new PPL Study codebase based on modular shared-hosting architecture principles
- 880-card bundled PPL(A) study bank in 9 subjects
- dynamic subject dashboard and browser-local progress
- open-answer study cards with reveal, explanation, warnings, source traces, and attachment links
- custom randomized drills and timed subject paper drills
- search across the study bank
- administrator dashboard, authentication, card editor, importer, settings, and System Health
- MySQL/MariaDB migration system and transactional bank synchronization
- shared-hosting installer and setup-key administrator recovery
- offline PDF-to-JSON builder and release validation scripts
- architecture, database, code map, testing, and contributor documentation

### Source handling

The application intentionally does not invent the four multiple-choice distractors absent from the supplied compilation. Drills use learner self-assessment after answer reveal.
