#!/usr/bin/env python3
"""
Project: PPL Study
File: scripts/generate_study_choices.py
Module Type: Offline Development Utility

Purpose:
    Build four study answer choices for each question from the supplied study
    compilation without reproducing the absent official multiple-choice set.

Approach:
    The correct answer remains the source-derived answer already present in the
    compilation. Distractors are selected deterministically from other answers
    in the study bank, favoring the same subject, answer shape, nearby topics,
    units, and wording length. Simple one-number answers may also use generated
    numeric variants so options stay useful when the bank contains too few
    same-unit candidates.

Important:
    These are project-generated study distractors. Administrators can review
    and replace them in the application.

Notes:
    - Keep comments and docstrings intact when modifying this file.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import math
import re
import unicodedata
from collections import defaultdict
from pathlib import Path
from typing import Iterable


STOPWORDS = {
    'a', 'aby', 'ale', 'ani', 'ano', 'bez', 'bude', 'byt', 'co', 'do', 'ho', 'i', 'jak', 'jako',
    'je', 'jej', 'jeji', 'jeho', 'jen', 'jsou', 'k', 'ke', 'ktera', 'ktere', 'ktery', 'ma', 'mezi',
    'na', 'nad', 'nebo', 'nez', 'o', 'od', 'po', 'pod', 'pro', 'proc', 'pri', 'se', 'si', 's',
    'tento', 'to', 'u', 'urci', 'v', 've', 'z', 'za', 'ze', 'znamena', 'spravna', 'spravny',
    'spravnou', 'hodnotu', 'popis', 'vysvetli', 'vyznam', 'pojmu', 'udaje', 'definuj', 'identifikuj',
    'nasledujiciho', 'nasledujici', 'moznosti', 'moznost', 'odpoved', 'otazka', 'plati', 'treba',
}

UNIT_ALIASES = {
    'ft': 'ft', 'stop': 'ft', 'stopa': 'ft', 'stopy': 'ft', 'stopach': 'ft',
    'km': 'km', 'kilometr': 'km', 'kilometry': 'km', 'kilometru': 'km',
    'm': 'm', 'metr': 'm', 'metry': 'm', 'metru': 'm', 'nm': 'nm',
    'kt': 'kt', 'kts': 'kt', 'uzel': 'kt', 'uzly': 'kt', 'uzlu': 'kt',
    'hpa': 'hpa', 'mhz': 'mhz', 'khz': 'khz',
    'min': 'min', 'minuta': 'min', 'minuty': 'min', 'minut': 'min',
    'h': 'h', 'hodina': 'h', 'hodiny': 'h', 'hodin': 'h',
    'let': 'years', 'roku': 'years', 'roky': 'years', 'roků': 'years',
    'mesic': 'months', 'mesice': 'months', 'mesicu': 'months',
    'procent': 'percent', 'stupnu': 'deg', 'stupne': 'deg', 'stupen': 'deg',
    'kg': 'kg', 'l': 'l', 'litru': 'l', 'litry': 'l', 'litru': 'l',
    'v': 'volt', 'volt': 'volt', 'voltu': 'volt',
}


def strip_accents(text: str) -> str:
    """Return a lowercase comparison form without diacritics."""
    normalized = unicodedata.normalize('NFKD', text)
    return ''.join(ch for ch in normalized if not unicodedata.combining(ch)).lower()


def normalize_text(text: str) -> str:
    """Return a whitespace/punctuation-normalized comparison key."""
    value = strip_accents(text)
    value = re.sub(r'[^a-z0-9%°.+/-]+', ' ', value)
    return re.sub(r'\s+', ' ', value).strip()


def topic_tokens(question: dict) -> set[str]:
    """Extract meaningful tokens from title and prompt for topic matching."""
    text = normalize_text(f"{question.get('title', '')} {question.get('prompt', '')}")
    return {token for token in text.split() if len(token) > 2 and token not in STOPWORDS and not token.isdigit()}


def numeric_parts(answer: str) -> list[tuple[str, float]]:
    """Return numeric strings and parsed values found in an answer."""
    parts: list[tuple[str, float]] = []
    for match in re.finditer(r'(?<![\w])[-+]?\d+(?:[.,]\d+)?', answer):
        raw = match.group(0)
        try:
            parts.append((raw, float(raw.replace(',', '.'))))
        except ValueError:
            continue
    return parts


def detect_unit(answer: str) -> str:
    """Detect a compact unit family used for numeric distractor matching."""
    lowered = strip_accents(answer).replace(',', '.')
    if '%' in lowered:
        return 'percent'
    if '°' in answer:
        return 'deg'
    for word in re.findall(r'[a-zA-Z]+', lowered):
        if word in UNIT_ALIASES:
            return UNIT_ALIASES[word]
    return ''


def answer_signature(answer: str) -> str:
    """Classify answer shape so distractors resemble the correct response."""
    value = answer.strip()
    normalized = normalize_text(value)
    numbers = numeric_parts(value)
    if len(numbers) == 1 and len(value) <= 64:
        return f'numeric:{detect_unit(value)}'
    if 2 <= len(numbers) <= 6 and len(value) <= 220:
        return 'numeric-multi'
    if re.fullmatch(r'[A-Z0-9][A-Z0-9 ./+,_-]{0,24}', value) and any(ch.isalpha() for ch in value):
        return 'code'
    if ';' in value:
        return 'list:semicolon'
    if value.count(',') >= 3 and len(value) >= 45:
        return 'list:comma'
    if len(normalized) <= 18:
        return 'short'
    if len(normalized) <= 55:
        return 'medium'
    return 'long'


def jaccard(left: set[str], right: set[str]) -> float:
    """Return Jaccard similarity for two token sets."""
    if not left or not right:
        return 0.0
    union = left | right
    return len(left & right) / len(union) if union else 0.0


def too_similar_norm(left: str, right: str) -> bool:
    """Reject duplicate or near-duplicate normalized answer wording."""
    if not left or not right or left == right:
        return True
    if left in right or right in left:
        shorter = min(len(left), len(right))
        longer = max(len(left), len(right))
        if shorter / longer > 0.78:
            return True
    left_tokens = set(left.split())
    right_tokens = set(right.split())
    if left_tokens and right_tokens:
        overlap = len(left_tokens & right_tokens) / max(1, min(len(left_tokens), len(right_tokens)))
        if overlap >= 0.92 and min(len(left), len(right)) / max(len(left), len(right)) >= 0.80:
            return True
    return False


def candidate_score(question: dict, candidate: dict) -> float:
    """Score one precomputed bank answer as a distractor for another question."""
    score = 0.0
    if question['_signature'] == candidate['_signature']:
        score += 9.0
    elif question['_signature_group'] == candidate['_signature_group']:
        score += 4.0
    if question['_unit'] and question['_unit'] == candidate['_unit']:
        score += 5.0
    score += 14.0 * jaccard(question['_topic_tokens'], candidate['_topic_tokens'])
    distance = abs(int(question['sequence_no']) - int(candidate['sequence_no']))
    score += max(0.0, 6.0 - distance / 5.0)
    score += 3.0 * (min(question['_answer_len'], candidate['_answer_len']) / max(question['_answer_len'], candidate['_answer_len']))
    return score


def format_number_like(raw: str, value: float) -> str:
    """Format a replacement number using the source answer's decimal style."""
    decimal_comma = ',' in raw
    decimal_places = 0
    if '.' in raw or ',' in raw:
        decimal_places = len(re.split(r'[.,]', raw, maxsplit=1)[1])
    if decimal_places:
        rendered = f'{value:.{decimal_places}f}'
        if rendered.endswith('.' + ('0' * decimal_places)):
            rendered = rendered.split('.', 1)[0]
    else:
        rendered = str(int(round(value)))
    return rendered.replace('.', ',') if decimal_comma else rendered


