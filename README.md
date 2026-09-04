# PPL Study

A framework-free PHP 8.1+ study application for the Czech PPL(A) theoretical syllabus. The application provides an invitation-only learning environment with four-choice study cards, automatic scoring, randomized drills, subject paper drills, search, browser-local progress, learner profiles, and administrator management.

**Current version:** 0.12.0

**Deployment target:** ordinary PHP shared hosting with MySQL or MariaDB. The runtime uses native PHP, PDO, JavaScript, and Apache routing.

## What ships in 0.12.0

- All nine PPL(A) subjects now use source-exact wording, the published four answer choices in source order, and the marked correct answer from the directly supplied official PDFs.
- **Obecné znalosti o letadle** contains all 128 published source questions as independent records.
- **Navigace** contains all 137 published source questions as independent records. The two compact compilation cards that represented source questions 15-17 and 73/76 are split back into their five original questions.
- All 265 newly processed questions include curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations.
- The supplied official subject 8 and 9 PDFs are bundled as protected source documents with SHA-256 verification.
- Local project-owned study redraws cover AGK-002, AGK-003, AGK-005, AGK-006, AGK-007, AGK-008, AGK-011, NAV-002, NAV-004, NAV-014, NAV-017, NAV-019, NAV-022, NAV-024, and NAV-031.
- The integrated bank now contains exactly **896 active source questions**, and all 896 are `source_exact` and `verified`.
- Subject 8 occupies global questions **632-759**. Subject 9 occupies global questions **760-896**.
- Browser-local progress moves to `pplStudyProgressV7` because restoring the individual Navigation source questions changes sequence mapping inside subject 9.
- No database migration is included. Existing installations import the bundled bank after deploying the affected files.

## What ships in 0.11.0

- Subjects 1 through 7 use source-exact wording, the published four answer choices in source order, and the marked correct answer from the directly supplied official PDFs.
- Provedení a plánování letu contains all 101 published source questions as independent records.
- All 101 questions include curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations.
- The supplied official subject 7 PDF is bundled as a protected source document with SHA-256 verification.
- Local project-owned study redraws cover the PFP attachment set used by this subject and the CAP697 SEP1 2.2.3 performance-table questions.
- The integrated bank remains at 893 questions; 631 are source-exact and verified.
- Provedení a plánování letu occupies global questions 531-631. The next subject awaiting source-exact processing is **Obecné znalosti o letadle**, beginning at global question 632.
- Browser-local progress remains `pplStudyProgressV6` because this source-exact replacement preserves the subject question count and global sequence.
- No database migration is included. Existing installations import the bundled bank after deploying the affected files.

## What ships in 0.10.0

- Subjects 1 through 6 use source-exact wording, the published four answer choices in source order, and the marked correct answer from the directly supplied official PDFs.
- Provozní postupy contains all 60 published source questions as independent records.
- The compact compilation card that had merged wake-turbulence source questions 33 through 37 is split back into five separate source questions.
- All 60 Provozní postupy questions include curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations.
- The supplied official Provozní postupy PDF is bundled as a protected source document with SHA-256 verification.
- OPR-001 has a local project-owned SVG study redraw for the microburst questions 25 and 26.
- The integrated bank now contains 893 questions; 530 are source-exact and verified.
- Provozní postupy occupies global questions 471-530. The next subject awaiting source-exact processing is **Provedení a plánování letu**, beginning at global question 531.
- Browser-local progress moves to `pplStudyProgressV6` because restoring source questions 34 through 37 as independent records shifts global sequence numbers from subject 7 onward.
- No database migration is included. Existing installations import the bundled bank after deploying the affected files.

## What ships in 0.9.0

- Subjects 1 through 5 use source-exact wording, the published four answer choices in source order, and the marked correct answer from the directly supplied official PDFs.
- Letové zásady contains all 99 active published source questions. Source question 8 is explicitly marked `Vyřazena` in the official PDF and is therefore excluded from the active bank.
- The compact compilation card that had merged source questions 48 and 70 is split back into the two published questions about the critical angle of attack.
- All 99 active Letové zásady questions include curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations.
- The supplied official Letové zásady PDF is bundled as a protected source document with SHA-256 verification.
- PFA-003, PFA-008, PFA-009, PFA-010, and PFA-011 have local project-owned SVG study redraws for the attachment questions.
- The integrated bank now contains 889 questions; 470 are source-exact and verified.
- Letové zásady occupies global questions 372-470. The next subject awaiting source-exact processing is **Provozní postupy**, beginning at global question 471.
- Browser-local progress moves to `pplStudyProgressV5` because restoring source question 70 as its own record shifts global sequence numbers from that point onward.

## What ships in 0.8.0

