<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_questions.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Lists and edits study cards while keeping database mutations inside the
 *   question-bank service.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use RuntimeException;
use function PplStudy\Core\require_admin;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Services\admin_question_page;
use function PplStudy\Services\question_by_sequence;
use function PplStudy\Services\subject_by_id;
use function PplStudy\Services\subjects_all;
use function PplStudy\Services\update_question;
use function PplStudy\Views\render_admin_question_edit;
use function PplStudy\Views\render_admin_questions;
use function PplStudy\Views\render_not_found;

/** Render the filtered administrator card list. */
function admin_questions(): void
{
    require_admin();
    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query, 'UTF-8') > study_limit('question_search_max_length')) {
        $query = mb_substr($query, 0, study_limit('question_search_max_length'), 'UTF-8');
    }
    $subjectId = isset($_GET['subject']) && ctype_digit((string) $_GET['subject']) ? (int) $_GET['subject'] : null;
    $page = max(1, (int) ($_GET['p'] ?? 1));
    render_admin_questions(subjects_all(true), admin_question_page($query, $subjectId, $page), $query, $subjectId);
}

/** Edit one card. */
function admin_question_edit(): void
{
    require_admin();
    $sequence = (int) ($_GET['sequence'] ?? 0);
    $question = $sequence > 0 ? question_by_sequence($sequence, true) : null;
    if (!$question) {
        render_not_found();
        return;
    }
    $message = '';
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $subjectId = (int) ($_POST['subject_id'] ?? 0);
            if (!$subjectId || !subject_by_id($subjectId)) {
                throw new RuntimeException('Vybraný předmět neexistuje.');
            }
            foreach (['source_trace', 'prompt', 'answer', 'explanation'] as $field) {
                if (trim((string) ($_POST[$field] ?? '')) === '') {
                    throw new RuntimeException('Pole ' . $field . ' nesmí být prázdné.');
                }
            }
            $answer = trim((string) $_POST['answer']);
            $choices = [];
            $choiceKeys = [];
            for ($choiceIndex = 1; $choiceIndex <= 4; $choiceIndex++) {
                $choice = trim((string) ($_POST['choice_' . $choiceIndex] ?? ''));
                if ($choice === '') {
                    throw new RuntimeException('Všechny čtyři možnosti odpovědi musí být vyplněné.');
                }
                $normalizedChoice = strtolower(preg_replace('/\s+/u', ' ', $choice) ?? $choice);
                if (isset($choiceKeys[$normalizedChoice])) {
                    throw new RuntimeException('Možnosti odpovědi musí být vzájemně odlišné.');
                }
                $choiceKeys[$normalizedChoice] = true;
                $choices[] = $choice;
            }
            if (count(array_filter($choices, static fn (string $choice): bool => $choice === $answer)) !== 1) {
                throw new RuntimeException('Správná odpověď musí být přesně jednou ze čtyř možností.');
            }
            update_question($sequence, [
                'subject_id' => $subjectId,
                'title' => (string) ($_POST['title'] ?? ''),
                'source_trace' => (string) $_POST['source_trace'],
                'prompt' => (string) $_POST['prompt'],
                'answer' => $answer,
                'choices' => $choices,
                'explanation' => (string) $_POST['explanation'],
                'warning' => (string) ($_POST['warning'] ?? ''),
                'attachment_code' => (string) ($_POST['attachment_code'] ?? ''),
                'attachment_url' => (string) ($_POST['attachment_url'] ?? ''),
                'active' => isset($_POST['active']),
            ]);
            admin_log('question.update', 'Updated study card #' . $sequence . '.', ['sequence_no' => $sequence]);
            $message = 'Karta byla uložena.';
            $question = question_by_sequence($sequence, true) ?? $question;
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
    render_admin_question_edit($question, subjects_all(true), $message, $error);
}