def numeric_variant_values(value: float, unit: str) -> list[float]:
    """Return plausible alternate values for one-number study answers."""
    if unit == 'percent':
        common = [25, 50, 60, 70, 75, 80, 90, 95, 100]
    elif unit == 'ft':
        common = [500, 1000, 1500, 2000, 2500, 3000, 5000, 7000, 10000, 12000, 18000]
    elif unit == 'km':
        common = [1.5, 3, 5, 8, 10, 15]
    elif unit == 'nm':
        common = [1, 2, 3, 5, 10, 15, 20, 25, 30]
    elif unit == 'kt':
        common = [5, 10, 15, 20, 25, 30, 40, 50, 60, 90, 100, 120]
    elif unit == 'hpa':
        common = [980, 990, 1000, 1009, 1013.25, 1020, 1030]
    elif unit == 'mhz':
        common = [118.0, 118.5, 121.5, 123.45, 125.0, 130.0]
    elif unit == 'min':
        common = [1, 2, 5, 10, 15, 20, 25, 30, 35, 45, 60]
    elif unit == 'h':
        common = [1, 2, 3, 5, 10, 12, 24, 48]
    elif unit == 'years':
        common = [10, 12, 14, 16, 17, 18, 21, 50]
    elif unit == 'months':
        common = [1, 3, 6, 12, 24, 60]
    elif unit == 'deg':
        common = [15, 30, 45, 60, 90, 120, 180, 270, 360]
    else:
        common = []

    rounded = int(round(value))
    values: list[float] = []
    if 7000 <= rounded <= 7999 and rounded % 100 == 0:
        values.extend([7500.0, 7600.0, 7700.0, 7000.0])
    values.extend(float(candidate) for candidate in common)
    if abs(value) < 10:
        values.extend([value - 1, value + 1, value + 2, value * 2])
    elif abs(value) < 100:
        values.extend([value - 5, value + 5, value - 10, value + 10])
    elif abs(value) < 1000:
        values.extend([value * 0.8, value * 1.2, value - 50, value + 50])
    else:
        values.extend([value * 0.8, value * 1.2, value - 500, value + 500])

    ordered: list[float] = []
    seen: set[str] = set()
    for candidate in sorted(values, key=lambda item: abs(item - value)):
        if candidate <= 0 and value > 0:
            continue
        key = f'{candidate:.6f}'
        if math.isclose(candidate, value, rel_tol=0.0, abs_tol=1e-9) or key in seen:
            continue
        seen.add(key)
        ordered.append(candidate)
    return ordered


