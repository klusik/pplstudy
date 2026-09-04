<?php

/**
 * Project: PPL Study
 * File: app/bootstrap/dispatch.php
 * Module Type: Bootstrap Module
 *
 * Purpose:
 *   Owns the explicit route-to-controller table.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

use function PplStudy\Controllers\account_password;
use function PplStudy\Controllers\account_profile;
use function PplStudy\Controllers\admin_dashboard;
use function PplStudy\Controllers\admin_diagnostics;
use function PplStudy\Controllers\admin_import;
use function PplStudy\Controllers\admin_question_edit;
use function PplStudy\Controllers\admin_questions;
use function PplStudy\Controllers\admin_settings;
use function PplStudy\Controllers\admin_user_edit;
use function PplStudy\Controllers\admin_user_new;
use function PplStudy\Controllers\admin_users;
use function PplStudy\Controllers\api_quiz;
use function PplStudy\Controllers\public_about;
use function PplStudy\Controllers\public_drill;
use function PplStudy\Controllers\public_home;
use function PplStudy\Controllers\public_question;
use function PplStudy\Controllers\public_search;
use function PplStudy\Controllers\public_study;
use function PplStudy\Controllers\user_login;
use function PplStudy\Controllers\user_logout;
use function PplStudy\Views\render_not_found;

/** Dispatch one resolved page identifier. */
function study_dispatch_page(string $page): void
{
    $routes = [
        'home' => public_home(...),
        'user_login' => user_login(...),
        'user_logout' => user_logout(...),
        'account_profile' => account_profile(...),
        'account_password' => account_password(...),
        'study' => public_study(...),
        'question' => public_question(...),
        'drill' => public_drill(...),
        'search' => public_search(...),
        'about' => public_about(...),
        'api_quiz' => api_quiz(...),
        'admin_dashboard' => admin_dashboard(...),
        'admin_users' => admin_users(...),
        'admin_user_new' => admin_user_new(...),
        'admin_user_edit' => admin_user_edit(...),
        'admin_questions' => admin_questions(...),
        'admin_question_edit' => admin_question_edit(...),
        'admin_import' => admin_import(...),
        'admin_settings' => admin_settings(...),
        'admin_diagnostics' => admin_diagnostics(...),
    ];
    if (!isset($routes[$page])) {
        render_not_found();
        return;
    }
    $routes[$page]();
}
