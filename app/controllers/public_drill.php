<?php

/**
 * Project: PPL Study
 * File: app/controllers/public_drill.php
 * Module Type: Public Controller
 *
 * Purpose:
 *   Renders the client-driven custom and paper drill interface.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_user;

use function PplStudy\Core\clamp_int;
use function PplStudy\Services\app_setting;
use function PplStudy\Services\subject_by_id;
use function PplStudy\Services\subjects_all;
use function PplStudy\Views\render_drill;

/** Handle the drill route. */
function public_drill(): void
{
    require_user();
    $subjectId = isset($_GET['subject']) && ctype_digit((string) $_GET['subject']) ? (int) $_GET['subject'] : null;
    if ($subjectId !== null && !subject_by_id($subjectId)) {
        $subjectId = null;
    }
    $mode = (string) ($_GET['mode'] ?? 'custom');
    if (!in_array($mode, ['custom', 'paper'], true)) {
        $mode = 'custom';
    }
    $defaultCount = clamp_int((int) app_setting('default_drill_count'), 1, 120);
    render_drill(subjects_all(), $subjectId, $mode, $defaultCount);
}
