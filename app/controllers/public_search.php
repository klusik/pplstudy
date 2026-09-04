<?php

/**
 * Project: PPL Study
 * File: app/controllers/public_search.php
 * Module Type: Public Controller
 *
 * Purpose:
 *   Searches the active question bank by content, card number, source trace,
 *   or attachment code.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_user;

use function PplStudy\Core\study_limit;
use function PplStudy\Services\search_questions;
use function PplStudy\Services\subjects_all;
use function PplStudy\Views\render_search;

/** Handle the public search route. */
function public_search(): void
{
    require_user();
    $query = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($query, 'UTF-8') > study_limit('question_search_max_length')) {
        $query = mb_substr($query, 0, study_limit('question_search_max_length'), 'UTF-8');
    }
    $subjectId = isset($_GET['subject']) && ctype_digit((string) $_GET['subject']) ? (int) $_GET['subject'] : null;
    $results = ($query !== '' || $subjectId !== null) ? search_questions($query, $subjectId) : [];
    render_search(subjects_all(), $query, $subjectId, $results);
}
