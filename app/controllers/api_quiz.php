<?php

/**
 * Project: PPL Study
 * File: app/controllers/api_quiz.php
 * Module Type: Public API Controller
 *
 * Purpose:
 *   Returns randomized custom drills or subject paper drills as JSON for the
 *   first-party browser client.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Answers are not secret content in this study application. The API returns
 *     them with the drill and the browser reveals them only on learner action.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\json_response;
use function PplStudy\Core\require_user_json;
use function PplStudy\Core\study_limit;
use function PplStudy\Services\quiz_custom;
use function PplStudy\Services\quiz_subject_paper;
use function PplStudy\Services\subject_by_id;

/** Handle the public drill JSON endpoint. */
function api_quiz(): void
{
    require_user_json();
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
    }
    $mode = (string) ($_GET['mode'] ?? 'custom');
    $rawSubject = (string) ($_GET['subject'] ?? 'all');
    $subjectId = ($rawSubject !== 'all' && ctype_digit($rawSubject)) ? (int) $rawSubject : null;

    if ($subjectId !== null && !subject_by_id($subjectId)) {
        json_response(['ok' => false, 'error' => 'Unknown subject.'], 404);
    }

    if ($mode === 'paper') {
        if ($subjectId === null) {
            json_response(['ok' => false, 'error' => 'Paper drill requires one subject.'], 422);
        }
        $payload = quiz_subject_paper($subjectId);
        if (!$payload) {
            json_response(['ok' => false, 'error' => 'Subject is unavailable.'], 404);
        }
    } else {
        $count = max(1, min(study_limit('quiz_custom_max_questions'), (int) ($_GET['count'] ?? 20)));
        $payload = quiz_custom($subjectId, $count);
    }

    json_response(['ok' => true, 'drill' => $payload]);
}
