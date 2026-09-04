<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_users.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Provides administrator-only learner account creation, role management,
 *   activation, and one-time password reset.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Public self-registration is intentionally absent.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use RuntimeException;
use function PplStudy\Core\current_user_id;
use function PplStudy\Core\require_admin;
use function PplStudy\Core\study_url;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Services\user_admin_update;
use function PplStudy\Services\user_by_id;
use function PplStudy\Services\user_create;
use function PplStudy\Services\user_reset_temporary_password;
use function PplStudy\Services\users_all;
use function PplStudy\Views\render_admin_user_edit;
use function PplStudy\Views\render_admin_user_new;
use function PplStudy\Views\render_admin_users;
use function PplStudy\Views\render_not_found;

/** Render all managed learner accounts. */
function admin_users(): void
{
    require_admin();
    $error = '';
    $rows = [];
    try {
        $rows = users_all();
    } catch (\Throwable $exception) {
        $error = $exception->getMessage();
    }
    render_admin_users($rows, $error);
}

/** Create one invitation-only learner or administrator account. */
function admin_user_new(): void
{
    $admin = require_admin();
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $password = (string) ($_POST['temporary_password'] ?? '');
            $passwordAgain = (string) ($_POST['temporary_password_again'] ?? '');
            if (!hash_equals($password, $passwordAgain)) {
                throw new RuntimeException('Zadej stejné dočasné heslo do obou polí.');
            }
            $newId = user_create(
                (string) ($_POST['username'] ?? ''),
                (string) ($_POST['display_name'] ?? ''),
                (string) ($_POST['role'] ?? 'user'),
                $password,
                (int) $admin['id']
            );
            admin_log('user.create', 'Created user account.', ['user_id' => $newId, 'role' => (string) ($_POST['role'] ?? 'user')]);
            header('Location: ' . study_url('/admin/user/' . $newId . '?created=1'), true, 302);
            exit;
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
    render_admin_user_new($error);
}

/** Edit one learner profile or assign a fresh one-time password. */
function admin_user_edit(): void
{
    $admin = require_admin();
    $userId = (int) ($_GET['user_id'] ?? 0);
    try {
        $user = $userId > 0 ? user_by_id($userId) : null;
    } catch (\Throwable $exception) {
        render_admin_users([], $exception->getMessage());
        return;
    }
    if (!$user) {
        render_not_found();
        return;
    }

    $message = isset($_GET['created']) ? 'Uživatelský profil byl vytvořen. Dočasné heslo platí pro první přihlášení.' : '';
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $action = (string) ($_POST['action'] ?? 'save');
            if ($action === 'save') {
                user_admin_update(
                    $userId,
                    (string) ($_POST['display_name'] ?? ''),
                    (string) ($_POST['role'] ?? 'user'),
                    isset($_POST['active']),
                    (int) $admin['id']
                );
                admin_log('user.update', 'Updated user account.', ['user_id' => $userId, 'role' => (string) ($_POST['role'] ?? 'user'), 'active' => isset($_POST['active'])]);
                $message = 'Uživatelský profil byl uložen.';
            } elseif ($action === 'reset_password') {
                $password = (string) ($_POST['temporary_password'] ?? '');
                $passwordAgain = (string) ($_POST['temporary_password_again'] ?? '');
                if (!hash_equals($password, $passwordAgain)) {
                    throw new RuntimeException('Zadej stejné dočasné heslo do obou polí.');
                }
                user_reset_temporary_password($userId, $password);
                admin_log('user.password_reset', 'Assigned one-time password to user account.', ['user_id' => $userId]);
                $message = 'Nové dočasné heslo bylo nastaveno. Uživatel si po přihlášení vytvoří vlastní stálé heslo.';
            } else {
                throw new RuntimeException('Vyber platnou správní akci.');
            }
            $user = user_by_id($userId) ?? $user;
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
    render_admin_user_edit($user, (int) $admin['id'], $message, $error);
}