- Subjects 1 through 4 use source-exact wording, the published four answer choices in source order, and the marked correct answer from the directly supplied official PDFs.
- Communication contains all 87 published source questions as individual records. The compact compilation card that had merged source questions 54 and 55 is split back into the two published questions.
- All 87 Communication questions include curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations.
- The supplied official Communication PDF is bundled as a protected source document with SHA-256 verification.
- The integrated bank now contains 888 questions; 371 are source-exact and verified.
- Communication occupies global questions 285-371. The next subject awaiting source-exact processing is **Letové zásady**, beginning at global question 372.
- Browser-local progress moves to `pplStudyProgressV4` because restoring source question 55 shifts global sequence numbers from subject 5 onward.

## What ships in 0.7.0

- Subjects 1, 2, and 3 use source-exact wording, the published four answer choices in source order, and the marked correct answer from the directly supplied official PDFs.
- Meteorology contains all 129 published source questions as individual records.
- All 129 Meteorology questions include curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations.
- The supplied official Meteorology PDF is bundled as a protected source document with SHA-256 verification.
- MET-001, MET-002, MET-004, and MET-005 have local project-owned SVG study redraws so attachment questions remain usable without an external dependency.
- The integrated bank contains 887 questions; 284 are source-exact and verified.

## What ships in 0.6.0

- Subjects 1 and 2 now use source-exact wording, the published four answer choices in source order, and the marked correct answer from the supplied official PDFs.
- Human Performance contains all 58 source questions as individual records, including the previously merged CO questions 7 and 9.
- All 58 Human Performance questions include curated Principle, memory aid, and operational-context explanations.
- The supplied official Human Performance PDF is bundled as a protected source document with SHA-256 verification.
- HPL-002 is available locally as a project-owned SVG study redraw for questions 55 and 56.
- The integrated bank now contains 887 questions; 155 are source-exact and verified.

## What ships in 0.5.0

- 886 current study questions in 9 PPL(A) subjects
- four answer choices on every bundled study card
- separate **Režim otázek** and **Režim učení**
- explicit curation and wording-fidelity state for every question
- exact source wording, exact four answer choices, source answer order, and marked correct answer for all 97 questions in **Právní předpisy v oblasti letectví**
- structured explanation sections retained for all 97 source-exact questions in the first subject
- protected source PDF storage, bundled first-subject source PDF, and **Admin > Zdroje**
- source trace, correct result, explanation, warning, and source-page metadata
- attachment code and official attachment link where applicable
- public dashboard with subject counts and examination-paper metadata
- authenticated random single-card study mode
- authenticated custom and timed paper drills
- authenticated question-bank search
- browser-local automatic scoring, stars, and progress tracking
- invitation-only learner accounts created by administrators
- learner and administrator roles in one account model
- administrator-issued one-time login passwords
- mandatory permanent-password setup after the first login
- account activation/deactivation, role assignment, and password reset from Admin
- administrator card editing, import, settings, audit log, and System Health
- transactional, versioned JSON question-bank importer
- deterministic database migrations
- three-screen shared-hosting installer
- emergency administrator recovery protected by a deployment-specific setup key
- offline PDF-to-JSON regeneration and study-choice generation scripts
- architecture, code map, database, testing, release, and contributor documentation
- MySQL/MariaDB-compatible System Health metadata inspection with native PDO prepares

## Access model

The root dashboard and source-information page are publicly readable. Study-card content is served only to authenticated accounts with completed permanent-password setup.

Administrators create every learner account in **Admin > Uživatelé**. Account creation accepts a username, display name, role, and temporary password. The temporary password authorizes exactly one successful login. That login immediately marks the temporary credential as consumed and routes the learner to permanent-password setup. A lost session during this step is recovered by an administrator assigning a fresh temporary password.

Administrators can promote another account to the Administrator role, deactivate accounts, reactivate accounts, and issue a fresh temporary password. The application protects the active administrator from disabling or demoting the account currently used for administration and preserves at least one active administrator.

## Question-source model

Every question contains four answer choices and one marked correct answer. Subjects 1 through 7 currently use the exact wording and exact answer sets from the supplied official ÚCL PDFs, including the original answer order. The remaining subjects keep the compilation-derived study model until their official PDFs are processed in the same way.

Source-exact subject data lives under `data/source-exact/`. The regeneration pipeline replaces compact merged study cards with the individual published questions before applying source-keyed explanation overlays.

For actual flight operations, current regulations, AIP, NOTAM, AFM/POH, and ATS instructions take precedence.

## Requirements

Server:

- PHP 8.1 or newer
- `pdo_mysql`
- `json`
- `mbstring`
- MySQL 5.7+ or MariaDB 10.4+ recommended
- Apache with `mod_rewrite` recommended for pretty URLs
- write access to project root during installation, `cache/`, and `data/source-documents/`

Offline question-bank regeneration additionally uses:

- Python 3.10+
- Poppler `pdftotext`

## Installation on shared hosting

1. Create an empty MySQL or MariaDB database and database user in the hosting control panel.
2. Upload the complete project into the document root of the chosen subdomain.
3. Open `https://your-subdomain.example/install.php`.
4. Step 1 verifies the environment and database connection.
5. Step 2 creates the initial administrator with a permanent password, runs migrations, validates/imports the bundled 893-question bank, writes `config.php`, and creates `cache/installed.lock`.
6. Step 3 links to `/admin`.

