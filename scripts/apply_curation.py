#!/usr/bin/env python3
"""
Project: PPL Study
File: scripts/apply_curation.py
Module Type: Offline Development Utility

Purpose:
    Apply source-exact subject replacements and stable curation overlays to a
    generated question-bank JSON file.

Notes:
    - Source-exact subject files live in data/source-exact/*.json.
    - Source-exact replacement happens before curation overlays are applied.
    - Subject/source-question keys remain stable when compact compilation cards
      are split into the individual questions published by the source.
    - Legacy sequence-number overlays remain supported for older project data.
    - Keep comments and docstrings intact when modifying this file.
"""

from __future__ import annotations

import argparse
import copy
import json
from pathlib import Path


def load_json(path: Path) -> dict:
    """Load one UTF-8 JSON object."""
    with path.open('r', encoding='utf-8') as handle:
        value = json.load(handle)
    if not isinstance(value, dict):
        raise ValueError(f'{path} must contain a JSON object')
    return value


def apply_source_exact_replacements(payload: dict, source_exact_dir: Path) -> dict:
    """Replace compact subject cards with exact source questions and renumber globally."""
    questions = payload.get('questions')
    subjects = payload.get('subjects')
    if not isinstance(questions, list) or not isinstance(subjects, list):
        raise ValueError('question bank must contain questions and subjects lists')
    if not source_exact_dir.is_dir():
        return payload

    for path in sorted(source_exact_dir.glob('*.json')):
        source = load_json(path)
        subject_id = int(source.get('subject_id', 0))
        exact_questions = source.get('questions')
        if subject_id < 1 or not isinstance(exact_questions, list) or not exact_questions:
            raise ValueError(f'{path} has invalid subject_id/questions metadata')

        existing = [question for question in questions if int(question.get('subject_id', 0)) == subject_id]
        templates: dict[int, dict] = {}
        for question in existing:
            for source_id in question.get('source_question_ids', []):
                source_id = int(source_id)
                if source_id in templates:
                    raise ValueError(f'{path}: source question {source_id} maps to multiple templates')
                templates[source_id] = question

        replacement: list[dict] = []
        seen_source_ids: set[int] = set()
        for exact in exact_questions:
            if not isinstance(exact, dict):
                raise ValueError(f'{path} contains an invalid exact question entry')
            source_id = int(exact.get('source_question_id', 0))
            if source_id < 1 or source_id in seen_source_ids:
                raise ValueError(f'{path} contains an invalid or duplicate source_question_id')
            seen_source_ids.add(source_id)
            if source_id not in templates:
                raise ValueError(f'{path}: no compact template contains source question {source_id}')

            choices = exact.get('choices')
            if not isinstance(choices, list) or len(choices) != 4:
                raise ValueError(f'{path}: source question {source_id} must contain exactly four choices')
            choices = [str(choice).strip() for choice in choices]
            answer = str(exact.get('answer', '')).strip()
            if choices.count(answer) != 1:
                raise ValueError(f'{path}: source question {source_id} answer must occur exactly once')

            question = copy.deepcopy(templates[source_id])
            question.update({
                'subject_id': subject_id,
                'title': str(exact.get('title') or question.get('title') or f'Otázka {source_id}'),
                'source_trace': f'otázka {source_id}',
                'source_question_ids': [source_id],
                'merged': False,
                'prompt': str(exact.get('prompt', '')).strip(),
                'answer': answer,
                'choices': choices,
                'choices_generated': False,
                'wording_status': 'source_exact',
                'source_document_key': str(source.get('source_key', '')).strip(),
                'source_document_page': int(exact.get('source_document_page', 0)) or None,
                'attachment_code': str(exact.get('attachment_code', '')).strip(),
                'attachment_url': str(exact.get('attachment_url', '')).strip(),
            })
            replacement.append(question)

        if set(templates) != seen_source_ids:
            missing = sorted(set(templates) - seen_source_ids)
            raise ValueError(f'{path}: exact source file does not cover template source IDs {missing}')

        questions = [question for question in questions if int(question.get('subject_id', 0)) != subject_id]
        questions.extend(replacement)
        for subject in subjects:
            if int(subject.get('id', 0)) == subject_id:
                subject['expected_cards'] = len(replacement)
                break
        else:
            raise ValueError(f'{path}: subject {subject_id} is missing from subject metadata')

    questions.sort(key=lambda question: (
        int(question.get('subject_id', 0)),
        min([int(value) for value in question.get('source_question_ids', [])] or [65535]),
        int(question.get('sequence_no', 0)),
    ))
    for sequence, question in enumerate(questions, start=1):
        question['sequence_no'] = sequence

    payload['questions'] = questions
    bank = payload.setdefault('bank', {})
    bank['study_cards'] = len(questions)
    bank['source_exact_subject_replacement'] = True
    return payload