def generated_numeric_distractors(question: dict) -> list[str]:
    """Generate alternate numeric answers while preserving surrounding text."""
    answer = str(question['answer'])
    parts = numeric_parts(answer)
    if not parts or len(parts) > 6 or len(answer) > 220:
        return []

    results: list[str] = []
    if len(parts) == 1:
        raw, value = parts[0]
        match = re.search(re.escape(raw), answer)
        if not match:
            return []
        prefix = answer[:match.start()]
        suffix = answer[match.end():]
        if 'UTC' in answer.upper() and re.fullmatch(r'\d{4}', raw):
            hours = int(raw[:2])
            minutes = int(raw[2:])
            total = hours * 60 + minutes
            for delta in (-60, -30, 30, 60, 90):
                candidate_total = (total + delta) % (24 * 60)
                replacement = f'{candidate_total // 60:02d}{candidate_total % 60:02d}'
                result = f'{prefix}{replacement}{suffix}'
                if normalize_text(result) != question['_norm_answer'] and result not in results:
                    results.append(result)
            return results
        for candidate in numeric_variant_values(value, question['_unit']):
            result = f'{prefix}{format_number_like(raw, candidate)}{suffix}'
            if normalize_text(result) != question['_norm_answer'] and result not in results:
                results.append(result)
            if len(results) >= 8:
                break
        return results

    # Multi-value answers are common in minima, timing, mass-and-balance and
    # performance questions. Change one value at a time so the distractor keeps
    # the same structure and remains plausible.
    offsets: list[tuple[int, int, str, float]] = []
    for match in re.finditer(r'(?<![\w])[-+]?\d+(?:[.,]\d+)?', answer):
        raw = match.group(0)
        try:
            value = float(raw.replace(',', '.'))
        except ValueError:
            continue
        offsets.append((match.start(), match.end(), raw, value))

    for index, (start_pos, end_pos, raw, value) in enumerate(offsets):
        local_unit = detect_unit(answer[end_pos:end_pos + 18]) or question['_unit']
        variants = numeric_variant_values(value, local_unit)
        for replacement_value in variants[:3]:
            replacement = format_number_like(raw, replacement_value)
            result = answer[:start_pos] + replacement + answer[end_pos:]
            if normalize_text(result) != question['_norm_answer'] and result not in results:
                results.append(result)
                break
        if len(results) >= 6:
            break
    return results


