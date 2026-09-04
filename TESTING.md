# Testing

## 1. Fast release checks

Run from project root:

```bash
bash scripts/check_syntax.sh
php scripts/validate_question_bank.php
```

Expected bundled question-bank validation:

- question-bank schema version 3
- 9 subjects
- 896 questions
- contiguous sequence 1 through 896
- 84 questions referencing an attachment code
- 896 questions with exactly four unique answer choices
- each correct answer appears exactly once in its four choices
- per-subject counts: 97, 58, 129, 87, 99, 60, 101, 128, 137

Application database schema version for 0.1.0 is migration generation 4, ending with `202609040004_question_curation`.

## 2. Installer acceptance test

Use a disposable empty MySQL or MariaDB database.

1. Ensure `config.php` and `cache/installed.lock` are absent.
2. Open `/install.php`.
3. Confirm prerequisite checks are green for PHP, PDO MySQL, JSON, mbstring, write access, and bundled bank.
4. Enter valid database credentials and submit step 1.
5. Confirm invalid database credentials keep the installer on step 1.
6. In step 2 verify passwords shorter than 15 characters are rejected.
7. Complete installation.
8. Confirm step 3 reports 896 imported questions and 9 subjects.
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

Expected question count is 896, min/max are 1/896, and migration `202609040004_question_curation` is present.

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

- `/study` offers **Režim otázek** and **Režim učení** for all subjects and individual subjects.
- random link redirects to a valid `/question/{sequence}?mode=...`.
- four answer choices are visible immediately.
- quiz mode scores the selected choice and then reveals the explanation.
- learning mode keeps the answer hidden until **Zobrazit správnou odpověď**, then allows **Věděl jsem / Chci zopakovat** self-assessment.
- questions 1-97 render structured explanation sections and exact source wording.
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
- imported question count is 896
- no pending migrations remain after fresh installation

## 7. Recovery test

1. Open `/reset.php`.
2. Confirm an invalid setup key leaves credentials unchanged.
3. Use the correct `setup_key` plus a 15+ character password.
4. Confirm the target account is an active Administrator with a permanent password and `must_change_password = 0` when the current schema is present.
5. Sign in through `/login`.
6. Rotate `setup_key` in `config.php` after a real recovery event where the key was exposed.

## 8. Security regression checks

Verify state-changing administrator and account POST requests reject absent or invalid CSRF tokens.

Verify output escaping for usernames and display names.

Verify error pages keep these values server-side:

- DB password
- SQL DSN
- stack trace
- absolute server filesystem path

Verify a signed-out client cannot obtain question prompts, choices, answers, explanations, or drill JSON through application routes.

## 9. PDF regeneration test

With Poppler installed:

```bash
python3 scripts/build_question_bank.py PPL_A_teorie_studijni_kompilat_2026_FINAL.pdf /tmp/question-bank.json
php scripts/validate_question_bank.php /tmp/question-bank.json
```

The generator parses the 880-card compilation, then applies source-exact subject replacements for all nine subjects. The final bank contains 896 active questions.

## System Health metadata-query regression

System Health must work with `PDO::ATTR_EMULATE_PREPARES => false`. Metadata existence checks use parameterized `SELECT` statements against `information_schema.tables` and `information_schema.columns`. Do not use parameter markers in MySQL/MariaDB `SHOW TABLES`, `SHOW COLUMNS`, or similar metadata grammar.

Regression check:

1. Open **Admin > System Health** on MySQL or MariaDB with native PDO prepares enabled.
2. Confirm every expected table is inspected without SQLSTATE 42000 / error 1064.
3. Confirm `User account schema` reports the actual number of profile columns.
4. Confirm `Pending migrations` reports either `none` or the exact pending migration filenames.
5. Run pending migrations if present and reload System Health.



### Source documents

1. Run migration `202609040004_question_curation`.
2. Open **Admin > Zdroje**.
3. Upload a PDF for one manifest entry.
4. Confirm the row shows local availability, file size, and SHA-256 prefix.
5. Open the local PDF link while authenticated and confirm inline PDF output.
6. Sign out and request `/source/{source_key}` directly. Confirm authentication is required.
7. Confirm direct HTTP access to `data/source-documents/<file>.pdf` is denied.
8. Replace the PDF and confirm metadata and served content update.

### Source-exact first subject

- Admin question list shows questions 1-97 as `verified` with `source_exact` wording.
- Subject 1 reports exactly 97 questions.
- Question 6 is exactly `Co znamená zkratka ARC?`.
- Question 6 answer choices are exactly the four strings from the supplied official PDF and remain in source order.
- Question 8 uses the source-marked answer `ve všech státech ICAO`.
- Questions 32-39 are seven independent source questions rather than one merged study card.
- Questions 89-92 retain attachment codes ALW-019, ALW-020, ALW-011, and ALW-012.
- Question 98 is the first question of subject 2 after global renumbering.
- regenerate the bank with `scripts/build_question_bank.py` and confirm `data/source-exact/01-pravni-predpisy.json` restores all 97 exact source questions before curation is applied.


## Human Performance source-exact regression

- `data/source-exact/02-lidska-vykonnost.json` contains exactly source questions 1..58.
- Every source-exact Human Performance question has four unique choices and one checked answer.
- Questions 55 and 56 reference local HPL-002 SVG.
- Integrated bank contains 896 source-exact, verified questions.
- Subject 3 begins at global sequence 156 after restoring subject-2 source question 9 as its own record.
## Meteorology source-exact regression

