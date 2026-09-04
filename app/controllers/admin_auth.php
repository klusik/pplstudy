<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_auth.php
 * Module Type: Authentication Controller
 *
 * Purpose:
 *   Handles shared learner and administrator login and logout at the HTTP
 *   boundary while preserving legacy /admin/login and /admin/logout routes.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Account creation remains administrator-only.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\authenticate_user;
use function PplStudy\Core\consume_user_return_to;
use function PplStudy\Core\csrf_token;
use function PplStudy\Core\current_user;
use function PplStudy\Core\is_admin;
use function PplStudy\Core\is_authenticated;
use function PplStudy\Core\logout_user;
use function PplStudy\Core\password_change_required;
use function PplStudy\Core\study_request_path;
use function PplStudy\Core\study_url;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Views\render_login;

/** Handle learner and administrator login. */
function user_login(): void
{
    if (is_authenticated()) {
        if (password_change_required()) {
            header('Location: ' . study_url('/account/password'), true, 302);
            exit;
        }
        $target = is_admin() && str_starts_with(study_request_path(), '/admin') ? '/admin' : '/';
        header('Location: ' . study_url($target), true, 302);
        exit;
    }

    $error = '';
    csrf_token();
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($username === '' || $password === '' || !authenticate_user($username, $password)) {
            $error = 'Přihlášení se nezdařilo. Zkontroluj údaje nebo vyčkej po více neúspěšných pokusech.';
        } else {
            $user = current_user(true);
            if ($user && (string) $user['role'] === 'admin') {
                admin_log('auth.login', 'Administrator logged in.');
            }
            if ($user && !empty($user['must_change_password'])) {
                header('Location: ' . study_url('/account/password'), true, 302);
                exit;
            }
            $target = consume_user_return_to('/');
            header('Location: ' . study_url($target), true, 302);
            exit;
        }
    }
    render_login($error);
}

/** Handle logout for every authenticated account. */
function user_logout(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        return;
    }
    verify_csrf();
    if (is_admin()) {
        admin_log('auth.logout', 'Administrator logged out.');
    }
    logout_user();
    header('Location: ' . study_url('/'), true, 302);
    exit;
}

/** Legacy route wrapper. */
function admin_login(): void
{
    user_login();
}

/** Legacy route wrapper. */
function admin_logout(): void
{
    user_logout();
}
