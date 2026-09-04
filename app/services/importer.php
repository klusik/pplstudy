<?php

/**
 * Project: PPL Study
 * File: app/services/importer.php
 * Module Type: Service
 *
 * Purpose:
 *   Validates and imports versioned question-bank JSON into MySQL/MariaDB.
 *
 * Responsibilities:
 *   - Validate structural invariants before database changes
 *   - Upsert subjects and questions transactionally
 *   - Preserve a reproducible bundled import path for first installation
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE and data/README.md.
 *
 * Notes:
 *   - Shared hosting is not expected to parse PDFs. PDF-to-JSON regeneration is
 *     an offline development task handled by scripts/build_question_bank.py.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use PDO;
use RuntimeException;
use Throwable;
use function PplStudy\Core\db;
use function PplStudy\Core\now_sql;

/** Load and decode one question-bank JSON file. */
function question_bank_load_file(string $path): array
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Question-bank file is missing or unreadable.');
    }
    $raw = file_get_contents($path);
    if (!is_string($raw) || $raw === '') {
        throw new RuntimeException('Question-bank file is empty.');
    }
    $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($payload)) {
        throw new RuntimeException('Question-bank JSON must decode to an object.');
    }
    return $payload;
}

/** Validate the complete import payload and return compact statistics. */
function question_bank_validate_payload(array $payload): array
{
    $schemaVersion = (int) ($payload['schema_version'] ?? 0);
    if (!in_array($schemaVersion, [2, 3], true)) {
        throw new RuntimeException('Unsupported question-bank schema_version. Expected 2 or 3.');
    }
    $subjects = $payload['subjects'] ?? null;
    $questions = $payload['questions'] ?? null;
    if (!is_array($subjects) || !is_array($questions)) {
        throw new RuntimeException('Question bank must contain subjects and questions arrays.');
    }
    if ($subjects === [] || $questions === []) {
        throw new RuntimeException('Question bank requires at least one item.');
    }

    $subjectIds = [];
    $expectedBySubject = [];
    foreach ($subjects as $index => $subject) {
        if (!is_array($subject)) {
            throw new RuntimeException('Subject entry ' . ($index + 1) . ' is invalid.');
        }
        $id = (int) ($subject['id'] ?? 0);
        if ($id < 1 || $id > 255 || isset($subjectIds[$id])) {
            throw new RuntimeException('Subject IDs must be unique integers from 1 to 255.');
        }
        foreach (['slug', 'name', 'exam_question_count', 'exam_time_minutes', 'expected_cards'] as $required) {
            if (!array_key_exists($required, $subject) || trim((string) $subject[$required]) === '') {
                throw new RuntimeException('Subject ' . $id . ' is missing ' . $required . '.');
            }
        }
        $subjectIds[$id] = true;
        $expectedBySubject[$id] = (int) $subject['expected_cards'];
    }

    $sequences = [];
    $countsBySubject = array_fill_keys(array_keys($subjectIds), 0);
    $attachmentCount = 0;
    $generatedChoicesCount = 0;
    foreach ($questions as $index => $question) {
        if (!is_array($question)) {
            throw new RuntimeException('Question entry ' . ($index + 1) . ' is invalid.');
        }
        $sequence = (int) ($question['sequence_no'] ?? 0);
        $subjectId = (int) ($question['subject_id'] ?? 0);
        if ($sequence < 1 || $sequence > 65535 || isset($sequences[$sequence])) {
            throw new RuntimeException('Question sequence numbers must be unique positive integers.');
        }
        if (!isset($subjectIds[$subjectId])) {
            throw new RuntimeException('Question ' . $sequence . ' references unknown subject ' . $subjectId . '.');
        }
        foreach (['source_trace', 'prompt', 'answer', 'explanation'] as $required) {
            if (trim((string) ($question[$required] ?? '')) === '') {
                throw new RuntimeException('Question ' . $sequence . ' is missing ' . $required . '.');
            }
        }
        if (!is_array($question['source_question_ids'] ?? [])) {
            throw new RuntimeException('Question ' . $sequence . ' source_question_ids must be an array.');
        }
        $choices = $question['choices'] ?? null;
        if (!is_array($choices) || count($choices) !== 4) {
            throw new RuntimeException('Question ' . $sequence . ' must contain exactly four study choices.');
        }
        $normalizedChoices = [];
        $answerMatches = 0;
        $answer = trim((string) $question['answer']);
        foreach ($choices as $choiceIndex => $choice) {
            $choiceText = trim((string) $choice);
            if ($choiceText === '') {
                throw new RuntimeException('Question ' . $sequence . ' contains an empty study choice at position ' . ($choiceIndex + 1) . '.');
            }
            $normalized = strtolower(preg_replace('/\s+/u', ' ', $choiceText) ?? $choiceText);
            if (isset($normalizedChoices[$normalized])) {
                throw new RuntimeException('Question ' . $sequence . ' contains duplicate study choices.');
            }
            $normalizedChoices[$normalized] = true;
            if ($choiceText === $answer) {
                $answerMatches++;
            }
        }
        if ($answerMatches !== 1) {
            throw new RuntimeException('Question ' . $sequence . ' must contain the correct answer exactly once in its study choices.');
        }
        $sequences[$sequence] = true;
        $countsBySubject[$subjectId]++;
        if (trim((string) ($question['attachment_code'] ?? '')) !== '') {
            $attachmentCount++;
        }
        if (!empty($question['choices_generated'])) {
            $generatedChoicesCount++;
        }

        if ($schemaVersion >= 3) {
            $curationStatus = (string) ($question['curation_status'] ?? 'draft');
            $wordingStatus = (string) ($question['wording_status'] ?? 'study_wording');
            if (!in_array($curationStatus, ['draft', 'explained', 'verified'], true)) {
                throw new RuntimeException('Question ' . $sequence . ' has an invalid curation_status.');
            }
            if (!in_array($wordingStatus, ['study_wording', 'source_exact'], true)) {
                throw new RuntimeException('Question ' . $sequence . ' has an invalid wording_status.');
            }
            if ($curationStatus === 'verified' && $wordingStatus !== 'source_exact') {
                throw new RuntimeException('Question ' . $sequence . ' can be verified only with source_exact wording.');
            }
            $sections = $question['explanation_sections'] ?? [];
            if (!is_array($sections)) {
                throw new RuntimeException('Question ' . $sequence . ' explanation_sections must be an array.');
            }
            foreach ($sections as $sectionIndex => $section) {
                if (!is_array($section) || trim((string) ($section['heading'] ?? '')) === '' || trim((string) ($section['body'] ?? '')) === '') {
                    throw new RuntimeException('Question ' . $sequence . ' has an invalid explanation section at position ' . ($sectionIndex + 1) . '.');
                }
            }
        }
    }

    foreach ($expectedBySubject as $subjectId => $expected) {
        if ($countsBySubject[$subjectId] !== $expected) {
            throw new RuntimeException('Subject ' . $subjectId . ' contains ' . $countsBySubject[$subjectId] . ' questions; expected ' . $expected . '.');
        }
    }

    $sequenceNumbers = array_map('intval', array_keys($sequences));
    sort($sequenceNumbers, SORT_NUMERIC);
    if ($sequenceNumbers !== range(1, count($sequenceNumbers))) {
        throw new RuntimeException('Question sequence numbers must form one contiguous range beginning at 1.');
    }

    return [
        'subjects' => count($subjects),
        'questions' => count($questions),
        'attachments' => $attachmentCount,
        'generated_choices' => $generatedChoicesCount,
        'explained' => count(array_filter($questions, static fn (array $question): bool => in_array((string) ($question['curation_status'] ?? 'draft'), ['explained', 'verified'], true))),
        'verified' => count(array_filter($questions, static fn (array $question): bool => (string) ($question['curation_status'] ?? 'draft') === 'verified')),
        'source_exact' => count(array_filter($questions, static fn (array $question): bool => (string) ($question['wording_status'] ?? 'study_wording') === 'source_exact')),
        'counts_by_subject' => $countsBySubject,
        'bank' => is_array($payload['bank'] ?? null) ? $payload['bank'] : [],
    ];
}