def structured_text_distractors(question: dict) -> list[str]:
    """Generate plausible omissions or compact variants for structured answers."""
    answer = str(question['answer']).strip()
    signature = question['_signature']
    results: list[str] = []

    if signature == 'list:semicolon':
        parts = [part.strip() for part in answer.split(';') if part.strip()]
        if len(parts) >= 3:
            # Omit different required items. This preserves the answer's topic
            # and wording while creating clearly distinct incomplete variants.
            order = [len(parts) - 1, 1, 0, max(0, len(parts) - 2)]
            for remove_index in order:
                if remove_index >= len(parts):
                    continue
                variant = '; '.join(part for index, part in enumerate(parts) if index != remove_index)
                if answer.endswith('.'):
                    variant = variant.rstrip('.') + '.'
                if variant and variant not in results:
                    results.append(variant)
                if len(results) >= 3:
                    break

    if signature == 'list:comma':
        parts = [part.strip() for part in answer.split(',') if part.strip()]
        if 3 <= len(parts) <= 10:
            for remove_index in [len(parts) - 1, 1, 0]:
                variant = ', '.join(part for index, part in enumerate(parts) if index != remove_index)
                if variant and variant not in results:
                    results.append(variant)

    # Compact code lists such as "A, C, S" benefit from code-shaped variants.
    if re.fullmatch(r'(?:[A-Z]{1,4},\s*){1,5}[A-Z]{1,4}', answer):
        parts = [part.strip() for part in answer.split(',')]
        if len(parts) >= 2:
            results.append(', '.join(parts[:-1]))
            results.append(', '.join(parts[1:]))
            replacement = list(parts)
            replacement[-1] = 'B' if replacement[-1] != 'B' else 'D'
            results.append(', '.join(replacement))

    # Questions with a two-item conjunction often test that both elements are
    # required. Single-item variants are useful distractors.
    if 20 <= len(answer) <= 150 and answer.count(' a ') == 1 and not numeric_parts(answer) and ',' not in answer:
        left, right = [part.strip(' .') for part in answer.split(' a ', 1)]
        if len(left) >= 4 and len(right) >= 4:
            results.extend([left, right])

    if re.fullmatch(r'[A-Z]', answer):
        for candidate in ['T', 'L', 'X', 'H']:
            if candidate != answer and candidate not in results:
                results.append(candidate)

    return results


