# PPL Study

A framework-free PHP 8.1+ study application for the Czech PPL(A) theoretical syllabus. It provides an invitation-only learning environment with source-exact four-choice study cards, automatic scoring, drills, search, browser-local progress, learner profiles, and administrator management.

**Current version:** 0.1.0

**Deployment target:** ordinary PHP shared hosting with MySQL or MariaDB. The runtime uses native PHP, PDO, JavaScript, and Apache routing.

## First release

Version 0.1.0 is the first public release. Earlier development milestones were commits, not published versions.

The release includes:

- 896 active questions in all nine PPL(A) subjects
- exact source wording and four answer choices in published order
- the source-derived correct answer as the authoritative answer
- curated principle, memory-aid, and operational-context explanations
- project-owned SVG study redraws for attachment-dependent questions
- authenticated learning and quiz modes, custom drills, timed subject papers, search, scoring, stars, and browser-local progress
- invitation-only learner accounts and administrator-managed access
- transactional question-bank import, deterministic migrations, audit logging, and System Health
- shared-hosting installation and deployment tooling

## Access model

The subject dashboard and source-information page are public. Study-card content is available only to active authenticated accounts that have completed permanent-password setup.

Administrators create all accounts. A temporary password authorizes one successful login and then forces permanent-password setup. Administrators can manage roles, activation, display names, and password resets. The application prevents the current administrator from demoting or disabling their own account and preserves at least one active administrator.

## Question and source model

Every question contains four choices and exactly one correct answer. All nine subjects use source-exact active question sets, producing these subject counts:

`97 / 58 / 129 / 87 / 99 / 60 / 101 / 128 / 137`

The integrated bank contains 896 questions. Source-derived correct answers remain authoritative; generated or curated study content is separate and editable project data.

Source-exact JSON lives in `data/source-exact/`, explanation overlays in `data/curation/`, and source metadata in `data/source-manifest.json`. Official source PDFs are not part of the repository or deployment archive. An administrator may associate locally supplied PDFs through **Admin > Zdroje**; protected documents are served only through authenticated application routes.

For flight operations, current regulations, AIP, NOTAM, AFM/POH, and ATS instructions take precedence.

## Requirements

Server:

- PHP 8.1 or newer
- PDO MySQL, JSON, and mbstring extensions
- MySQL 5.7+ or MariaDB 10.4+
- Apache with `mod_rewrite` recommended
- write access to the project root during installation, `cache/`, and `data/source-documents/`

Offline question-bank regeneration additionally requires Python 3.10+ and Poppler `pdftotext`.

## Installation

1. Create an empty MySQL or MariaDB database and user.
2. Upload the release archive to the chosen document root.
3. Open `https://your-subdomain.example/install.php`.
4. Verify the environment and database connection.
5. Create the initial administrator. The installer runs all four migrations, imports the bundled 896-question bank, writes `config.php`, and creates `cache/installed.lock`.
6. Continue to `/admin`, review System Health, and create learner accounts.

`config.php` contains deployment secrets and is generated locally. Never commit or overwrite it during deployment. `reset.php` requires the deployment-specific `setup_key` for emergency administrator recovery.

## Main routes

| Route | Access and function |
| --- | --- |
| `/` | public subject dashboard |
| `/about` | public source and examination information |
| `/login` | learner/administrator login |
| `/account` | authenticated profile |
| `/account/password` | required first-login setup and password change |
| `/study` | authenticated study setup |
| `/question/{sequence}` | authenticated learning or quiz card |
| `/drill` | authenticated custom or timed paper drill |
| `/search` | authenticated question-bank search |
| `/api/quiz` | authenticated first-party drill API |
| `/admin` | administrator dashboard |
| `/admin/users` | account management |
| `/admin/questions` | question list and editor |
| `/admin/sources` | protected source-document association |
| `/admin/import` | bundled or uploaded JSON import |
| `/admin/settings` | application settings |
| `/admin/diagnostics` | System Health and migrations |

## Question-bank maintenance

Regenerate the bank offline; never parse PDFs during installation or normal requests:

```bash
python3 scripts/build_question_bank.py /outside/project/source.pdf data/question-bank.json
python3 scripts/apply_curation.py data/question-bank.json
php scripts/validate_question_bank.php
```

The importer validates the complete payload before starting its transaction. Subject counts and continuous `sequence_no` values are invariants.

## Release packaging

On Windows:

```bat
deploy.bat -Mode local -DeployFolder deploy -MakeZipDeploy true
```

On Unix-like systems:

```bash
bash deploy.sh
```

The package is named `PPL-Study-0.1.0-deploy.zip`. Packaging excludes local credentials, runtime cache, generated deployment output, Git metadata, tooling caches, and source PDFs.

Before release, run:

```bash
bash scripts/check_syntax.sh
php scripts/validate_question_bank.php
```

Then complete the fresh-install and administrator acceptance cases in `TESTING.md` against a disposable MySQL or MariaDB database.

## Architecture and licensing

Root entry points stay thin; bootstrap owns runtime assembly and routing; controllers handle HTTP concerns; services own domain and SQL logic; views render HTML; migrations are append-only; and configuration defaults are centralized.

See `ARCHITECTURE.md`, `CODEMAP.md`, `DATABASE.md`, and `TESTING.md`.

Application code is MIT licensed. The source-derived data has separate provenance and usage notes in `data/README.md`; the project owner is responsible for confirming redistribution rights.