- `data/source-exact/03-meteorologie.json` contains exactly source questions 1..129.
- Every Meteorology source question has four unique answer choices in published order and exactly one marked correct answer.
- All 129 marked answers were cross-checked against the compilation-derived correct result before replacement.
- Source questions 56, 57, 58, 70, 71, and 72 reference local SVG study assets for MET-001, MET-002, MET-004, and MET-005.
- Integrated bank contains 896 source-exact, verified questions.
- Subject 3 begins at global sequence 156 and subject 4 begins at global sequence 285.
- `php scripts/validate_question_bank.php` must report subject counts `97/58/129/86/98/56/101/128/134`.
- Browser-local progress uses pplStudyProgressV7.



## Communication source-exact regression

- Subject 4 contains exactly 87 source questions, source IDs 1 through 87.
- Every Communication source question has four unique answer choices in published order and exactly one marked correct answer.
- Communication source questions 54 and 55 are separate records. Question 54 answers `vítr`; question 55 answers `informace o provozu`.
- All 87 Communication questions are `source_exact` and `verified` and reference the corresponding source-manifest entry.
- Integrated bank contains 896 source-exact, verified questions.
- Subject 4 spans global sequence 285-371 and subject 5 begins at global sequence 372.
- Browser-local progress uses pplStudyProgressV7.
- `data/source-documents/04-komunikace-official.pdf` SHA-256 is `cda0b7d7698ad0e8423df9e043f6183ed37493367fc4371eb2ee265398a73eab`.
- The fresh installation applies all four migrations.

## Letové zásady source-exact regression

Required checks:

- `data/source-exact/05-letove-zasady.json` contains 99 active source questions with source IDs 1-7 and 9-100.
- Source question 8 is recorded as retired and is absent from the active question list.
- Every active source question contains exactly four unique choices and exactly one marked correct answer.
- Source questions 48 and 70 exist as separate records and both preserve their published wording and option order.
- Subject 5 contains 99 questions and spans global sequence 372-470.
- Subject 6 begins at global sequence 471.
- Integrated bank contains 896 source-exact, verified questions.
- `data/source-documents/05-letove-zasady-official.pdf` SHA-256 is `50839b3a314b8627ddd19035bb3b15b29284c0c1ad7e04cd7aa55ded7ab33967`.
- PFA-003, PFA-008, PFA-009, PFA-010, and PFA-011 local SVG study assets parse as valid XML.
- Browser-local progress uses pplStudyProgressV7.
- The fresh installation applies all four migrations.

## Provozní postupy source-exact regression

Required checks:

- `data/source-exact/06-provozni-postupy.json` contains exactly source questions 1 through 60.
- Every Provozní postupy source question has exactly four unique answer choices in published order and exactly one marked correct answer.
- Source questions 33, 34, 35, 36, and 37 exist as five independent wake-turbulence records.
- Source questions 25 and 26 reference `/public/assets/attachments/OPR-001.svg`.
- Subject 6 contains 60 questions and spans global sequence 471-530.
- Subject 7 begins at global sequence 531.
- Integrated bank contains 896 source-exact, verified questions.
- `data/source-documents/06-provozni-postupy-official.pdf` SHA-256 is `c797b4f5bdde387eeb6778cab4d3dfa76a880ed45263439d92150f268b066f92`.
- OPR-001 local SVG study asset parses as valid XML.
- Browser-local progress uses pplStudyProgressV7.
- The fresh installation applies all four migrations.

## Subject 7 source-exact regression

For the 0.1.0 release candidate:

1. Run `php scripts/validate_question_bank.php data/question-bank.json`.
2. Confirm the integrated bank contains 896 questions and subject 7 contains exactly 101 questions.
3. Confirm subject 7 source question IDs are the contiguous range 1 through 101.
4. Confirm every subject 7 question has four unique choices, exactly one correct choice, `wording_status = source_exact`, and `curation_status = verified`.
5. Spot-check calculation questions 27-30, 52-72, 79-80, and 90-98 against the supplied official PDF and their explanation sections.
6. Open attachment questions 26, 31-36, 48-50, 53, 55-72, 77-78, 81-85, and 99-101 and confirm the local study asset renders inline.
7. Confirm `/source/subject-07-official-questions` is inaccessible to an anonymous visitor and serves the bundled PDF to an authenticated user.
8. In Admin, import the bundled bank and confirm global question 531 is source question 1 and global question 631 is source question 101.
9. Confirm global question 632 remains the first question of Obecné znalosti o letadle.
10. Confirm browser progress uses pplStudyProgressV7.

The fresh installation applies all four migrations.

## Subjects 8 and 9 source-exact regression

For the 0.1.0 release candidate:

1. Run `php scripts/validate_question_bank.php` and confirm 896 total questions, 896 source-exact questions, and 896 verified questions.
2. Confirm subject 8 contains source question IDs 1 through 128 and occupies global sequence 632 through 759.
3. Confirm subject 9 contains source question IDs 1 through 137 and occupies global sequence 760 through 896.
4. Confirm every subject 8 and 9 question contains exactly four unique choices and exactly one correct choice.
5. Confirm all subject 8 and 9 questions have `choices_generated = false`, `wording_status = source_exact`, and `curation_status = verified`.
6. Confirm Navigation source questions 15, 16, and 17 are three independent records.
7. Confirm Navigation source questions 73 and 76 are two independent records.
8. When local source PDFs are associated, confirm they resolve through authenticated routing and their SHA-256 values match `data/source-manifest.json`.
9. Confirm AGK-002, AGK-003, AGK-005, AGK-006, AGK-007, AGK-008, AGK-011, NAV-002, NAV-004, NAV-014, NAV-017, NAV-019, NAV-022, NAV-024, and NAV-031 render inline for their attachment questions.
10. Confirm browser-local progress uses `pplStudyProgressV7`.
11. In Admin, import the bundled bank and confirm the importer reports 896 questions across 9 subjects.

The fresh installation applies all four migrations.

