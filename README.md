# PPL Study

A framework-free PHP 8.1+ study application for the Czech PPL(A) theoretical syllabus. It provides an invitation-only learning environment with source-exact four-choice study cards, automatic scoring, drills, search, browser-local progress, learner profiles, and administrator management.

**Current version:** 0.3.2

**Deployment target:** ordinary PHP shared hosting with MySQL or MariaDB. The runtime uses native PHP, PDO, JavaScript, and Apache routing.

## First release

Version 0.1.0 was the first application release. Version 0.1.1 hardens public URL handling on shared hosting. Version 0.2.0 adds administrator-triggered verified updates from the public GitHub repository. Version 0.3.0 adds the first comprehensive Czech theory textbook: aviation meteorology. Version 0.3.1 expands the illustrations and memory aids, clarifies dependencies between concepts and supplies metric and foot equivalents. Version 0.3.2 adds 57 interactive meteorology laboratories with native controls and live SVG diagrams.

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

## Theory textbooks

Select **Studium > Meteorologie > Učebnice · výklad**. The meteorology course has 14 chapters, 62 illustrated sections, 61 original illustrations, 58 memory aids, a 26-item quick reference, a glossary, chapter prerequisites, chapter-level bibliography, step-by-step worked examples and self-check questions. Heights and gradients are taught in metres and feet; hPa remains primary and operational codes remain unchanged. Coverage is mapped to all 129 source question IDs; the course does not claim to cover unpublished examination questions. The original textbook content is versioned in `app/theory/meteorology/`, registered by `app/services/theory.php`, and served by an authenticated, read-only controller. It does not modify the database or import question content. All 14 chapters now include 57 section-local interactive laboratories; these run in the browser without dependencies, external requests, or changes to the question bank. Models are educational, not operational flight tools. Existing 0.2.0 or newer installations can install 0.3.2 via the web updater.

Consult the [ÚCL question bank terms](https://www.caa.gov.cz/zkusebni-otazky-pro-zkousky-teoretickych-znalosti-ppl-lapl/) before using the source questions for training or creating study materials; the page requires informing the copyright holder and written permission for commercial use. The project-owned explanatory text and diagrams are separate from the existing question bank.

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


### Shared-hosting URL handling

`config.php` `base_url` is authoritative for generated application URLs and canonical redirects. The runtime deliberately ignores hosting-internal filesystem/web aliases such as WEDOS `/subdom/...` prefixes when they are not part of the configured public URL. Installer URL detection uses the browser-visible `REQUEST_URI`, not `SCRIPT_NAME`, because shared hosts may rewrite `SCRIPT_NAME` to an internal deployment path.

## Main routes

| Route | Access and function |
| --- | --- |
| `/` | public subject dashboard |
| `/about` | public source and examination information |
| `/login` | learner/administrator login |
| `/account` | authenticated profile |
| `/account/password` | required first-login setup and password change |
| `/study` | authenticated study setup |
| `/study/theory/meteorologie` | authenticated meteorology textbook and chapter navigation |
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
| `/admin/updates` | administrator-only stable release checks and forced reinstall |

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

The legacy local package is named `PPL-Study-0.3.2-deploy.zip`. Packaging excludes local credentials, runtime cache, generated deployment output, Git metadata, tooling caches, and source PDFs.

Before release, run:

```bash
bash scripts/check_syntax.sh
php scripts/validate_question_bank.php
```

Then complete the fresh-install and administrator acceptance cases in `TESTING.md` against a disposable MySQL or MariaDB database.

## Web updater (0.2.0 and newer)

The administrator navigation includes **Aktualizace** at `/admin/updates`.
Guests and learners cannot invoke it. No GitHub account, personal access token,
SSH, cron, Composer or shell execution is needed on the hosting server.
The public repository is fixed to `klusik/pplstudy`.

### Initial deployment

An existing 0.1.x installation must receive the complete
`PPL-Study-0.2.0-deploy.zip` asset from GitHub Releases once, by FTP or the
hosting file manager. Back up the site and database first. Extract and overlay
the package without deleting the existing installation, `config.php`,
`cache/`, uploaded source PDFs or the database. Do not rerun the installer.
Generation 4 remains unchanged. PHP needs cURL, ZipArchive, verified outbound
HTTPS, and permission to write cache and replace the managed application files.
Confirm **Admin > Aktualizace** and the displayed version after deployment.

### Normal use

1. Sign in as an administrator with permanent password setup complete.
2. Open **Aktualizace** and select **Zkontrolovat aktualizace**.
3. Select **Nainstalovat novou verzi**, or **Vynutit aktualizaci / opravit soubory**
   to revalidate the latest stable release even when the installed version matches.
4. The POST action rechecks the release ID, version and SHA-256. It downloads
   the canonical public release asset, checks the GitHub-provided byte count
   and digest, and checks each staged file against the package manifest.
5. Downloads and staging do not block other normal application requests.
   A short exclusive maintenance period (HTTP 503 for competing requests)
   protects backup and activation. If another request is still using the
   application at activation, retry the operation rather than forcing it.
6. The updater records the outcome in the administrator audit log and redirects
   to the new runtime. Identical files do not need to be rewritten, including
   in forced mode; altered/missing managed files are repaired.

The 0.2.0 updater only overlays the verified managed inventory. It does not
delete unlisted files, allow downgrade, install arbitrary commits, or
automatically run on visits. It refuses a different application schema or
added, removed or altered migration files. Schema-changing releases still
require a backed-up manual deployment followed by the existing migration
runner; this avoids unsafe code rollback after nontransactional MySQL DDL.
The bundled JSON may be refreshed, but no live question-bank import is run.
Existing users, administrator edits, database settings, local configuration,
uploaded source PDFs and browser progress are not replaced.

### Recovery

Before publishing any file the updater stores verified originals under
`cache/ppl-update/backup/` and writes `cache/ppl-update/activation.json`.
Each replacement uses a verified sibling temporary file and atomic rename.
An interrupted activation is rolled back under the exclusive request gate
on a subsequent dynamic request. Reload after the maintenance response.
The last successful backup is retained until the next update attempt.
If recovery cannot verify original bytes, it deliberately fails closed.
Using FTP, stop update attempts and restore the matching originals from the
backup inventory (remove only files marked originally absent), or restore
a known complete release from a separate backup. Check the restored bytes
and PHP runtime before removing the activation journal. Do not remove the
journal simply to bypass maintenance mode.

### Publishing a release

GitHub Actions builds and tests a managed distribution package on feature,
develop, release and main branches. A successful push to main publishes
`v_<major>.<minor>.<patch>` with its `PPL-Study-<version>-deploy.zip` asset
using the workflow's repository token. Existing published releases are not
overwritten. The server does not need that token. For a local clean checkout:

```bash
python3 scripts/build_update_package.py dist/PPL-Study-0.3.2-deploy.zip
php scripts/test_updater.php dist/PPL-Study-0.3.2-deploy.zip
```

Use the published distribution ZIP, not the automatic GitHub source archive,
for the first deployment and web updates.

## Architecture and licensing

Root entry points stay thin; bootstrap owns runtime assembly and routing; controllers handle HTTP concerns; services own domain and SQL logic; views render HTML; migrations are append-only; and configuration defaults are centralized.

See `ARCHITECTURE.md`, `CODEMAP.md`, `DATABASE.md`, and `TESTING.md`.

Application code is MIT licensed. The source-derived data has separate provenance and usage notes in `data/README.md`; the project owner is responsible for confirming redistribution rights.
