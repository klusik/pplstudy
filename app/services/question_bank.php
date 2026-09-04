<?php

/**
 * Project: PPL Study
 * File: app/services/question_bank.php
 * Module Type: Service
 *
 * Purpose:
 *   Owns all read/write access to subjects and study cards so public and admin
 *   controllers share one question-bank contract.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use PDO;
use function PplStudy\Core\db;
use function PplStudy\Core\now_sql;
use function PplStudy\Core\study_limit;

/** Return all active subjects in display order. */
function subjects_all(bool $includeInactive = false): array
{
    $where = $includeInactive ? '' : 'WHERE active = 1';
    return db()->query('SELECT * FROM subjects ' . $where . ' ORDER BY sort_order, id')->fetchAll();
}

/** Return one subject by numeric ID. */
function subject_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM subjects WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

/** Return one subject by route slug. */
function subject_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM subjects WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

/** Return public card counts grouped by subject. */
function question_counts_by_subject(): array
{
    $rows = db()->query('SELECT subject_id, COUNT(*) AS total FROM questions WHERE active = 1 GROUP BY subject_id')->fetchAll();
    $counts = [];
    foreach ($rows as $row) {
        $counts[(int) $row['subject_id']] = (int) $row['total'];
    }
    return $counts;
}

/** Return the total number of cards. */
function question_count(bool $includeInactive = false): int
{
    $where = $includeInactive ? '' : ' WHERE active = 1';
    return (int) db()->query('SELECT COUNT(*) FROM questions' . $where)->fetchColumn();
}

/** Decode the source question ID list stored as JSON text. */
function normalize_question_row(array $row): array
{
    $decoded = json_decode((string) ($row['source_question_ids_json'] ?? '[]'), true);
    $row['source_question_ids'] = is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
    $decodedChoices = json_decode((string) ($row['choices_json'] ?? '[]'), true);
    $row['choices'] = is_array($decodedChoices) ? array_values(array_map(static fn ($choice): string => trim((string) $choice), $decodedChoices)) : [];
    $decodedSections = json_decode((string) ($row['explanation_sections_json'] ?? '[]'), true);
    $row['explanation_sections'] = is_array($decodedSections) ? array_values(array_filter($decodedSections, static fn ($section): bool => is_array($section))) : [];
    $row['sequence_no'] = (int) $row['sequence_no'];
    $row['subject_id'] = (int) $row['subject_id'];
    $row['merged'] = !empty($row['merged']);
    $row['choices_generated'] = !empty($row['choices_generated']);
    $row['attachment_indexed'] = !empty($row['attachment_indexed']);
    $row['source_page'] = isset($row['source_page']) && $row['source_page'] !== null ? (int) $row['source_page'] : null;
    $row['source_document_page'] = isset($row['source_document_page']) && $row['source_document_page'] !== null ? (int) $row['source_document_page'] : null;
    $row['curation_status'] = trim((string) ($row['curation_status'] ?? 'draft')) ?: 'draft';
    $row['wording_status'] = trim((string) ($row['wording_status'] ?? 'study_wording')) ?: 'study_wording';
    $row['source_document_key'] = trim((string) ($row['source_document_key'] ?? ''));
    $row['source_verified_at'] = (string) ($row['source_verified_at'] ?? '');
    $row['active'] = !empty($row['active']);
    return $row;
}

/** Return one card by compilation sequence number. */
function question_by_sequence(int $sequence, bool $includeInactive = false): ?array
{
    $sql = 'SELECT q.*, s.name AS subject_name, s.slug AS subject_slug, s.attachments_url AS subject_attachments_url, s.official_pdf_url AS subject_official_pdf_url FROM questions q JOIN subjects s ON s.id = q.subject_id WHERE q.sequence_no = ?';
    if (!$includeInactive) {
        $sql .= ' AND q.active = 1 AND s.active = 1';
    }
    $sql .= ' LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$sequence]);
    $row = $stmt->fetch();
    return is_array($row) ? normalize_question_row($row) : null;
}

/** Return the first/previous/next card sequence within the selected subject. */
function question_neighbor_sequence(int $subjectId, int $sequence, string $direction): ?int
{
    if ($direction === 'previous') {
        $stmt = db()->prepare('SELECT sequence_no FROM questions WHERE subject_id = ? AND active = 1 AND sequence_no < ? ORDER BY sequence_no DESC LIMIT 1');
    } else {
        $stmt = db()->prepare('SELECT sequence_no FROM questions WHERE subject_id = ? AND active = 1 AND sequence_no > ? ORDER BY sequence_no ASC LIMIT 1');
    }
    $stmt->execute([$subjectId, $sequence]);
    $value = $stmt->fetchColumn();
    return $value === false ? null : (int) $value;
}

