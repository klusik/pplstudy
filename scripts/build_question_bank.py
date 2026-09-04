#!/usr/bin/env python3
"""
Project: PPL Study
File: scripts/build_question_bank.py
Module Type: Offline Development Utility

Purpose:
    Convert the supplied PPL(A) study compilation PDF version 3.0 into the
    versioned JSON question-bank format consumed by the PHP importer.

Requirements:
    Python 3.10+ and Poppler pdftotext on PATH.

Important:
    This parser intentionally enforces the known structure of the supplied
    version 3.0 compilation. A structural mismatch raises an error rather than
    silently producing a partial or shifted bank.
"""

from __future__ import annotations

import argparse
import datetime as dt
import json
import re
import shutil
import subprocess
import tempfile
from pathlib import Path

from generate_study_choices import enrich_questions_with_choices


SUBJECT_META = [
    {'id': 1, 'slug': 'pravni-predpisy', 'name': 'Právní předpisy v oblasti letectví', 'exam_question_count': 12, 'exam_time_minutes': 20, 'expected_cards': 91, 'source_version': '1.0, 29.12.2020', 'description': 'Part-FCL, SERA, dokumentace letadla, vzdušný prostor, pravidla vyhýbání, SSR, ATS/AIS, letištní signály a bezpečnostní šetření.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/1-Letecky-zakon-a-postupy-ATC-letoun.pdf?cb=ea1e3325ff27d2215bca6884278bf74b', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/1-Letecky-zakon-a-postupy-ATC-prilohy.zip?cb=4ea10ea9ed465f10f9e465062204ba52'},
    {'id': 2, 'slug': 'lidska-vykonnost', 'name': 'Lidská výkonnost', 'exam_question_count': 12, 'exam_time_minutes': 20, 'expected_cards': 57, 'source_version': '1.2, 31.03.2022', 'description': 'Hypoxie a další fyziologická omezení, zrak a vestibulární iluze, stres, únava, rozhodování a modely lidské chyby.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2022/03/2.-Lidska-vykonnost-letoun.pdf?cb=8004da996035ab6c78e0e1ef237788f3', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/2-Lidska-vykonnost-a-omezeni-prilohy.zip?cb=a6ea0caea0b820ae4cc0c161a5f31279'},
    {'id': 3, 'slug': 'meteorologie', 'name': 'Meteorologie', 'exam_question_count': 12, 'exam_time_minutes': 20, 'expected_cards': 129, 'source_version': '1.1, 29.12.2020', 'description': 'Tlak, teplota, stabilita, vlhkost, vítr, fronty, oblaka, srážky, námraza a bouřky se zaměřením na význam pro VFR pilota.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/3-Meteorologie-letoun.pdf?cb=2b5a86fb57a52d34dfa3c47c42b8bb43', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/3-Meteorologie-prilohy.zip?cb=f3edccef25b3c9cfe916137170c8e6b3'},
    {'id': 4, 'slug': 'komunikace', 'name': 'Komunikace', 'exam_question_count': 12, 'exam_time_minutes': 25, 'expected_cards': 86, 'source_version': '1.1, 12.08.2025', 'description': 'Standardní slova, tísňový a pilnostní provoz, blind transmission, readback, altimetrie, transpondér a základní meteorologické kódy.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2025/08/Komunikace.pdf?cb=a8691b6532de1f990e13dad4dad5d560', 'attachments_url': ''},
    {'id': 5, 'slug': 'letove-zasady', 'name': 'Letové zásady', 'exam_question_count': 12, 'exam_time_minutes': 25, 'expected_cards': 98, 'source_version': '1.3, 12.08.2025', 'description': 'Aerodynamika, zatížení, stabilita a účinek kormidel, klapek, slotů a trimu.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2025/08/Letove-zasady-letoun.pdf?cb=8e382d9b5e55cd7ab72d0ca26a29cf64', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/5-Letove-zasady-prilohy.zip?cb=dfad7ffdcb2c2d6ef198214241d711f8'},
    {'id': 6, 'slug': 'provozni-postupy', 'name': 'Provozní postupy', 'exam_question_count': 12, 'exam_time_minutes': 25, 'expected_cards': 56, 'source_version': '1.0, 10.12.2020', 'description': 'Požár, námraza, windshear a microburst, turbulence v úplavu, nouzová přistání, nebezpečné plochy a kontaminovaná dráha.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/6-Provozni-postupy-letoun.pdf?cb=8945842cf7af6df95d22f17d2741fc1b', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/6-Provozni-postupy-prilohy.zip?cb=8b32737c968f721b0be13b57fa5ac9ef'},
    {'id': 7, 'slug': 'provedeni-planovani', 'name': 'Provedení a plánování letu', 'exam_question_count': 16, 'exam_time_minutes': 35, 'expected_cards': 101, 'source_version': '1.2, 30.12.2021', 'description': 'Hmotnost a vyvážení, výkonnost při vzletu, stoupání a přistání, rychlosti, palivo, letový plán a práce s grafy.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2021/12/7-Vykonnost-a-planovani-letu-letoun.pdf?cb=8dbfad5e25b1d482456469ec1d7ab7ff', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/7-Vykonnost-a-planovani-letu-prilohy.zip?cb=eba73448870bea3ae82e7647f73a88ee'},
    {'id': 8, 'slug': 'obecne-znalosti', 'name': 'Obecné znalosti o letadle', 'exam_question_count': 16, 'exam_time_minutes': 35, 'expected_cards': 128, 'source_version': '1.0, 30.12.2020', 'description': 'Konstrukce, motor, vrtule, palivová, hydraulická a elektrická soustava a letové přístroje.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/8-Obecne-znalosti-o-letadle-letoun.pdf?cb=a7604a369f40cfa069c881dae17f1c4c', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/8-Vseobecna-znalost-letadla-prilohy.zip?cb=d6e59963e4cf86472b6962ba398d808a'},
    {'id': 9, 'slug': 'navigace', 'name': 'Navigace', 'exam_question_count': 16, 'exam_time_minutes': 35, 'expected_cards': 134, 'source_version': '1.0, 09.12.2020', 'description': 'Zeměpisná geometrie, čas, mapy, magnetismus, výpočtová navigace a radionavigace, včetně true/magnetic a track/heading vztahů.', 'official_pdf_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/9-Navigace-letoun.pdf?cb=2b3055b703982d7ca1a4589ff4e1b0fa', 'attachments_url': 'https://www.caa.gov.cz/wp-content/uploads/2020/11/9-Navigace-prilohy.zip?cb=81de53c12ef3f0cb28ac44e06f90862a'},
]

OFFICIAL_SOURCE_PAGE = 'https://www.caa.gov.cz/zkusebni-otazky-pro-zkousky-teoretickych-znalosti-ppl-lapl/'


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description='Build PPL Study question-bank JSON from the supplied compilation PDF.')
    parser.add_argument('input_pdf', type=Path)
    parser.add_argument('output_json', type=Path, nargs='?', default=Path('data/question-bank.json'))
    return parser.parse_args()


def extract_layout_text(pdf: Path) -> str:
    if not pdf.is_file():
        raise SystemExit(f'Input PDF not found: {pdf}')
    if shutil.which('pdftotext') is None:
        raise SystemExit('pdftotext was not found on PATH. Install Poppler first.')
    with tempfile.TemporaryDirectory(prefix='ppl-study-bank-') as tmp:
        txt_path = Path(tmp) / 'source.txt'
        subprocess.run(['pdftotext', '-layout', str(pdf), str(txt_path)], check=True)
        return txt_path.read_text(encoding='utf-8')


def build_payload(text: str) -> dict:
    index_start = text.find('Přílohové otázky - rychlý index', 10000)
    index_end = text.find('Zdroje, metodika a kontrola', index_start + 1)
    attachment_index: dict[int, str] = {}
    if index_start >= 0 and index_end > index_start:
        for line in text[index_start:index_end].splitlines():
            match = re.match(r'^\s*(\d+)\s+.*?\b([A-Z]{2,5}-\d{3})\b\s+\d+\s*$', line)
            if match:
                attachment_index[int(match.group(1))] = match.group(2)

    pages = text.split('\f')
    page_offsets: list[tuple[int, int]] = []
    cursor = 0
    for page_no, page in enumerate(pages, 1):
        page_offsets.append((cursor, page_no))
        cursor += len(page) + 1

    def page_for_pos(pos: int) -> int:
        page_no = 1
        for offset, candidate in page_offsets:
            if offset > pos:
                break
            page_no = candidate
        return page_no

    subject_by_id = {subject['id']: subject for subject in SUBJECT_META}
    ranges: list[tuple[int, int, int]] = []
    start = 1
    for subject in SUBJECT_META:
        end = start + subject['expected_cards'] - 1
        ranges.append((start, end, subject['id']))
        start = end + 1

    def subject_for(sequence: int) -> int:
        for first, last, subject_id in ranges:
            if first <= sequence <= last:
                return subject_id
        raise RuntimeError(f'Question {sequence} falls outside declared subject ranges.')

    section_names = '|'.join(re.escape(subject['name']) for subject in SUBJECT_META)
    section_header_re = re.compile(r'(?m)(?:^|(?<=\f))[ \t]*[1-9]\. (?:' + section_names + r')[ \t]*$')
    footer_re = re.compile(r'^PPL\(A\) teorie - studijní kompilát 2026, FINAL\s+Strana \d+$')

    def clean(value: str) -> str:
        lines = [line for line in value.splitlines() if not footer_re.match(line.strip())]
        return re.sub(r'\s+', ' ', '\n'.join(lines)).strip()

    card_re = re.compile(r'(?m)(?:^|(?<=\f))[ \t]*(\d{3})\.[ \t]*(.*)$')
    matches = list(card_re.finditer(text))
    if len(matches) != 880:
        raise RuntimeError(f'Expected 880 card markers, found {len(matches)}. Source structure changed or extraction failed.')

    cards: list[dict] = []
    labels = ['Veřejná stopa ÚCL:', 'Kontrolní otázka:', 'Správná odpověď:', 'Proč:', 'Pozor:', 'Příloha:']
    for index, match in enumerate(matches):
        sequence = int(match.group(1))
        title = clean(match.group(2))
        end = matches[index + 1].start() if index + 1 < len(matches) else text.find('Přílohové otázky - rychlý index', match.end())
        block = text[match.end():end]
        next_section = section_header_re.search(block)
        if next_section:
            block = block[:next_section.start()]

        positions: list[tuple[int, str, int]] = []
        for label in labels:
            found = re.search(re.escape(label), block)
            if found:
                positions.append((found.start(), label, found.end()))
        positions.sort()
        fields: dict[str, str] = {}
        for field_index, (pos, label, start_pos) in enumerate(positions):
            stop = positions[field_index + 1][0] if field_index + 1 < len(positions) else len(block)
            fields[label] = clean(block[start_pos:stop])

        source_trace = fields.get('Veřejná stopa ÚCL:', '')
        source_question_ids = [int(value) for value in re.findall(r'\d+', source_trace.split('|', 1)[0])]
        attachment_text = fields.get('Příloha:', '')
        attachment_code = ''
        attachment_match = re.search(r'\b([A-Z]{2,5}-\d{3})\b', attachment_text)
        if attachment_match:
            attachment_code = attachment_match.group(1)
        if not attachment_code and 'příloh' in fields.get('Kontrolní otázka:', '').lower():
            attachment_match = re.search(r'\b([A-Z]{2,5}-\d{3})\b', fields.get('Kontrolní otázka:', ''))
            if attachment_match:
                attachment_code = attachment_match.group(1)

        subject_id = subject_for(sequence)
        card = {
            'sequence_no': sequence,
            'subject_id': subject_id,
            'title': title,
            'source_trace': source_trace,
            'source_question_ids': source_question_ids,
            'merged': 'sloučeno' in source_trace.lower(),
            'prompt': fields.get('Kontrolní otázka:', ''),
            'answer': fields.get('Správná odpověď:', ''),
            'explanation': fields.get('Proč:', ''),
            'warning': fields.get('Pozor:', ''),
            'attachment_code': attachment_code,
            'attachment_url': subject_by_id[subject_id]['attachments_url'] if attachment_code else '',
            'attachment_indexed': sequence in attachment_index,
            'source_page': page_for_pos(match.start()),
        }
        for required in ('source_trace', 'prompt', 'answer', 'explanation'):
            if not card[required]:
                raise RuntimeError(f'Card {sequence} is missing required field {required}.')
        cards.append(card)

    if [card['sequence_no'] for card in cards] != list(range(1, 881)):
        raise RuntimeError('Card sequence is not exactly 1..880.')
    for subject in SUBJECT_META:
        actual = sum(card['subject_id'] == subject['id'] for card in cards)
        if actual != subject['expected_cards']:
            raise RuntimeError(f"Subject {subject['id']} count mismatch: expected {subject['expected_cards']}, got {actual}.")
    if len(attachment_index) != 70:
        raise RuntimeError(f'Expected 70 rows in the source attachment quick index, found {len(attachment_index)}.')
    attachment_cards = sum(bool(card['attachment_code']) for card in cards)
    if attachment_cards != 73:
        raise RuntimeError(f'Expected 73 cards referencing attachments, found {attachment_cards}.')

    cards = enrich_questions_with_choices(cards)

    return {
        'schema_version': 2,
        'bank': {
            'name': 'PPL(A) teorie - studijní kompilát 2026',
            'compilation_version': '3.0',
            'compiled_at': '2026-08-22',
            'generated_project_at': dt.date.today().isoformat(),
            'public_source_items_active': 896,
            'public_source_items_retired': 1,
            'study_cards': 880,
            'subjects': 9,
            'official_source_page': OFFICIAL_SOURCE_PAGE,
            'note': 'The project uses its own editable study answer choices around the source-derived correct result. For real operations, current regulations, AIP, NOTAM, AFM/POH and ATS instructions take precedence.',
            'choice_model': 'generated_study_distractors_v1',
            'choice_note': 'Study answer choices are generated by PPL Study from the compilation bank and are editable in Admin.',
        },
        'subjects': SUBJECT_META,
        'questions': cards,
    }


def main() -> None:
    args = parse_args()
    payload = build_payload(extract_layout_text(args.input_pdf))
    args.output_json.parent.mkdir(parents=True, exist_ok=True)
    args.output_json.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(f"Wrote {args.output_json}: {len(payload['questions'])} cards, {len(payload['subjects'])} subjects")


if __name__ == '__main__':
    main()
