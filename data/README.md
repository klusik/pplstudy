# Bundled Question-Bank Data

`question-bank.json` is generated from the PPL(A) theoretical study compilation supplied for this project:

- title: `PPL(A) teorie - studijní kompilát 2026, FINAL`
- compilation version: 3.0
- compilation date: 22. 8. 2026
- active public source items stated by the compilation: 896
- one source item marked retired
- study cards after merging obvious duplicate/equivalent variants: 880
- subjects: 9

The compilation supplies the study question, correct result, explanation, and source trace. PPL Study adds four editable study choices per card. Generated distractors are derived from the local study bank and deterministic structural/value mutations.

## Data license boundary

The MIT license in the project root applies to application code written for PPL Study. It does not assert ownership of or relicense source-derived question content, regulator publications, attachment graphics, or third-party materials.

Before public redistribution, the project owner remains responsible for confirming the permitted use of the supplied compilation and linked source material.

## Attachments

Attachment images are not bundled. Cards store the attachment code and, where available, a link to the official ÚCL attachment ZIP for that subject.

The supplied compilation has a 70-row quick attachment index while three additional cards explicitly reference PFP-061, PFP-062, and PFP-063. The JSON therefore contains 73 cards with an attachment code and preserves an `attachment_indexed` flag so this distinction is not silently lost.

## Regeneration

Use the offline scripts:

```bash
python3 scripts/build_question_bank.py input.pdf data/question-bank.json
php scripts/validate_question_bank.php
```

`build_question_bank.py` calls the study-choice generator automatically. To regenerate choices for an existing compatible bank without re-parsing the PDF:

```bash
python3 scripts/generate_study_choices.py data/question-bank.json
php scripts/validate_question_bank.php
```

Do not parse the PDF on production web requests.