def explanation_candidates(question: dict) -> list[str]:
    """Extract explicit contrast terms already named in the card explanation."""
    explanation = str(question.get('explanation', ''))
    results: list[str] = []

    # Named airspace/service concepts with parenthetical abbreviations.
    for match in re.finditer(r'([A-Za-zÁ-ž]+(?:\s+[A-Za-zÁ-ž]+){0,2}\s+prostor)\s*\(([A-Z]{1,6})\)', explanation, flags=re.IGNORECASE):
        phrase = match.group(1).strip(' .,:;')
        if ' a ' in phrase.lower():
            phrase = re.split(r'\s+a\s+', phrase, flags=re.IGNORECASE)[-1].strip()
        abbreviation = match.group(2).strip()
        if phrase and phrase not in results:
            results.append(phrase)
        if abbreviation and abbreviation not in results:
            results.append(abbreviation)

    # Uppercase aviation abbreviations explicitly discussed in the explanation.
    for token in re.findall(r'\b[A-Z][A-Z0-9-]{1,8}\b', explanation):
        if token not in results:
            results.append(token)

    # Quoted alternatives often encode a studied phrase or signal.
    for quoted in re.findall(r'[„“\"]([^„“\"]{2,70})[„“\"]', explanation):
        quoted = quoted.strip()
        if quoted and quoted not in results:
            results.append(quoted)

    return results


def deterministic_shuffle(values: list[str], sequence: int) -> list[str]:
    """Shuffle choices reproducibly so the correct answer is not always A."""
    decorated = []
    for index, value in enumerate(values):
        digest = hashlib.sha256(f'{sequence}:{index}:{value}'.encode('utf-8')).hexdigest()
        decorated.append((digest, value))
    decorated.sort(key=lambda item: item[0])
    return [value for _, value in decorated]


def add_unique(distractors: list[str], candidate: str, correct_norm: str) -> bool:
    """Append a candidate when it is unique enough to be useful."""
    candidate = candidate.strip()
    norm = normalize_text(candidate)
    if not candidate or too_similar_norm(correct_norm, norm):
        return False
    for existing in distractors:
        if too_similar_norm(normalize_text(existing), norm):
            return False
    distractors.append(candidate)
    return True


def add_generated_variant(distractors: list[str], candidate: str, correct_norm: str) -> bool:
    """Append a deliberately mutated answer when it differs exactly from existing choices."""
    candidate = candidate.strip()
    norm = normalize_text(candidate)
    if not candidate or not norm or norm == correct_norm:
        return False
    if any(normalize_text(existing) == norm for existing in distractors):
        return False
    distractors.append(candidate)
    return True


def build_choices(question: dict, subject_pool: list[dict], signature_pool: list[dict], all_questions: list[dict]) -> list[str]:
    """Build one four-choice set containing the correct answer exactly once."""
    correct = str(question['answer']).strip()
    distractors: list[str] = []

    ranked_subject = [candidate for candidate in subject_pool if candidate is not question]
    ranked_subject.sort(key=lambda candidate: candidate_score(question, candidate), reverse=True)

    # Preserve the structure of the source-derived correct answer whenever we
    # can. Numeric substitutions and incomplete list variants are usually much
    # stronger distractors than unrelated correct answers from another card.
    if question['_signature'].startswith('numeric'):
        for candidate in generated_numeric_distractors(question):
            add_generated_variant(distractors, candidate, question['_norm_answer'])
            if len(distractors) >= 3:
                break
    if len(distractors) < 3:
        for candidate in structured_text_distractors(question):
            add_generated_variant(distractors, candidate, question['_norm_answer'])
            if len(distractors) >= 3:
                break
    if len(distractors) < 3:
        for candidate in explanation_candidates(question):
            candidate_norm = normalize_text(candidate)
            if candidate_norm and candidate_norm not in question['_norm_answer'] and question['_norm_answer'] not in candidate_norm:
                add_unique(distractors, candidate, question['_norm_answer'])
            if len(distractors) >= 3:
                break

    # First bank pass: same subject plus compatible answer shape. Topic-local clusters
    # yield strong alternatives for signals, abbreviations, definitions, and limits.
    for candidate in ranked_subject:
        shape_match = candidate['_signature'] == question['_signature'] or candidate['_signature_group'] == question['_signature_group']
        unit_match = bool(question['_unit'] and candidate['_unit'] == question['_unit'])
        topic_match = jaccard(question['_topic_tokens'], candidate['_topic_tokens']) > 0
        close = abs(int(question['sequence_no']) - int(candidate['sequence_no'])) <= 18
        if shape_match and (unit_match or topic_match or close or question['_signature'] in {'code', 'short'}):
            add_unique(distractors, str(candidate['answer']), question['_norm_answer'])
        if len(distractors) >= 3:
            break

    # Then use same-signature answers across the whole bank.
    if len(distractors) < 3:
        ranked_signature = [candidate for candidate in signature_pool if candidate is not question]
        ranked_signature.sort(key=lambda candidate: candidate_score(question, candidate), reverse=True)
        for candidate in ranked_signature:
            add_unique(distractors, str(candidate['answer']), question['_norm_answer'])
            if len(distractors) >= 3:
                break

    # Final fallback remains within the same subject before using the whole bank.
    if len(distractors) < 3:
        for candidate in ranked_subject:
            add_unique(distractors, str(candidate['answer']), question['_norm_answer'])
            if len(distractors) >= 3:
                break

    if len(distractors) < 3:
        for candidate in all_questions:
            if candidate is not question:
                add_unique(distractors, str(candidate['answer']), question['_norm_answer'])
            if len(distractors) >= 3:
                break

    if len(distractors) < 3:
        raise RuntimeError(f"Could not generate three unique distractors for question {question['sequence_no']}.")

    return deterministic_shuffle([correct, *distractors[:3]], int(question['sequence_no']))