The hosting control panel owns database/database-user creation. The installer consumes those credentials and configures the application schema.

### URL rewriting

The root `.htaccess` serves existing static assets directly and rewrites application routes to `index.php`. The internal router also understands `?page=...` identifiers for hosting environments that use fallback routing.

### Configuration

`config.php` is generated by the installer and contains deployment credentials and secrets. `config.example.php` documents the supported shape.

The installer also creates a random `setup_key`. `reset.php` uses this key for emergency administrator recovery.

## Updating an existing 0.9.0 installation

1. Upload the 0.10.0 affected files while preserving the live `config.php` and runtime `cache/`.
2. Keep all six bundled official source PDFs under `data/source-documents/`.
3. Open **Admin > Import** and import the bundled question bank.
4. Verify that **Provozní postupy** contains 60 source-exact questions and spans global questions 471-530.
5. Verify that source questions 33, 34, 35, 36, and 37 are five independent wake-turbulence records.
6. Open source questions 25 and 26 and confirm the local OPR-001 microburst diagram renders.
7. Confirm the next subject, **Provedení a plánování letu**, begins at global question 531.

No database migration is included in 0.10.0. The import updates question rows transactionally. Restoring four source questions shifts later global sequence numbers, so browser-local progress starts cleanly on `pplStudyProgressV6`.

## Updating an existing 0.8.0 installation

1. Upload the 0.9.0 affected files while preserving the live `config.php` and runtime `cache/`.
2. Keep all five bundled official source PDFs under `data/source-documents/`.
3. Open **Admin > Import** and import the bundled question bank.
4. Verify that **Letové zásady** contains 99 active questions and begins at global question 372.
5. Verify that source question 8 is absent from the active bank because the official PDF marks it `Vyřazena`.
6. Open source questions 48 and 70 and verify that they are two independent source-exact records.
7. Confirm the next subject, **Provozní postupy**, begins at global question 471.

No database migration is included in 0.9.0. The import updates question rows transactionally. Restoring source question 70 as an independent record shifts later global sequence numbers, so browser-local progress starts cleanly on `pplStudyProgressV5`.

## Windows deployment packaging

`deploy.bat` is a thin wrapper around `scripts/deploy.ps1`. Local ZIP mode preserves project-relative directory paths and excludes `config.php`, runtime cache, generated ZIP files, and tooling caches.

Double-click `deploy.bat` for the interactive workflow, or run:

```bat
deploy.bat -Mode local -DeployFolder deploy -MakeZipDeploy true
```

## Public and account routes

| Route | Function |
| --- | --- |
| `/` | public subject dashboard |
| `/about` | source and examination structure |
| `/login` | learner/administrator login |
| `/account` | authenticated profile |
| `/account/password` | mandatory first-login setup and later password change |
| `/study` | authenticated study-mode setup |
| `/question/{sequence}` | authenticated individual study card; `?mode=quiz` or `?mode=learn` |
| `/drill` | authenticated custom and paper drill |
| `/search` | authenticated question-bank search |
| `/api/quiz` | authenticated first-party drill JSON endpoint |

Legacy `/admin/login` and `/admin/logout` routes continue to resolve through the shared authentication controller.

## Administrator routes

| Route | Function |
| --- | --- |
| `/admin` | dashboard |
| `/admin/users` | user list |
| `/admin/users/new` | create learner or administrator |
| `/admin/user/{id}` | role, active state, and temporary-password management |
| `/admin/questions` | filtered question list with curation status |
| `/admin/sources` | source-document manifest and protected PDF uploads |
| `/admin/question/{sequence}` | card editor |
| `/admin/import` | bundled or uploaded JSON import |
| `/admin/settings` | public text and defaults |
| `/admin/diagnostics` | System Health and pending migrations |

## Question-bank updates

Regenerate JSON offline:

```bash
python3 scripts/build_question_bank.py path/to/PPL_compilation.pdf data/question-bank.json
python3 scripts/apply_curation.py data/question-bank.json
php scripts/validate_question_bank.php
```

After uploading a new valid JSON bank, use **Admin > Import**. The importer validates the complete payload before starting the transaction. Subject counts and `sequence_no` continuity are treated as invariants.

## Validation before deployment

Run:

```bash
bash scripts/check_syntax.sh
php scripts/validate_question_bank.php
```

For full acceptance testing, follow `TESTING.md` against a disposable MySQL or MariaDB database.

## Architecture

The project uses explicit module ownership:

- root entry points stay thin
- bootstrap owns runtime assembly and routing
- controllers handle HTTP concerns
- services own domain and database logic
- views render HTML
- migrations are explicit PHP files containing SQL statements
- configuration defaults are centralized
- bundled data is a versioned artifact independent of database schema
- documentation describes module ownership and mutation boundaries

See `ARCHITECTURE.md` and `CODEMAP.md` before making structural changes.

## Licensing

Application code is MIT licensed. See `LICENSE`.

The bundled question-bank data has its own provenance and usage notes in `data/README.md`.
