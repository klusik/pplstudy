## 0.12.0 - 2026-09-04

### Added

- source-exact data for all 128 **Obecné znalosti o letadle** questions from the directly supplied official PDF
- source-exact data for all 137 **Navigace** questions from the directly supplied official PDF
- curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** sections for all 265 newly processed questions
- protected bundled source PDFs for subjects 8 and 9 with SHA-256 manifest metadata
- local study redraws for the AGK and NAV attachment codes used by the two subjects
- full-bank source-exact state: all 896 active questions are now verified against their supplied subject PDF

### Changed

- restored Navigation source questions 15, 16, and 17 from one compact compilation card into three independent questions
- restored Navigation source questions 73 and 76 from one compact compilation card into two independent questions
- final integrated question count is now 896, matching the active public-source count recorded by the compilation
- `scripts/apply_curation.py` now reports a dedicated all-subjects source-exact choice model when every active question is exact
- browser-local progress key moves to `pplStudyProgressV7` because Navigation sequence mapping changes

### Upgrade

No database migration is included. Upload the affected files, then open **Admin > Import** and import the bundled bank.

## 0.11.0 - 2026-09-04

- Rebuilt **Provedení a plánování letu** directly from the supplied official ÚCL PDF, version 1.2 dated 30.12.2021.
- Added all 101 published source questions with exact question wording, exact four answer strings, source answer order, and the check-marked correct answer.
- Added tailored curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations for all 101 questions.
- Bundled the supplied official source PDF behind the authenticated source-document route and recorded SHA-256 `3ee4983cf47c4e523f210f2f25fefe24498cf937b7fb4975170c9622de410328`.
- Added local study redraws for PFP-003, PFP-004, PFP-005, PFP-006, PFP-007, PFP-008, PFP-009, PFP-011, PFP-012, PFP-013, PFP-014, PFP-023, PFP-024, PFP-026, PFP-030, PFP-051, PFP-051a, PFP-052e, PFP-053e, PFP-056, PFP-061, PFP-062, PFP-063, and CAP697 SEP1 2.2.3.
- The integrated bank remains at 893 questions; 631 are source-exact and verified. Global questions 531-631 now correspond to subject 7 source questions 1-101.
- Kept browser-local progress at `pplStudyProgressV6` because the subject remains 101 questions and no global sequence numbers move.
- No database migration is required. Deploy the affected files and import the bundled bank from Admin.

## 0.10.0 - 2026-09-04

- Rebuilt **Provozní postupy** directly from the supplied official ÚCL PDF, version 1.0 dated 10.12.2020.
- Added all 60 published source questions with exact question wording, exact four answer strings, source answer order, and the check-marked correct answer.
- Restored source questions 33, 34, 35, 36, and 37 as five independent wake-turbulence questions instead of one merged compilation card.
- Added tailored curated **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** explanations for all 60 questions.
- Bundled the official source PDF as `data/source-documents/06-provozni-postupy-official.pdf`.
- Added local project-owned `OPR-001.svg` study artwork for microburst source questions 25 and 26.
- Integrated bank count is now 893 questions, with 530 source-exact and verified questions across subjects 1 through 6.
- Provozní postupy spans global questions 471-530. Provedení a plánování letu begins at global question 531.
- Browser-local progress key is `pplStudyProgressV6` because restored source questions shift later global sequence numbers.
- No database migration is required. Import the bundled bank from Admin after deploying the affected files.

## 0.9.0 - 2026-09-04

- Rebuilt **Letové zásady** directly from the supplied official ÚCL PDF, version 1.3 dated 12.08.2025.
- Preserved exact wording, all four published options in source order, and the marked correct answer for all 99 active source questions.
- Recorded source question 8 as retired (`Vyřazena`) and excluded it from the active bank.
- Restored source questions 48 and 70 as separate records instead of the compilation's merged critical-angle-of-attack card.
- Added curated Principle, memory-aid, and operational-context explanations for all 99 active questions.
- Bundled `data/source-documents/05-letove-zasady-official.pdf` with SHA-256 `50839b3a314b8627ddd19035bb3b15b29284c0c1ad7e04cd7aa55ded7ab33967`.
- Added local SVG study redraws for PFA-003, PFA-008, PFA-009, PFA-010, and PFA-011.
- Integrated bank now contains 889 questions, 470 source-exact and 470 verified.
- Browser-local progress key is `pplStudyProgressV5` because the restored split shifts later global sequence numbers.
- No database migration is required; import the bundled bank from Admin after deployment.

## 0.8.0 - 2026-09-04

### Communication source-exact rebuild

- Rebuilt subject 4 from the directly supplied official `Komunikace.pdf`, version 1.1 dated 12.08.2025.
- Preserved the exact published wording, all four answer strings, source answer order, and the check-marked correct answer for source questions 1-87.
- Restored source questions 54 and 55 as separate records instead of the compact compilation card that merged both readback examples.
- Added tailored titles and structured **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** sections for all 87 Communication questions.
- Added `data/source-exact/04-komunikace.json` and `data/curation/04-komunikace.json`; the curation manifest now activates subjects 1-4.
- Bundled the supplied source PDF as `data/source-documents/04-komunikace-official.pdf` with SHA-256 `cda0b7d7698ad0e8423df9e043f6183ed37493367fc4371eb2ee265398a73eab`.
- Integrated bank count is now 888; subjects 1-4 are fully source-exact and verified for a total of 371 verified questions.
- Communication occupies global questions 285-371; Letové zásady now begins at global question 372.
- Browser-local progress uses `pplStudyProgressV4` because restoring the separate source question 55 shifts global sequence numbers from subject 5 onward.
- No database migration is required; import the bundled bank after uploading the affected files.