def enrich_questions_with_choices(questions: Iterable[dict]) -> list[dict]:
    """Return question dictionaries enriched with generated study choices."""
    items = [dict(question) for question in questions]
    by_subject: dict[int, list[dict]] = defaultdict(list)
    by_signature: dict[str, list[dict]] = defaultdict(list)

    for question in items:
        answer = str(question['answer'])
        question['_topic_tokens'] = topic_tokens(question)
        question['_norm_answer'] = normalize_text(answer)
        question['_signature'] = answer_signature(answer)
        question['_signature_group'] = question['_signature'].split(':', 1)[0]
        question['_unit'] = detect_unit(answer)
        question['_answer_len'] = max(1, len(question['_norm_answer']))
        by_subject[int(question['subject_id'])].append(question)
        by_signature[question['_signature']].append(question)

    for question in items:
        question['choices'] = build_choices(
            question,
            by_subject[int(question['subject_id'])],
            by_signature[question['_signature']],
            items,
        )
        question['choices_generated'] = True

    for question in items:
        for key in ('_topic_tokens', '_norm_answer', '_signature', '_signature_group', '_unit', '_answer_len'):
            question.pop(key, None)
    return items


def parse_args() -> argparse.Namespace:
    """Parse command-line input and optional output path."""
    parser = argparse.ArgumentParser(description='Generate editable study choices for a PPL Study question-bank JSON file.')
    parser.add_argument('input_json', type=Path)
    parser.add_argument('output_json', type=Path, nargs='?')
    return parser.parse_args()


def main() -> None:
    """Enrich a question bank and write schema version 2 JSON."""
    args = parse_args()
    output = args.output_json or args.input_json
    payload = json.loads(args.input_json.read_text(encoding='utf-8'))
    questions = payload.get('questions')
    if not isinstance(questions, list) or not questions:
        raise SystemExit('Input JSON does not contain a non-empty questions array.')
    payload['questions'] = enrich_questions_with_choices(questions)
    payload['schema_version'] = 2
    bank = payload.setdefault('bank', {})
    bank['choice_model'] = 'generated_study_distractors_v1'
    bank['choice_note'] = 'Study answer choices are generated by PPL Study from the compilation bank and are editable by administrators.'
    output.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(f'Wrote {output}: {len(payload["questions"])} questions with four study choices each')


if __name__ == '__main__':
    main()
