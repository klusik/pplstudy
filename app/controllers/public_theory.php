<?php

/**
 * Project: PPL Study
 * File: app/controllers/public_theory.php
 * Module Type: Authenticated study controller
 * Purpose: Dispatch only registered course and chapter slugs.
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_user;
use function PplStudy\Services\subject_by_id;
use function PplStudy\Services\theory_course;
use function PplStudy\Views\render_not_found;
use function PplStudy\Views\render_theory_course;

/** Render a course overview or an individual registered chapter. */
function public_theory(): void
{
    require_user();
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        return;
    }
    $slug = $_GET['course'] ?? '';
    $chapterSlug = $_GET['chapter'] ?? '';
    if (!is_string($slug) || !is_string($chapterSlug)) {
        render_not_found();
        return;
    }
    $course = theory_course($slug);
    if ($course === null || ($chapterSlug !== '' && !isset($course['chapters'][$chapterSlug]))) {
        render_not_found();
        return;
    }
    $subject = subject_by_id($course['subject_id']);
    if (!$subject || empty($subject['active'])) {
        render_not_found();
        return;
    }
    render_theory_course($course, $chapterSlug);
}
