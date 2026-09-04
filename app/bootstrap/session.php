<?php

/**
 * Project: PPL Study
 * File: app/bootstrap/session.php
 * Module Type: Bootstrap Module
 *
 * Purpose:
 *   Starts the hardened PHP session used for learner authentication,
 *   administrator authorization, and CSRF protection.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Browser-local study progress remains separate from account identity.
 *   - Existing 0.2.x admin_session_name configuration stays compatible.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

/** Start the application session once. */
function study_start_session(array $config): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $configuredName = (string) ($config['session_name'] ?? $config['admin_session_name'] ?? 'ppl_study_session');
    $name = preg_replace('/[^A-Za-z0-9_-]/', '', $configuredName) ?: 'ppl_study_session';
    session_name($name);
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();

    if (empty($_SESSION['user_id']) && !empty($_SESSION['admin_user_id'])) {
        $_SESSION['user_id'] = (int) $_SESSION['admin_user_id'];
        $_SESSION['user_username'] = (string) ($_SESSION['admin_username'] ?? '');
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_must_change_password'] = 0;
        $_SESSION['user_authenticated_at'] = (int) ($_SESSION['admin_authenticated_at'] ?? time());
        $_SESSION['user_last_activity'] = (int) ($_SESSION['admin_last_activity'] ?? time());
    }

    $now = time();
    $last = (int) ($_SESSION['user_last_activity'] ?? 0);
    if (!empty($_SESSION['user_id']) && $last > 0 && ($now - $last) > study_limit('session_lifetime_seconds')) {
        clear_authenticated_session();
        session_regenerate_id(true);
    }
    if (!empty($_SESSION['user_id'])) {
        $_SESSION['user_last_activity'] = $now;
    }
}