/** Return one random active card, optionally limited to a subject. */
function random_question(?int $subjectId = null): ?array
{
    if ($subjectId !== null && $subjectId > 0) {
        $stmt = db()->prepare('SELECT sequence_no FROM questions WHERE active = 1 AND subject_id = ? ORDER BY RAND() LIMIT 1');
        $stmt->execute([$subjectId]);
    } else {
        $stmt = db()->query('SELECT sequence_no FROM questions WHERE active = 1 ORDER BY RAND() LIMIT 1');
    }
    $sequence = $stmt->fetchColumn();
    return $sequence === false ? null : question_by_sequence((int) $sequence);
}

/** Search cards for the public search page. */
function search_questions(string $query, ?int $subjectId = null, int $limit = 50, bool $includeInactive = false): array
{
    $limit = max(1, min(study_limit('public_search_page_size'), $limit));
    $query = trim($query);
    $where = [];
    $params = [];
    if (!$includeInactive) {
        $where[] = 'q.active = 1';
        $where[] = 's.active = 1';
    }
    if ($subjectId !== null && $subjectId > 0) {
        $where[] = 'q.subject_id = ?';
        $params[] = $subjectId;
    }
    if ($query !== '') {
        if (ctype_digit($query)) {
            $where[] = '(q.sequence_no = ? OR q.source_trace LIKE ? OR q.prompt LIKE ? OR q.answer LIKE ? OR q.title LIKE ?)';
            $params[] = (int) $query;
            for ($i = 0; $i < 4; $i++) {
                $params[] = '%' . $query . '%';
            }
        } else {
            $where[] = '(q.title LIKE ? OR q.prompt LIKE ? OR q.answer LIKE ? OR q.choices_json LIKE ? OR q.explanation LIKE ? OR q.warning LIKE ? OR q.attachment_code LIKE ? OR q.source_trace LIKE ?)';
            for ($i = 0; $i < 8; $i++) {
                $params[] = '%' . $query . '%';
            }
        }
    }
    $sql = 'SELECT q.*, s.name AS subject_name, s.slug AS subject_slug FROM questions q JOIN subjects s ON s.id = q.subject_id';
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY q.sequence_no LIMIT ' . $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return array_map(__NAMESPACE__ . '\\normalize_question_row', $stmt->fetchAll());
}

