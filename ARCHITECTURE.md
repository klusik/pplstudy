# Architecture

## 1. Design goals

PPL Study targets ordinary shared hosting while retaining explicit module ownership and a dedicated PPL(A) study domain model.

Primary constraints:

- PHP 8.1+
- no framework
- no Composer dependency at runtime
- MySQL or MariaDB persistence
- deterministic installation and migration
- safe failure behavior without raw exception leakage
- source question data can be replaced independently from application code
- the public dashboard exposes subject and examination metadata
- study-card content requires an administrator-created learner account
- personal progress remains in the learner's browser

## 2. Request lifecycle

Normal request:

1. Apache serves an existing static file directly or rewrites the URL to root `index.php`.
2. Root `index.php` delegates to `public/index.php`.
3. `public/index.php` loads `app/early_runtime.php`, registers the emergency error boundary and enters the shared updater request gate before assembling the application.
4. `app/bootstrap.php` loads runtime modules in a deterministic order.
5. Missing `config.php` redirects to `install.php`.
6. `app/bootstrap/session.php` starts the configured application session.
7. `app/bootstrap/request.php` emits common security headers and resolves a page identifier.
8. `app/bootstrap/dispatch.php` maps that identifier to one controller.
9. The controller validates HTTP input and calls services.
10. Services query or mutate the database.
11. Views render escaped output.

The public quiz API uses the same routing and service layer and returns JSON instead of HTML.

Public URL generation is configuration-led: `config.php` `base_url` defines the canonical origin and public base path. `SCRIPT_NAME` is treated only as a request-normalization hint because shared hosts may expose internal aliases such as `/subdom/ppl`. If such an internal prefix leaks into `REQUEST_URI`, the request is canonicalized back to the configured public URL. Installer detection uses the browser-visible request path.

## 3. Module boundaries

### Bootstrap and core

`app/bootstrap.php`, `app/bootstrap/*`, `app/database.php`, `app/security.php`, `app/migrations.php`, and `app/helpers.php` form the application runtime.

These modules may be used by services, controllers, and views. They must not contain PPL subject-specific content.

### Services

Services own reusable domain behavior:

- question-bank reads and writes
- randomized quiz construction
- import validation and synchronization
- settings
- audit log
- diagnostics
- verified public stable-release discovery, staging and activation

Controllers must not duplicate SQL that belongs to these services.

### Controllers

Controllers own:

- HTTP methods
- GET/POST parsing
- redirects
- CSRF enforcement for mutations
- auth gates
- selecting the view or JSON response

Controllers should stay thin. New cross-route behavior normally belongs in a service.

### Views

Views own HTML only. They receive prepared arrays and use the shared escaping helper. Views must not execute SQL or mutate application state.

### Browser JavaScript

`public/assets/app.js` owns intentionally client-local behavior:

- four-choice interaction and automatic scoring
- local progress
- local starred cards
- drill state, paper answer collection, result review, and timer

Learner progress remains client-local. Account identity controls access while study scoring and stars stay in browser localStorage.

## 4. Data model split

There are two separate versioned concepts:

1. **Database schema**: versioned by files in `database/migrations/` and recorded in `schema_migrations`.
2. **Question bank**: versioned by `data/question-bank.json` with its own `schema_version`, source metadata, subjects, and questions.

Do not infer question-bank compatibility solely from application version. Validate the bank payload through `question_bank_validate_payload()`.

## 5. Question-bank invariants

The current bundled bank is represented by these hard invariants:

- 9 subjects
- 896 current questions
- sequence numbers form the exact contiguous range 1..896
- subjects 1 through 6 contain all 97, 58, 129, 87, 99, and 60 active published source questions with exact wording and answer order; Letové zásady source question 8 is recorded as retired
- each question belongs to one declared subject
- each subject count equals `expected_cards`
- every card has `source_trace`, `prompt`, `answer`, `choices`, and `explanation`
- every card has exactly four unique choices
- the source-derived `answer` appears exactly once among the four choices
- source question IDs are stored as JSON to permit merged cards
- attachment references are metadata; local image attachments can also render inline in question and drill views
- curation state and wording fidelity are explicit per-question fields
- structured explanation sections are stored as JSON
- source-document identity and source PDF page are tracked independently from the compilation page

Source-exact subjects preserve the published answer order. Subjects awaiting exact-source processing use explicit generated study choices until their source replacement is added.

## 6. Authentication and mutation security

Authentication uses one shared session model for learners and administrators.

- the installer creates the initial administrator with a permanent password
- every additional account is created by an administrator
- roles are `user` and `admin`
- newly created accounts receive an administrator-issued temporary password
- a temporary password authorizes exactly one successful login
- that successful login marks the temporary credential as consumed
- the authenticated session is forced through `/account/password` before study content becomes available
- permanent passwords use PHP `password_hash()` / `password_verify()`
- successful login regenerates the session ID
- account active state and role are re-read from the database during authenticated requests
- deactivated accounts lose access on their next request
- administrator POST mutations require CSRF tokens
- logout is POST-only
- login failures are recorded in the legacy `admin_login_attempts` throttle table
- `reset.php` is outside normal authentication and requires the random `setup_key` from `config.php`