def overlay_paths(overlay_dir: Path) -> list[Path]:
    """Return deterministic overlay paths, honoring data/curation/manifest.json when present."""
    manifest_path = overlay_dir / 'manifest.json'
    if manifest_path.is_file():
        manifest = load_json(manifest_path)
        names = manifest.get('overlays')
        if not isinstance(names, list):
            raise ValueError(f'{manifest_path} must contain an overlays list')
        result = []
        for name in names:
            candidate = overlay_dir / Path(str(name)).name
            if not candidate.is_file():
                raise ValueError(f'{manifest_path} references missing overlay {candidate.name}')
            result.append(candidate)
        return result
    return [path for path in sorted(overlay_dir.glob('*.json')) if path.name != 'manifest.json']


def apply_overlays(payload: dict, overlay_dir: Path) -> dict:
    """Apply source-exact replacements, then curation patches by stable source identity."""
    questions = payload.get('questions')
    if not isinstance(questions, list):
        raise ValueError('question bank must contain a questions list')

    payload = apply_source_exact_replacements(payload, overlay_dir.parent / 'source-exact')
    questions = payload['questions']

    by_sequence = {int(question['sequence_no']): question for question in questions}
    by_source: dict[tuple[int, int], dict] = {}
    for question in questions:
        subject_id = int(question.get('subject_id', 0))
        source_ids = [int(value) for value in question.get('source_question_ids', [])]
        if len(source_ids) == 1:
            by_source[(subject_id, source_ids[0])] = question

    applied = 0
    for path in overlay_paths(overlay_dir):
        overlay = load_json(path)
        patches = overlay.get('questions')
        if not isinstance(patches, list):
            raise ValueError(f'{path} must contain a questions list')
        for patch in patches:
            if not isinstance(patch, dict):
                raise ValueError(f'{path} contains an invalid question patch')

            target = None
            subject_id = int(patch.get('subject_id', 0))
            source_question_id = int(patch.get('source_question_id', 0))
            if subject_id > 0 and source_question_id > 0:
                target = by_source.get((subject_id, source_question_id))
                if target is None:
                    raise ValueError(
                        f'{path} references unknown subject/source question '
                        f'{subject_id}/{source_question_id}'
                    )
                ignored_keys = {'subject_id', 'source_question_id'}
            else:
                sequence = int(patch.get('sequence_no', 0))
                target = by_sequence.get(sequence)
                if target is None:
                    raise ValueError(f'{path} references unknown sequence {sequence}')
                ignored_keys = {'sequence_no'}

            for key, value in patch.items():
                if key not in ignored_keys:
                    target[key] = value
            applied += 1

    payload['schema_version'] = max(3, int(payload.get('schema_version', 0)))
    bank = payload.setdefault('bank', {})
    bank['curation_model'] = 'source_exact_subject_replacement_v2'
    bank['curation_note'] = (
        'Official subject PDFs can replace compact compilation cards with exact source questions. '
        'Curated explanations are then applied by stable subject/source-question identity.'
    )
    bank['curation_patches_applied'] = applied
    bank['source_exact_questions'] = sum(
        1 for question in payload['questions']
        if str(question.get('wording_status', '')) == 'source_exact'
    )
    bank['source_exact_subjects'] = sorted({
        int(question.get('subject_id', 0)) for question in payload['questions']
        if str(question.get('wording_status', '')) == 'source_exact'
    })
    if bank['source_exact_questions'] == len(payload['questions']) and payload['questions']:
        bank['choice_model'] = 'source_exact_all_subjects_v1'
        bank['choice_note'] = (
            'All active questions preserve the published four answer choices and source order '
            'from the directly supplied official subject PDFs.'
        )
        bank['note'] = (
            'All nine subjects reproduce the supplied official source wording, answer choices, '
            'source order and marked correct answer. Project-curated explanations remain a '
            'separate study layer.'
        )
    elif bank['source_exact_questions'] > 0:
        bank['choice_model'] = 'mixed_source_exact_and_generated_v2'
        bank['choice_note'] = (
            'Source-exact subjects preserve the published four answer choices and source order. '
            'Subjects awaiting source verification retain generated study distractors.'
        )
        bank['note'] = (
            'Source-exact subjects reproduce the supplied official source wording, answer choices, '
            'source order and marked correct answer. Remaining subjects retain the compilation-derived '
            'study model until their official source PDFs are processed.'
        )
    return payload


def main() -> int:
    """CLI entry point."""
    parser = argparse.ArgumentParser(description='Apply PPL Study source-exact data and curation overlays.')
    parser.add_argument('bank_json', type=Path, nargs='?', default=Path('data/question-bank.json'))
    parser.add_argument('--overlay-dir', type=Path, default=Path('data/curation'))
    args = parser.parse_args()

    payload = apply_overlays(load_json(args.bank_json), args.overlay_dir)
    with args.bank_json.open('w', encoding='utf-8') as handle:
        json.dump(payload, handle, ensure_ascii=False, indent=2)
        handle.write('\n')
    print(f'Applied source-exact replacements and curation overlays to {args.bank_json}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
