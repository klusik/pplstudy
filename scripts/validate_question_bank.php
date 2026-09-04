<?php

/**
 * Project: PPL Study
 * File: scripts/validate_question_bank.php
 * Module Type: Development Utility
 *
 * Purpose:
 *   Validate a question-bank JSON payload without requiring a database.
 *
 * Usage:
 *   php scripts/validate_question_bank.php [path/to/question-bank.json]
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$path = $argv[1] ?? ($root . '/data/question-bank.json');
require_once $root . '/app/services/importer.php';

try {
    $payload = \PplStudy\Services\question_bank_load_file($path);
    $summary = \PplStudy\Services\question_bank_validate_payload($payload);
    fwrite(STDOUT, "Question bank valid\n");
    fwrite(STDOUT, 'Path: ' . $path . "\n");
    fwrite(STDOUT, 'Subjects: ' . $summary['subjects'] . "\n");
    fwrite(STDOUT, 'Questions: ' . $summary['questions'] . "\n");
    fwrite(STDOUT, 'Attachment references: ' . $summary['attachments'] . "\n");
    fwrite(STDOUT, 'Generated choice sets: ' . $summary['generated_choices'] . "\n");
    fwrite(STDOUT, 'Explained questions: ' . ($summary['explained'] ?? 0) . "\n");
    fwrite(STDOUT, 'Verified questions: ' . ($summary['verified'] ?? 0) . "\n");
    fwrite(STDOUT, 'Source-exact questions: ' . ($summary['source_exact'] ?? 0) . "\n");
    foreach ($summary['counts_by_subject'] as $subjectId => $count) {
        fwrite(STDOUT, 'Subject ' . $subjectId . ': ' . $count . "\n");
    }
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Question bank INVALID: ' . $exception->getMessage() . "\n");
    exit(1);
}
