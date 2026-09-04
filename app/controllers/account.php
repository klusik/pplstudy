<?php

/**
 * Project: PPL Study
 * File: app/controllers/account.php
 * Module Type: Account Controller
 *
 * Purpose:
 *   Renders the authenticated learner profile and permanent-password setup.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - First-login password setup is mandatory before study content is opened.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use RuntimeException;
use function PplStudy\Core\consume_user_return_to;
use function PplStudy\Core\current_user;
use function PplStudy\Core\require_user;
use function PplStudy\Core\study_url;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\user_set_own_password;
use function PplStudy\Views\render_account;
use function PplStudy\Views\render_password_setup;

/** Render the authenticated user's profile. */
function account_profile(): void
{
    $user = require_user();
    $notice = (string) ($_SESSION['account_notice'] ?? '');
    unset($_SESSION['account_notice']);
    render_account($user, $notice);
}

/** Complete first-login password setup or change an existing password. */
function account_password(): void
{
    $user = require_user(true);
    $forced = !empty($user['must_change_password']);
    $message = '';
    $error = '';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $newPasswordAgain = (string) ($_POST['new_password_again'] ?? '');
            if (!hash_equals($newPassword, $newPasswordAgain)) {
                throw new RuntimeException('Zadej stejné nové heslo do obou polí.');
            }
            user_set_own_password((int) $user['id'], $currentPassword, $newPassword, !$forced);
            $user = current_user(true) ?? $user;
            if ($forced) {
                $target = consume_user_return_to('/');
                header('Location: ' . study_url($target), true, 302);
                exit;
            }
            $message = 'Heslo bylo změněno.';
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }

    render_password_setup($user, $forced, $message, $error);
}
