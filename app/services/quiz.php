<?php

/**
 * Project: PPL Study
 * File: app/services/quiz.php
 * Module Type: Service
 *
 * Purpose:
 *   Builds randomized multiple-choice study drills from the source compilation.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Each card carries four editable study choices and one source-derived correct answer.
 *   - Paper drills mirror question count and time from the compilation.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use function PplStudy\Core\db;
use function PplStudy\Core\study_limit;

/** Convert a database card to the JSON shape consumed by the browser drill. */
function quiz_public_question_shape(array $row): array
{
    $decodedChoices = json_decode((string) ($row['choices_json'] ?? '[]'), true);
    $choices = is_array($decodedChoices) ? array_values(array_map(static fn ($choice): string => trim((string) $choice), $decodedChoices)) : [];
    if (count($choices) === 4) {
        shuffle($choices);
    }
    return [
        'sequence_no' => (int) $row['sequence_no'],
        'subject_id' => (int) $row['subject_id'],
        'subject_name' => (string) $row['subject_name'],
        'title' => (string) ($row['title'] ?? ''),
        'source_trace' => (string) $row['source_trace'],
        'prompt' => (string) $row['prompt'],
        'answer' => (string) $row['answer'],
        'choices' => $choices,
        'choices_generated' => !empty($row['choices_generated']),
        'explanation' => (string) $row['explanation'],
        'warning' => (string) ($row['warning'] ?? ''),
        'attachment_code' => (string) ($row['attachment_code'] ?? ''),
        'attachment_url' => (string) ($row['attachment_url'] ?? ''),
        'source_page' => $row['source_page'] !== null ? (int) $row['source_page'] : null,
    ];
}

/** Return randomized cards for one subject or the whole active bank. */
function quiz_random_cards(?int $subjectId, int $count): array
{
    $count = max(1, min(study_limit('quiz_custom_max_questions'), $count));
    $where = 'q.active = 1 AND s.active = 1';
    $params = [];
    if ($subjectId !== null && $subjectId > 0) {
        $where .= ' AND q.subject_id = ?';
        $params[] = $subjectId;
    }
    $sql = 'SELECT q.*, s.name AS subject_name FROM questions q JOIN subjects s ON s.id = q.subject_id WHERE ' . $where . ' ORDER BY RAND() LIMIT ' . $count;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return array_map(__NAMESPACE__ . '\\quiz_public_question_shape', $stmt->fetchAll());
}

/** Build a paper drill using the subject's published count and time values. */
function quiz_subject_paper(int $subjectId): ?array
{
    $subject = subject_by_id($subjectId);
    if (!$subject || empty($subject['active'])) {
        return null;
    }
    $count = (int) $subject['exam_question_count'];
    return [
        'mode' => 'paper',
        'title' => (string) $subject['name'] . ' - paper drill',
        'subject' => $subject,
        'question_count' => $count,
        'time_minutes' => (int) $subject['exam_time_minutes'],
        'questions' => quiz_random_cards($subjectId, $count),
        'pass_percent' => 75,
    ];
}

/** Build a custom random drill. */
function quiz_custom(?int $subjectId, int $count): array
{
    $subject = $subjectId !== null ? subject_by_id($subjectId) : null;
    $questions = quiz_random_cards($subjectId, $count);
    return [
        'mode' => 'custom',
        'title' => $subject ? (string) $subject['name'] . ' - vlastní drill' : 'Všechny předměty - vlastní drill',
        'subject' => $subject,
        'question_count' => count($questions),
        'time_minutes' => 0,
        'questions' => $questions,
        'pass_percent' => 75,
    ];
}
