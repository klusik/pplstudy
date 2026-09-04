<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_dashboard.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Renders administrator summary metrics and recent audit events.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_admin;
use function PplStudy\Services\admin_log_recent;
use function PplStudy\Services\app_setting;
use function PplStudy\Services\question_bank_summary;
use function PplStudy\Services\subjects_all;
use function PplStudy\Services\users_summary;
use function PplStudy\Views\render_admin_dashboard;

/** Handle the administrator dashboard route. */
function admin_dashboard(): void
{
    require_admin();
    $meta = [
        'bank_name' => app_setting('bank_name'),
        'bank_compilation_version' => app_setting('bank_compilation_version'),
        'bank_compiled_at' => app_setting('bank_compiled_at'),
    ];
    render_admin_dashboard(question_bank_summary(), users_summary(), subjects_all(true), admin_log_recent(20), $meta);
}
