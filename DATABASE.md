# Database

## Engine and encoding

PPL Study targets MySQL or MariaDB through PDO. Tables use InnoDB and `utf8mb4_unicode_ci`.

Schema history:

- `202609030001_initial_schema.php` creates the original application tables
- `202609030002_question_choices.php` adds four-choice study data
- `202609030003_user_accounts.php` expands authentication into invitation-only learner/admin profiles

## Migration ledger

`app/migrations.php` creates and maintains `schema_migrations`.

- `version` VARCHAR primary key
- `applied_at` DATETIME

Migration filenames are the stable identifiers.

## Application tables

### `users`

Shared learner and administrator account table.

Important fields:

- `username` unique login name
- `display_name` user-facing name
- `password_hash` PHP password hash
- `role` with values `user` or `admin`
- `must_change_password` marks administrator-issued temporary credentials
- `temporary_password_consumed_at` records the single successful login allowed by a temporary password
- `active` controls account access
- `created_by_user_id` references the administrator who created the account
- `last_login_at`
- `password_changed_at`
- timestamps

The installer creates the first administrator with `must_change_password = 0`. Accounts created later through Admin use `must_change_password = 1` and `temporary_password_consumed_at = NULL`.

On successful temporary-password login, authentication sets `temporary_password_consumed_at`. A second login with the same temporary password is rejected. The already-authenticated session can set the permanent password. Permanent-password setup clears `must_change_password` and the temporary-consumed marker.

### `subjects`

One row per PPL(A) examination subject.

Important fields:

- numeric stable `id`
- route `slug`
- display `name`
- source description/version
- `exam_question_count`
- `exam_time_minutes`
- `expected_cards`
- official PDF URL
- optional official attachments ZIP URL
- sort and active state

### `questions`

One row per study card.

Important fields:

- `sequence_no` unique, matching the compilation sequence
- `subject_id` foreign key
- optional `title`
- `source_trace`
- `source_question_ids_json`
- `merged` flag
- `prompt`
- `answer`
- `choices_json` containing four editable answer choices
- `choices_generated`
- `explanation`
- optional `warning`
- optional attachment code/URL
- `attachment_indexed`
- source compilation page
- active state

The correct answer remains the source-derived `answer` field, and the importer requires it to appear exactly once in `choices_json`.

### `app_settings`

Small key/value configuration editable in Admin:

- site name
- subtitle
- home introduction
- source disclaimer
- default drill size
- imported bank metadata

Deployment secrets live in `config.php`.

### `admin_logs`

Audit events for administrator mutations. `user_id` references the acting account and becomes NULL if the account row is ever removed.

### `admin_login_attempts`

Login-throttle ledger retained under its original table name for upgrade compatibility. It records attempts for both learner and administrator accounts.

## Referential behavior

- `questions.subject_id` uses `ON DELETE RESTRICT`
- `admin_logs.user_id` uses `ON DELETE SET NULL`
- `users.created_by_user_id` uses `ON DELETE SET NULL`

## Account administration invariants

- account creation occurs only through authenticated administrator routes or the installer/recovery utility
- the current administrator cannot deactivate or demote their own account
- at least one active administrator remains available
- temporary password reset sets `must_change_password = 1` and clears `temporary_password_consumed_at`
- deactivated sessions are rejected when `current_user()` refreshes account state

## Question-bank synchronization

The JSON importer uses application-level upsert semantics. When `pruneMissing=true`, questions and subjects absent from the validated import are deleted inside the same transaction boundary used by the importer.

Question `sequence_no` is the stable identity for imported cards. Source compilation renumbering should be reviewed as a significant bank update before production import.

## Backup before source-bank replacement

For a manually curated production bank, take a database backup before importing a regenerated bank with pruning enabled. The bundled bank is reproducible from JSON, while administrator edits to question rows can be overwritten by a subsequent import.