/** Import a validated payload using upsert semantics. */
function question_bank_import(array $payload, bool $pruneMissing = true, ?PDO $pdo = null): array
{
    $summary = question_bank_validate_payload($payload);
    $pdo ??= db();
    $ownTransaction = !$pdo->inTransaction();
    if ($ownTransaction) {
        $pdo->beginTransaction();
    }

    try {
        $subjectStmt = $pdo->prepare('INSERT INTO subjects (id, slug, name, description, source_version, exam_question_count, exam_time_minutes, expected_cards, official_pdf_url, attachments_url, sort_order, active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?) ON DUPLICATE KEY UPDATE slug = VALUES(slug), name = VALUES(name), description = VALUES(description), source_version = VALUES(source_version), exam_question_count = VALUES(exam_question_count), exam_time_minutes = VALUES(exam_time_minutes), expected_cards = VALUES(expected_cards), official_pdf_url = VALUES(official_pdf_url), attachments_url = VALUES(attachments_url), sort_order = VALUES(sort_order), active = 1, updated_at = VALUES(updated_at)');
        foreach ($payload['subjects'] as $position => $subject) {
            $now = now_sql();
            $subjectStmt->execute([
                (int) $subject['id'],
                substr((string) $subject['slug'], 0, 100),
                substr((string) $subject['name'], 0, 190),
                (string) ($subject['description'] ?? ''),
                substr((string) ($subject['source_version'] ?? ''), 0, 100),
                (int) $subject['exam_question_count'],
                (int) $subject['exam_time_minutes'],
                (int) $subject['expected_cards'],
                (string) ($subject['official_pdf_url'] ?? ''),
                (string) ($subject['attachments_url'] ?? ''),
                $position + 1,
                $now,
                $now,
            ]);
        }

        $questionStmt = $pdo->prepare('INSERT INTO questions (sequence_no, subject_id, title, source_trace, source_question_ids_json, merged, prompt, answer, choices_json, choices_generated, curation_status, wording_status, explanation, explanation_sections_json, warning, attachment_code, attachment_url, attachment_indexed, source_page, source_document_key, source_document_page, source_verified_at, active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?) ON DUPLICATE KEY UPDATE subject_id = VALUES(subject_id), title = VALUES(title), source_trace = VALUES(source_trace), source_question_ids_json = VALUES(source_question_ids_json), merged = VALUES(merged), prompt = VALUES(prompt), answer = VALUES(answer), choices_json = VALUES(choices_json), choices_generated = VALUES(choices_generated), curation_status = VALUES(curation_status), wording_status = VALUES(wording_status), explanation = VALUES(explanation), explanation_sections_json = VALUES(explanation_sections_json), warning = VALUES(warning), attachment_code = VALUES(attachment_code), attachment_url = VALUES(attachment_url), attachment_indexed = VALUES(attachment_indexed), source_page = VALUES(source_page), source_document_key = VALUES(source_document_key), source_document_page = VALUES(source_document_page), source_verified_at = VALUES(source_verified_at), active = 1, updated_at = VALUES(updated_at)');
        $sequences = [];
        foreach ($payload['questions'] as $question) {
            $now = now_sql();
            $sequence = (int) $question['sequence_no'];
            $sequences[] = $sequence;
            $questionStmt->execute([
                $sequence,
                (int) $question['subject_id'],
                trim((string) ($question['title'] ?? '')) !== '' ? (string) $question['title'] : null,
                (string) $question['source_trace'],
                json_encode(array_values(array_map('intval', $question['source_question_ids'] ?? [])), JSON_UNESCAPED_SLASHES),
                !empty($question['merged']) ? 1 : 0,
                (string) $question['prompt'],
                (string) $question['answer'],
                json_encode(array_values(array_map(static fn ($choice): string => trim((string) $choice), $question['choices'])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                !empty($question['choices_generated']) ? 1 : 0,
                in_array((string) ($question['curation_status'] ?? 'draft'), ['draft', 'explained', 'verified'], true) ? (string) ($question['curation_status'] ?? 'draft') : 'draft',
                in_array((string) ($question['wording_status'] ?? 'study_wording'), ['study_wording', 'source_exact'], true) ? (string) ($question['wording_status'] ?? 'study_wording') : 'study_wording',
                (string) $question['explanation'],
                json_encode(is_array($question['explanation_sections'] ?? null) ? array_values($question['explanation_sections']) : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                trim((string) ($question['warning'] ?? '')) !== '' ? (string) $question['warning'] : null,
                trim((string) ($question['attachment_code'] ?? '')) !== '' ? (string) $question['attachment_code'] : null,
                trim((string) ($question['attachment_url'] ?? '')) !== '' ? (string) $question['attachment_url'] : null,
                !empty($question['attachment_indexed']) ? 1 : 0,
                isset($question['source_page']) && $question['source_page'] !== null ? (int) $question['source_page'] : null,
                trim((string) ($question['source_document_key'] ?? '')) !== '' ? (string) $question['source_document_key'] : null,
                isset($question['source_document_page']) && $question['source_document_page'] !== null ? (int) $question['source_document_page'] : null,
                (string) ($question['curation_status'] ?? '') === 'verified' && (string) ($question['wording_status'] ?? '') === 'source_exact' ? $now : null,
                $now,
                $now,
            ]);
        }

        if ($pruneMissing) {
            $placeholders = implode(',', array_fill(0, count($sequences), '?'));
            $stmt = $pdo->prepare('DELETE FROM questions WHERE sequence_no NOT IN (' . $placeholders . ')');
            $stmt->execute($sequences);
            $subjectIds = array_map(static fn (array $subject): int => (int) $subject['id'], $payload['subjects']);
            $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));
            $stmt = $pdo->prepare('DELETE FROM subjects WHERE id NOT IN (' . $subjectPlaceholders . ')');
            $stmt->execute($subjectIds);
        }

        foreach ([
            'bank_name' => (string) ($summary['bank']['name'] ?? ''),
            'bank_compilation_version' => (string) ($summary['bank']['compilation_version'] ?? ''),
            'bank_compiled_at' => (string) ($summary['bank']['compiled_at'] ?? ''),
            'bank_official_source_page' => (string) ($summary['bank']['official_source_page'] ?? ''),
        ] as $key => $value) {
            $stmt = $pdo->prepare('INSERT INTO app_settings (setting_key, setting_value, updated_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)');
            $stmt->execute([$key, $value, now_sql()]);
        }

        if ($ownTransaction) {
            $pdo->commit();
        }
        return $summary;
    } catch (Throwable $exception) {
        if ($ownTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

/** Load and import the bundled bank shipped with the release. */
function import_bundled_question_bank(bool $pruneMissing = true, ?PDO $pdo = null): array
{
    $path = dirname(__DIR__, 2) . '/data/question-bank.json';
    return question_bank_import(question_bank_load_file($path), $pruneMissing, $pdo);
}
