<?php

/**
 * Project: PPL Study
 * File: app/controllers/public_study.php
 * Module Type: Public Controller
 *
 * Purpose:
 *   Handles study setup, random-card selection, and direct card display.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_user;

use function PplStudy\Core\study_url;
use function PplStudy\Services\question_by_sequence;
use function PplStudy\Services\question_neighbor_sequence;
use function PplStudy\Services\random_question;
use function PplStudy\Services\subject_by_id;
use function PplStudy\Services\subjects_all;
use function PplStudy\Views\render_not_found;
use function PplStudy\Views\render_question_card;
use function PplStudy\Views\render_study_setup;

/** Render setup or redirect to a random card when a subject is selected. */
function public_study(): void
{
    require_user();
    if (!isset($_GET['subject']) || (string) $_GET['subject'] === '') {
        render_study_setup(subjects_all());
        return;
    }
    $raw = (string) $_GET['subject'];
    $subjectId = null;
    if ($raw !== 'all') {
        $subjectId = (int) $raw;
        if ($subjectId < 1 || !subject_by_id($subjectId)) {
            render_not_found();
            return;
        }
    }
    $question = random_question($subjectId);
    if (!$question) {
        render_not_found();
        return;
    }
    $mode = (string) ($_GET['mode'] ?? 'quiz') === 'learn' ? 'learn' : 'quiz';
    header('Location: ' . study_url('/question/' . (int) $question['sequence_no'] . '?mode=' . $mode), true, 302);
    exit;
}

/** Render one card by sequence number. */
function public_question(): void
{
    require_user();
    $sequence = (int) ($_GET['sequence'] ?? 0);
    $question = $sequence > 0 ? question_by_sequence($sequence) : null;
    if (!$question) {
        render_not_found();
        return;
    }
    $previous = question_neighbor_sequence((int) $question['subject_id'], $sequence, 'previous');
    $next = question_neighbor_sequence((int) $question['subject_id'], $sequence, 'next');
    $mode = (string) ($_GET['mode'] ?? 'quiz') === 'learn' ? 'learn' : 'quiz';
    render_question_card($question, $previous, $next, $mode);
}