The authentication layer tolerates the legacy administrator schema while migration 202609030003_user_accounts is pending, allowing System Health to complete the schema.

## 7. Import transaction boundary

Question-bank import follows this order:

1. load JSON
2. validate complete structure and counts
3. begin transaction
4. upsert subjects
5. upsert questions
6. optionally prune rows absent from the import
7. write bank metadata settings
8. commit

An exception rolls back the transaction if the importer started it.

This allows a later compilation update without partially replacing the live bank.

## 8. Migrations

Each migration file returns a non-empty array of SQL strings. `app/migrations.php` discovers files in filename order, runs unapplied migrations transactionally where supported, and records the migration filename.

Rules:

- never edit a released migration to change existing installations
- add a new migration file for schema evolution
- keep data migration behavior deterministic
- System Health may run pending migrations from the admin UI

## 9. Error handling

`app/early_runtime.php` is loaded before the main application and owns last-resort exceptions and fatal errors.

Public behavior:

- detailed exception messages stay in the server log
- browser responses are bounded and generic
- JSON requests receive JSON error shapes
- production pages do not print database credentials, SQL, stack traces, or filesystem paths

## 10. Configuration ownership

`config.php` contains deployment secrets and connectivity only. Editable product text lives in `app_settings`. Runtime tunables live in `app/configuration_defaults.php` and can be overridden through `config.php['runtime_limits']`.

Avoid scattering numeric security or input limits through controllers.

## 11. Future extension points

Likely additions can fit without architectural rewrite:

- synchronized server-side learner progress
- spaced repetition scheduling
- tagged weak-topic collections
- version-diff tooling between source compilations
- generated attachment previews if redistribution rights and source handling are resolved
- import preview/diff before commit
- generated-choice review queues and quality scoring
- multi-language study card content

These remain future extension points.


## 11. Sequential curation

Question curation proceeds by `sequence_no` from 1 upward. Two independent fields keep progress unambiguous:

- `curation_status`: `draft`, `explained`, `verified`
- `wording_status`: `study_wording`, `source_exact`

`verified` requires `source_exact`. This prevents a rich explanation from being mistaken for source-fidelity verification.

Hand-curated content lives in `data/curation/*.json`. Active overlays are explicitly ordered by `data/curation/manifest.json`. `scripts/build_question_bank.py` regenerates the base bank from the supplied compilation, applies source-exact subject replacements, then applies the curation overlays. All nine subjects are currently source-exact. This keeps question-generation logic separate from manual curation.

## 12. Source documents

`data/source-manifest.json` identifies source PDFs by stable `source_key`. Administrator-supplied PDFs are stored in `data/source-documents/`, a path denied to direct HTTP access. The application serves a local PDF only through `/source/{source_key}` after user authentication.

Production never downloads a source PDF automatically. Source PDFs stay outside version control and release packages; administrators can associate locally supplied documents with manifest entries through the protected workflow.

## 13. Subject 7 exact-source layer

`data/source-exact/07-provedeni-planovani.json` replaces the compact subject-7 compilation cards with all 101 published source questions from the supplied official PDF. `data/curation/07-provedeni-planovani.json` adds the project explanation layer by stable source-question ID.

The replacement preserves the subject cardinality at 101 questions. Attachment-dependent questions reference local study redraws under public/assets/attachments/; an optional locally supplied PDF can be associated through the protected source-document workflow.

## 14. Subjects 8 and 9 exact-source completion

`data/source-exact/08-obecne-znalosti.json` contains all 128 published **Obecné znalosti o letadle** questions. `data/source-exact/09-navigace.json` contains all 137 published **Navigace** questions.

The Navigation replacement expands two compact compilation records back to their original source granularity:

- source questions 15, 16, and 17 are three separate time/longitude questions;
- source questions 73 and 76 are separate numeric and verbal wind-correction questions.

After the subject 9 replacement, the integrated bank contains 896 active questions and exactly matches the active-source cardinality recorded by the compilation. Every active question has `wording_status = source_exact` and `curation_status = verified`.

Source PDFs remain outside the repository and release package. Attachment-dependent questions use local project-owned study redraws under public/assets/attachments/.

Client-local learning progress uses the pplStudyProgressV7 namespace in the first release.


## Administrator updater boundary

`app/update_gate.php` owns the early shared request lock, exclusive activation and interrupted-file rollback. `app/services/updater.php` owns the public GitHub release/asset contract, strict managed paths, archive and file verification, staging, backup and publication. The admin controller owns authorization and CSRF; the view renders explicit actions only. Network staging is separate from the exclusive activation period. Schema-changing packages and changed migration inventories are rejected; the existing database and local source documents remain independent. Keep the early gate protocol backward-compatible between releases.

## Theory courses

Theory is version-controlled, structured, original teaching content, independent from the imported question-bank database. The registry maps a registered course to a subject ID and fixed chapter files. Routes accept known slugs only; the controller requires a completed learner login and renders an active subject. Views escape all chapter text and reference local illustration assets. Chapter source IDs describe thematic coverage, not global card sequence numbers or answer keys. New courses can follow this structure without schema changes. The 0.2.0 managed-package path rules already admit these PHP and SVG files.