## 0.7.0 - 2026-09-04

### Meteorology source-exact rebuild

- Rebuilt subject 3 from the directly supplied official `3-Meteorologie-letoun.pdf`, version 1.1 dated 29.12.2020.
- Preserved exact question wording, exact four answer choices, source order, and the checked correct answer for all 129 questions.
- Cross-checked all 129 marked answers against the existing compilation-derived correct-answer bank before replacing subject data.
- Added tailored curated explanations for all 129 questions with Principle, memory aid, and operational context.
- Bundled the official source PDF under `data/source-documents/03-meteorologie-official.pdf`.
- Added local SVG study redraws for MET-001, MET-002, MET-004, and MET-005 and wired them directly into the corresponding questions.
- Integrated bank count remains 887; subjects 1-3 are now fully source-exact and verified for a total of 284 verified questions.
- Global sequence numbering is unchanged from 0.6.0, so browser-local progress remains on `pplStudyProgressV3`.
- No database migration is required; import the bundled bank from Admin after deploying the affected files.

## 0.6.0 - 2026-09-04

### Human Performance source-exact rebuild

- Rebuilt subject 2 from the supplied official `2.-Lidska-vykonnost-letoun.pdf`, version 1.2 dated 31.03.2022.
- Preserved exact question wording, exact four answer choices, source order, and the checked correct answer for all 58 questions.
- Split source questions 7 and 9 back into two independent CO questions.
- Added tailored curated explanations for all 58 questions.
- Bundled the official source PDF under `data/source-documents/02-lidska-vykonnost-official.pdf`.
- Added a local SVG redraw of HPL-002 for the two diagram questions.
- Integrated bank count is now 887, with subjects 1 and 2 fully source-exact and verified.
- No database migration is required; import the bundled bank from Admin after deploying the affected files.

# PPL Study Patch Notes

## 0.5.0 - 2026-09-04

- Rebuilt the complete first subject directly from the supplied official ÚCL PDF.
- Restored all 97 published questions in **Právní předpisy v oblasti letectví**; the previous 91-card compact representation had merged source questions 32, 33, 34, 35, 36, 38, and 39.
- Questions 1-97 now preserve the exact source wording, exact four answer strings, source answer order, and the check-marked correct answer.
- Question 6 now reads exactly `Co znamená zkratka ARC?` and uses the four ARC expansions from the source PDF.
- Marked all first-subject questions `source_exact` and `verified` while retaining the project explanation sections.
- Added `data/source-exact/01-pravni-predpisy.json` and a stable subject/source-question curation overlay.
- Added `data/curation/manifest.json`; legacy sequence overlays remain in the project but are excluded from active regeneration.
- Extended `scripts/apply_curation.py` to replace merged subject cards with exact source questions before renumbering and source-keyed curation.
- Bundled the directly supplied source PDF as `data/source-documents/01-pravni-predpisy-official.pdf` and made bundled source PDFs available through the authenticated source route without a prior database upload row.
- Source-exact questions keep the published answer order in individual cards and drill JSON.
- The bank now contains 886 questions with per-subject counts 97/57/129/86/98/56/101/128/134.
- Browser-local progress uses `pplStudyProgressV2` because restoring six source questions shifts global sequence numbers after the first subject.
- No database migration is required; import the bundled bank after uploading the affected files.

## 0.4.1 - 2026-09-04

- Curated questions 11-91 in sequence, completing the full **Právní předpisy v oblasti letectví** subject.
- Added tailored titles to previously untitled cards in this range.
- Added structured **Princip**, **Jak si to zapamatovat**, and **Provozní souvislost** sections to every question from 11 through 91.
- Added specific handling notes for attachment questions ALW-019, ALW-020, ALW-011, and ALW-012 while keeping their exact source graphics pending verified local source material.
- Added `data/curation/0011-0091.json`; regeneration now reapplies both sequential curation overlays.
- Bundled `data/question-bank.json` now contains 91 explained questions, 789 draft questions, and question 92 is the next curation boundary.
- No database migration is required. Existing 0.4.0 installations only need the affected files and a bundled-bank import.

## 0.4.0 - 2026-09-04

- Added sequential curation tracking for every question: waiting, explanation complete, or source verified.
- Added a separate wording-fidelity state so source-exact wording can be tracked independently from explanation quality.
- Added structured explanation sections and curated questions 1-10 with principle, memory aid, and operational context.
- Added question-bank schema 3 and database migration `202609040004_question_curation.php`.
- Added quiz mode and learning mode. Learning mode reveals the answer on request and records learner self-assessment.
- Drill feedback now renders structured explanations when available.
- Added `data/source-manifest.json` and protected `data/source-documents/` storage.
- Added **Admin > Zdroje** for administrator-supplied source PDFs, including SHA-256 metadata and authenticated inline viewing.
- Added source-document references and source PDF page numbers to the question editor and card source panel.
- Added curation progress metrics to the administrator dashboard and status columns to the question list.
- Added `scripts/apply_curation.py` so curated content survives regeneration of the base bank from the compilation PDF.
- Bundled third-party source PDFs remain external to the release; administrators can associate a locally supplied PDF with its manifest entry.

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
