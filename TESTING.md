# Testing

## 1. Fast release checks

Run from project root:

```bash
bash scripts/check_syntax.sh
php scripts/validate_question_bank.php
```

Expected bundled question-bank validation:

- question-bank schema version 2
- 9 subjects
- 880 questions
- contiguous sequence 1 through 880
- 73 cards referencing an attachment code
- 880 cards with exactly four unique study choices
- each correct answer appears exactly once in its four choices
- per-subject counts: 91, 57, 129, 86, 98, 56, 101, 128, 134

Application database schema version for 0.3.1 is migration generation 3, ending with `202609030003_user_accounts`.

## 2. Installer acceptance test

Use a disposable empty MySQL or MariaDB database.

1. Ensure `config.php` and `cache/installed.lock` are absent.
2. Open `/install.php`.
3. Confirm prerequisite checks are green for PHP, PDO MySQL, JSON, mbstring, write access, and bundled bank.
4. Enter valid database credentials and submit step 1.
5. Confirm invalid database credentials keep the installer on step 1.
6. In step 2 verify passwords shorter than 15 characters are rejected.
7. Complete installation.
8. Confirm step 3 reports 880 imported cards and 9 subjects.
9. Confirm the created administrator has role `admin`, `active = 1`, and `must_change_password = 0`.
10. Reload `/install.php` and confirm the installed-state screen appears.
11. Confirm `config.php` is protected from direct HTTP access.

Database verification:

```sql
SELECT COUNT(*) FROM subjects;
SELECT COUNT(*) FROM questions;
SELECT MIN(sequence_no), MAX(sequence_no) FROM questions;
SELECT subject_id, COUNT(*) FROM questions GROUP BY subject_id ORDER BY subject_id;
SELECT version FROM schema_migrations ORDER BY version;
SELECT username, role, active, must_change_password FROM users;
```

Expected question count is 880, min/max are 1/880, and migration `202609030003_user_accounts` is present.

## 3. Public access boundary

Use a clean browser session with no authenticated cookie.

### Dashboard

- `/` shows all 9 subject cards, counts, paper metadata, and the login action.
- individual question prompts and answer choices are absent from the dashboard HTML.
- subject cards contain no direct question/drill action links for a signed-out visitor.
- `/about` remains readable.

### Protected study routes

Request each route while signed out:

- `/study`
- `/question/1`
- `/drill`
- `/search?q=QNH`

Each HTML route redirects to `/login` before question content is loaded/rendered.

Request `/api/quiz?mode=custom&subject=all&count=5` while signed out. Expected result is HTTP 401 JSON with a login target and no drill/question payload.

Verify direct HTTP requests continue to protect `data/question-bank.json`, application PHP modules, database migrations, scripts, cache, config, and documentation.

## 4. Invitation-only account lifecycle

### Create learner

1. Sign in as the installer administrator.
2. Open **Admin > Uživatelé > Přidat uživatele**.
3. Create a role `user` account with a 15+ character temporary password.
4. Verify database values:
   - `role = 'user'`
   - `active = 1`
   - `must_change_password = 1`
   - `temporary_password_consumed_at IS NULL`
5. Confirm the site exposes no public registration route or form.

### First login

1. Sign out from Admin and sign in as the learner using the temporary password.
2. Confirm the login succeeds exactly once and redirects to `/account/password`.
3. Verify `temporary_password_consumed_at` is populated immediately after that successful login.
4. In a second clean browser, attempt the same temporary password again. Confirm login is rejected.
5. In the original authenticated session, attempt `/question/1` before setting a permanent password. Confirm redirect to `/account/password`.
6. Set a permanent password of 15+ characters and choose a value different from the temporary password.
7. Confirm `must_change_password = 0`, `temporary_password_consumed_at IS NULL`, and `password_changed_at` is populated.
8. Sign out and confirm the permanent password works.

### Interrupted first login

1. Create or reset a learner to a new temporary password.
2. Use the temporary password successfully once.
3. End that browser session before permanent-password setup.
4. Confirm the consumed temporary password cannot start another session.
5. As administrator, assign a fresh temporary password.
6. Confirm the new temporary password provides one new successful login.

### Account state and roles

- promote a learner to Administrator and confirm Admin routes become available after account state refresh
- demote another administrator to User while another active administrator remains
- deactivate a learner and confirm an existing session loses access on its next request
- reactivate the learner and confirm login works again
- attempt to deactivate/demote the currently acting administrator and confirm the operation is rejected
- attempt to remove the last active administrator role and confirm the operation is rejected

## 5. Authenticated study smoke test

### Single-card study

- `/study` offers all subjects and whole-bank random mode.
- random link redirects to a valid `/question/{sequence}`.
- four answer choices are visible immediately.
- selecting a choice marks the result and reveals the explanation.
- automatic score persists after reload through localStorage.
- star state persists after reload.
- previous/next stays inside the same subject.