/** Return a paginated admin card list and total count. */
function admin_question_page(string $query, ?int $subjectId, int $page): array
{
    $pageSize = study_limit('question_list_page_size');
    $page = max(1, $page);
    $where = [];
    $params = [];
    if ($subjectId !== null && $subjectId > 0) {
        $where[] = 'q.subject_id = ?';
        $params[] = $subjectId;
    }
    $query = trim($query);
    if ($query !== '') {
        $where[] = '(CAST(q.sequence_no AS CHAR) = ? OR q.title LIKE ? OR q.prompt LIKE ? OR q.answer LIKE ? OR q.attachment_code LIKE ?)';
        $params[] = $query;
        for ($i = 0; $i < 4; $i++) {
            $params[] = '%' . $query . '%';
        }
    }
    $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

    $countStmt = db()->prepare('SELECT COUNT(*) FROM questions q' . $whereSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $maxPage = max(1, (int) ceil($total / $pageSize));
    $page = min($page, $maxPage);
    $offset = ($page - 1) * $pageSize;

    $sql = 'SELECT q.*, s.name AS subject_name FROM questions q JOIN subjects s ON s.id = q.subject_id' . $whereSql . ' ORDER BY q.sequence_no LIMIT ' . $pageSize . ' OFFSET ' . $offset;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return [
        'rows' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'pages' => $maxPage,
        'page_size' => $pageSize,
    ];
}

/** Persist editable card fields from the administrator form. */
function update_question(int $sequence, array $values): bool
{
    $choices = array_values(array_map(static fn ($choice): string => trim((string) $choice), is_array($values['choices'] ?? null) ? $values['choices'] : []));
    $sections = [];
    foreach (is_array($values['explanation_sections'] ?? null) ? $values['explanation_sections'] : [] as $section) {
        if (!is_array($section)) {
            continue;
        }
        $heading = trim((string) ($section['heading'] ?? ''));
        $body = trim((string) ($section['body'] ?? ''));
        if ($heading !== '' && $body !== '') {
            $sections[] = ['heading' => $heading, 'body' => $body];
        }
    }

    $curationStatus = in_array((string) ($values['curation_status'] ?? ''), ['draft', 'explained', 'verified'], true)
        ? (string) $values['curation_status']
        : 'draft';
    $wordingStatus = in_array((string) ($values['wording_status'] ?? ''), ['study_wording', 'source_exact'], true)
        ? (string) $values['wording_status']
        : 'study_wording';
    $verifiedAt = $curationStatus === 'verified' && $wordingStatus === 'source_exact'
        ? (trim((string) ($values['source_verified_at'] ?? '')) !== '' ? (string) $values['source_verified_at'] : now_sql())
        : null;

    $stmt = db()->prepare('UPDATE questions SET subject_id = ?, title = ?, source_trace = ?, prompt = ?, answer = ?, choices_json = ?, choices_generated = 0, curation_status = ?, wording_status = ?, explanation = ?, explanation_sections_json = ?, warning = ?, attachment_code = ?, attachment_url = ?, source_document_key = ?, source_document_page = ?, source_verified_at = ?, active = ?, updated_at = ? WHERE sequence_no = ?');
    $stmt->execute([
        (int) $values['subject_id'],
        trim((string) $values['title']) !== '' ? trim((string) $values['title']) : null,
        trim((string) $values['source_trace']),
        trim((string) $values['prompt']),
        trim((string) $values['answer']),
        json_encode($choices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $curationStatus,
        $wordingStatus,
        trim((string) $values['explanation']),
        json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        trim((string) $values['warning']) !== '' ? trim((string) $values['warning']) : null,
        trim((string) $values['attachment_code']) !== '' ? trim((string) $values['attachment_code']) : null,
        trim((string) $values['attachment_url']) !== '' ? trim((string) $values['attachment_url']) : null,
        trim((string) ($values['source_document_key'] ?? '')) !== '' ? trim((string) $values['source_document_key']) : null,
        isset($values['source_document_page']) && (int) $values['source_document_page'] > 0 ? (int) $values['source_document_page'] : null,
        $verifiedAt,
        !empty($values['active']) ? 1 : 0,
        now_sql(),
        $sequence,
    ]);
    return $stmt->rowCount() >= 0;
}

/** Return compact subject/card metadata for diagnostics and the public home page. */
function question_bank_summary(): array
{
    $generatedChoiceSets = 0;
    $explained = 0;
    $verified = 0;
    $sourceExact = 0;
    try {
        $generatedChoiceSets = (int) db()->query('SELECT COUNT(*) FROM questions WHERE choices_generated = 1')->fetchColumn();
    } catch (\Throwable) {
        // Keep the dashboard available while an existing installation is waiting for migration 2.
    }
    try {
        $explained = (int) db()->query("SELECT COUNT(*) FROM questions WHERE curation_status IN ('explained','verified')")->fetchColumn();
        $verified = (int) db()->query("SELECT COUNT(*) FROM questions WHERE curation_status = 'verified'")->fetchColumn();
        $sourceExact = (int) db()->query("SELECT COUNT(*) FROM questions WHERE wording_status = 'source_exact'")->fetchColumn();
    } catch (\Throwable) {
        // Keep the dashboard available while an existing installation is waiting for migration 4.
    }

    return [
        'subjects' => (int) db()->query('SELECT COUNT(*) FROM subjects')->fetchColumn(),
        'active_subjects' => (int) db()->query('SELECT COUNT(*) FROM subjects WHERE active = 1')->fetchColumn(),
        'questions' => (int) db()->query('SELECT COUNT(*) FROM questions')->fetchColumn(),
        'active_questions' => (int) db()->query('SELECT COUNT(*) FROM questions WHERE active = 1')->fetchColumn(),
        'attachments' => (int) db()->query("SELECT COUNT(*) FROM questions WHERE attachment_code IS NOT NULL AND attachment_code <> ''")->fetchColumn(),
        'merged_cards' => (int) db()->query('SELECT COUNT(*) FROM questions WHERE merged = 1')->fetchColumn(),
        'generated_choice_sets' => $generatedChoiceSets,
        'explained_questions' => $explained,
        'verified_questions' => $verified,
        'source_exact_questions' => $sourceExact,
    ];
}
