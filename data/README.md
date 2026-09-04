# Bundled Question-Bank Data

`question-bank.json` is generated from the PPL(A) theoretical study compilation supplied for this project:

- title: `PPL(A) teorie - studijní kompilát 2026, FINAL`
- compilation version: 3.0
- compilation date: 22. 8. 2026
- active public source items stated by the compilation: 896
- one source item marked retired
- base compilation cards after merging obvious duplicate/equivalent variants: 880
- current bundled questions after restoring all nine subjects to their individual active source questions: 896
- subjects: 9

The compilation supplies the initial study structure and explanations. All nine subjects are replaced by the individual active questions extracted from the directly supplied official PDFs, with exact wording, exact four choices, source order, and the marked correct answer. The explanation layer remains project-curated.

## Data license boundary

The MIT license in the project root applies to application code written for PPL Study. It does not assert ownership of or relicense source-derived question content, regulator publications, attachment graphics, or third-party materials.

Before public redistribution, the project owner remains responsible for confirming the permitted use of the supplied compilation and linked source material.

## Source documents

`source-manifest.json` records stable source keys and official source URLs. The project owner directly supplied the official PDFs for all nine subjects. The corresponding files under `data/source-documents/` are bundled as verified local sources and carry SHA-256 metadata in the manifest. Additional source PDFs can still be associated through **Admin > Zdroje** when a later source version is introduced.

## Source-exact replacements

`data/source-exact/` contains complete source-exact active sets for all nine subjects: 97, 58, 129, 87, 99, 60, 101, 128, and 137 questions respectively. The Letové zásady source file records source question 8 as retired and excludes it from the active bank. `scripts/apply_curation.py` applies source-exact replacements first, renumbers the global sequence when necessary, then applies stable subject/source-question curation overlays listed by `curation/manifest.json`.

## Attachments

Attachment cards store the published attachment code. When a suitable local study asset is bundled, its project URL is stored directly and image formats render inline; otherwise the official attachment URL can be retained. HPL-002, Meteorology attachments MET-001, MET-002, MET-004, and MET-005, Letové zásady attachments PFA-003, PFA-008, PFA-009, PFA-010, and PFA-011, Provozní postupy attachment OPR-001, subject-7 PFP/CAP697 attachments, and the AGK/NAV attachment set used by subjects 8 and 9 are supplied as local project-owned SVG study redraws.

The supplied compilation has a 70-row quick attachment index while additional exact-source questions expose attachment references that were merged or omitted by the compact study model. The current integrated JSON contains 84 questions with an attachment code and preserves an `attachment_indexed` flag so source-index distinctions remain explicit.

## Regeneration

Use the offline scripts:

```bash
python3 scripts/build_question_bank.py input.pdf data/question-bank.json
python3 scripts/apply_curation.py data/question-bank.json
php scripts/validate_question_bank.php
```

`build_question_bank.py` calls the study-choice generator automatically. To regenerate choices for an existing compatible bank without re-parsing the PDF:

```bash
python3 scripts/generate_study_choices.py data/question-bank.json
php scripts/validate_question_bank.php
```

Do not parse the PDF on production web requests.