### Search

Test:

- `7600`
- `QNH`
- exact sequence `1`
- attachment code `ALW-019`
- Czech diacritics

Confirm results link to real cards and output is escaped.

### Drill

Custom:

- all subjects, 20 cards
- one subject, custom size 5
- size above 120 is clamped

Paper:

- subject 1 returns 12 cards with 20-minute timer
- subject 7 returns 16 cards with 35-minute timer
- all-subject paper selection is rejected client-side and API-side
- custom drill gives immediate feedback and explanation
- paper drill delays correctness feedback until result display
- score uses total question count
- timer expiry counts unanswered cards
- wrong answers appear in result review

### Session expiry

Expire or clear the authenticated session while the drill page is open, then request a new quiz payload. Confirm the API returns authenticated-access JSON rather than question content.

## 6. Administrator test

### Authentication

- wrong password fails generically
- repeated failures trigger the shared IP throttle
- correct login regenerates the session ID
- logout requires POST and CSRF
- legacy `/admin/login` resolves through the shared login controller

### Users

- user list displays role, active state, password state, and last login
- new account requires matching temporary passwords
- new account supports User and Administrator roles
- profile display name is editable
- password reset produces a fresh one-time-login state
- user management mutations produce administrator audit events

### Questions

- filter by subject
- search by text
- edit one card and verify authenticated study page changes
- edit all four choices and confirm the correct answer is required exactly once
- save a generated set and confirm `choices_generated` becomes false
- disable a card and verify it disappears from learner study while remaining in Admin
- re-import bundled bank and confirm source-derived values are restored

### Import

- valid bundled import succeeds
- malformed JSON is rejected before database mutation
- wrong question-bank `schema_version` is rejected
- a bank question with fewer than four choices is rejected
- duplicate choices are rejected
- choices omitting the correct answer are rejected
- missing sequence is rejected
- changed expected subject count is rejected
- oversized uploaded import is rejected

### Settings

- change site name and public intro
- confirm output is escaped
- default drill size stays in range

### System Health

- all required runtime checks display
- all expected tables display as present
- user account schema reports all profile columns present
- at least one active administrator is reported
- bundled bank validates
- imported question count is 880
- no pending migrations remain after fresh installation

## 7. Upgrade test from 0.2.0

Start with a real 0.2.0 schema and administrator.

1. Upload 0.3.0 files while preserving `config.php` and `cache/`.
2. Confirm the existing administrator can still authenticate using the legacy schema compatibility path.
3. Open **Admin > System Health**.
4. Run `202609030003_user_accounts`.
5. Reload System Health and confirm the profile columns are present.
6. Confirm the pre-existing administrator remains `admin`, active, and uses the existing permanent password.
7. Create a learner and execute the full one-time-password flow.

## 8. Recovery test

1. Open `/reset.php`.
2. Confirm an invalid setup key leaves credentials unchanged.
3. Use the correct `setup_key` plus a 15+ character password.
4. Confirm the target account is an active Administrator with a permanent password and `must_change_password = 0` when the 0.3.0 schema is present.
5. Sign in through `/login`.
6. Rotate `setup_key` in `config.php` after a real recovery event where the key was exposed.

## 9. Security regression checks

Verify state-changing administrator and account POST requests reject absent or invalid CSRF tokens.

Verify output escaping for usernames and display names.

Verify error pages keep these values server-side:

- DB password
- SQL DSN
- stack trace
- absolute server filesystem path

Verify a signed-out client cannot obtain question prompts, choices, answers, explanations, or drill JSON through application routes.

## 10. PDF regeneration test

With Poppler installed:

```bash
python3 scripts/build_question_bank.py PPL_A_teorie_studijni_kompilat_2026_FINAL.pdf /tmp/question-bank.json
php scripts/validate_question_bank.php /tmp/question-bank.json
```

The generator enforces the known 880-card structure and enriches every card with four deterministic study choices.

## System Health metadata-query regression

System Health must work with `PDO::ATTR_EMULATE_PREPARES => false`. Metadata existence checks use parameterized `SELECT` statements against `information_schema.tables` and `information_schema.columns`. Do not use parameter markers in MySQL/MariaDB `SHOW TABLES`, `SHOW COLUMNS`, or similar metadata grammar.

Regression check:

1. Open **Admin > System Health** on MySQL or MariaDB with native PDO prepares enabled.
2. Confirm every expected table is inspected without SQLSTATE 42000 / error 1064.
3. Confirm `User account schema` reports the actual number of profile columns.
4. Confirm `Pending migrations` reports either `none` or the exact pending migration filenames.
5. Run pending migrations if present and reload System Health.

