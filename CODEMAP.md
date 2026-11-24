# Code Map

## Root entry points

| File | Ownership |
| --- | --- |
| `index.php` | thin root front-controller bridge |
| `install.php` | three-screen first-install workflow |
| `reset.php` | setup-key protected admin recovery |
| `config.example.php` | documented config shape |
| `.htaccess` | rewrite and direct-access protection |

## Runtime

| File | Ownership |
| --- | --- |
| `app/early_runtime.php` | last-resort exception and fatal boundary |
| `app/update_gate.php` | shared request gate, exclusive activation and rollback |
| `app/bootstrap.php` | application assembly and version constants |
| `app/bootstrap/configuration.php` | config loading, base URL/path helpers |
| `app/bootstrap/session.php` | session cookie/runtime setup |
| `app/bootstrap/request.php` | request initialization and headers |
| `app/bootstrap/routing.php` | URL-to-page resolution |
| `app/bootstrap/dispatch.php` | explicit page-to-controller mapping |
| `app/configuration_defaults.php` | centralized runtime limits |
| `app/helpers.php` | escaping, JSON, normalization helpers |
| `app/database.php` | PDO construction and request-local handle |
| `app/security.php` | learner authentication, admin authorization, first-login enforcement, CSRF, login throttling |
| `app/migrations.php` | migration discovery and execution |

## Services

| File | Ownership |
| --- | --- |
| `app/services/question_bank.php` | subjects, questions, search, admin editing, curation state |
| `app/services/source_documents.php` | source manifest, protected PDF storage, upload metadata |
| `app/services/quiz.php` | random custom drills and paper drills |
| `app/services/importer.php` | question-bank load, validation, import |
| `app/services/app_settings.php` | editable application settings |
| `app/services/admin_logs.php` | bounded admin audit events |
| `app/services/users.php` | learner/admin account creation, roles, activation, password lifecycle |
| `app/services/diagnostics.php` | read-only System Health model |
| `app/services/updater.php` | public GitHub release validation, managed staging and activation |

`app/services.php` only loads service modules.

## Controllers

Public:

- `app/controllers/public_home.php`
- `app/controllers/public_study.php`
- `app/controllers/public_drill.php`
- `app/controllers/public_search.php`
- `app/controllers/public_about.php`
- `app/controllers/api_quiz.php`

Admin:

- `app/controllers/admin_auth.php`
- `app/controllers/account.php`
- `app/controllers/admin_dashboard.php`
- `app/controllers/admin_users.php`
- `app/controllers/admin_questions.php`
- `app/controllers/admin_sources.php`
- `app/controllers/admin_import.php`
- `app/controllers/admin_settings.php`
- `app/controllers/admin_diagnostics.php`
- `app/controllers/admin_updates.php`

`app/controllers.php` only loads controller modules.

## Views

| File | Ownership |
| --- | --- |
| `app/views/layout.php` | public header, navigation, footer, title blocks |
| `app/views/public.php` | public dashboard, cards, drill, search, source page |
| `app/views/account.php` | login, learner profile, permanent-password setup |
| `app/views/admin.php` | authenticated administrator pages and user management |

`app/views.php` only loads view modules.

## Data and database

| Path | Ownership |
| --- | --- |
| `data/question-bank.json` | versioned study bank distributed with release |
| `data/curation/*.json` | hand-curated question overlays |
| `data/curation/manifest.json` | explicit active-overlay order; legacy overlay files can remain without being applied |
| `data/source-exact/` | exact source-question replacements keyed by subject and official source question |
| `public/assets/attachments/` | locally bundled study diagrams used by attachment questions, including HPL, MET, PFA, OPR, PFP, and CAP697 study redraws |
| `data/source-manifest.json` | stable source-document metadata |
| `data/source-documents/` | protected administrator-associated source PDFs; PDF files are not versioned or released |
| `data/README.md` | provenance and data licensing boundary |
| `database/migrations/*.php` | deterministic schema changes |

## Browser assets

| File | Ownership |
| --- | --- |
| `public/assets/app.css` | complete responsive visual system |
| `public/assets/app.js` | local progress, cards, drill state machine |
| `public/index.php` | public runtime entry point |

## Development scripts

| File | Ownership |
| --- | --- |
| `scripts/build_question_bank.py` | offline compilation parser followed by source-exact subject replacement and curation |
| `scripts/generate_study_choices.py` | deterministic four-choice study distractor generator |
| `scripts/apply_curation.py` | source-exact subject replacement, global renumbering, and stable source-keyed curation |
| `scripts/validate_question_bank.php` | bank invariants without database |
| `scripts/check_syntax.sh` | PHP syntax and JSON validation |
| `deploy.bat` / `scripts/deploy.ps1` | Windows local/FTP deployment workflow with path-safe ZIP packaging |
| `deploy.sh` | Unix release ZIP excluding local secrets |

## Change-location guide

- New public page: controller + view + route + dispatch entry.
- New admin mutation: controller with auth/CSRF + service function + audit event.
- New learner-account behavior: `app/security.php` + `app/services/users.php` + account/admin controller/view ownership.
- New database column/table: new migration, then service code, diagnostics if operationally important.
- New runtime limit: `app/configuration_defaults.php`, optionally installer/config docs.
- New question source compilation: regenerate JSON offline, validate, inspect diff, import.
- New client-local learning behavior: `public/assets/app.js`, no PHP persistence unless intentionally changing the product boundary.

## Exact-source data ownership

- `data/source-exact/01-*.json` through `09-*.json`: exact active source-question sets for all nine PPL(A) subjects.
- `data/curation/01-*.json` through `09-*.json`: source-keyed explanation overlays for the same source questions.
- `data/source-documents/*-official.pdf`: authenticated local copies of the directly supplied official subject PDFs.
- `data/source-manifest.json`: stable source keys, official URLs, version labels, local filenames, and SHA-256 hashes.
- `public/assets/attachments/*.svg`: local project-owned study redraws used by source attachment questions.
- `scripts/apply_curation.py`: exact-subject replacement, restoration of merged source questions, global renumbering, and final curation overlay application.

## Shared-hosting URL ownership

- `app/bootstrap/configuration.php`: configured base URL, browser-visible request path, internal `SCRIPT_NAME` directory normalization, installer redirect target.
- `app/bootstrap/routing.php`: application-relative route parsing and shared-hosting internal-path normalization.
- `app/bootstrap/request.php`: canonical redirect when a hosting-internal path prefix leaks into the browser URL.
- `app/security.php`: sanitizes saved post-login return targets so historical internal hosting prefixes cannot survive authentication redirects.
- `install.php`: request-visible base URL detection and installed-state canonicalization using configured `base_url`.

Updater presentation: `app/views/admin_updates.php`. Release build and regression tooling: `scripts/build_update_package.py`, `scripts/test_updater.php`, `.github/workflows/release.yml`.
