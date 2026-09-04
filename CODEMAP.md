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
| `app/services/question_bank.php` | subjects, questions, search, admin editing |
| `app/services/quiz.php` | random custom drills and paper drills |
| `app/services/importer.php` | question-bank load, validation, import |
| `app/services/app_settings.php` | editable application settings |
| `app/services/admin_logs.php` | bounded admin audit events |
| `app/services/users.php` | learner/admin account creation, roles, activation, password lifecycle |
| `app/services/diagnostics.php` | read-only System Health model |

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
- `app/controllers/admin_import.php`
- `app/controllers/admin_settings.php`
- `app/controllers/admin_diagnostics.php`

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
| `data/question-bank.json` | versioned source-derived bank distributed with release |
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
| `scripts/build_question_bank.py` | offline compilation PDF parser plus generated study-choice enrichment |
| `scripts/generate_study_choices.py` | deterministic four-choice study distractor generator |
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
