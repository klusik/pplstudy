<?php

/**
 * Project: PPL Study
 * File: app/services/users.php
 * Module Type: Service
 *
 * Purpose:
 *   Owns invitation-only learner account administration and password changes.
 *
 * Responsibilities:
 *   - List and read learner and administrator accounts
 *   - Create accounts with administrator-issued one-time passwords
 *   - Promote, demote, activate, and deactivate accounts safely
 *   - Reset an account to a new one-time password
 *   - Replace a one-time password with the learner's permanent password
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - There is intentionally no public registration path.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use RuntimeException;
use function PplStudy\Core\db;
use function PplStudy\Core\now_sql;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\user_account_schema_ready;

/** Require the learner-account schema before account administration. */
function require_user_account_schema(): void
{
    if (!user_account_schema_ready()) {
        throw new RuntimeException('Uživatelské profily vyžadují databázovou migraci 202609030003_user_accounts. Spusť ji v System Health.');
    }
}

/** Validate and normalize a login username. */
function normalize_account_username(string $username): string
{
    $username = trim($username);
    $length = mb_strlen($username, 'UTF-8');
    if ($length < 3 || $length > 190 || preg_match('/\s/u', $username) === 1) {
        throw new RuntimeException('Uživatelské jméno musí mít 3 až 190 znaků a tvořit jeden souvislý výraz.');
    }
    return $username;
}

/** Validate an account role. */
function normalize_account_role(string $role): string
{
    if (!in_array($role, ['user', 'admin'], true)) {
        throw new RuntimeException('Vyber roli Uživatel nebo Administrátor.');
    }
    return $role;
}

/** Validate a newly assigned password against the centralized minimum. */
function validate_account_password(string $password): void
{
    $minimum = study_limit('account_password_min_length');
    if (mb_strlen($password, 'UTF-8') < $minimum) {
        throw new RuntimeException('Heslo musí mít alespoň ' . $minimum . ' znaků.');
    }
}

/** Return all accounts for administrator management. */
function users_all(): array
{
    require_user_account_schema();
    return db()->query("SELECT u.id, u.username, u.display_name, u.role, u.must_change_password, u.temporary_password_consumed_at, u.active, u.last_login_at, u.password_changed_at, u.created_at, c.username AS created_by_username
        FROM users u
        LEFT JOIN users c ON c.id = u.created_by_user_id
        ORDER BY u.active DESC, u.role = 'admin' DESC, COALESCE(NULLIF(u.display_name, ''), u.username), u.id")->fetchAll();
}

/** Return one account by ID. */
function user_by_id(int $userId): ?array
{
    require_user_account_schema();
    $stmt = db()->prepare("SELECT u.id, u.username, u.display_name, u.password_hash, u.role, u.must_change_password, u.temporary_password_consumed_at, u.active, u.created_by_user_id, u.last_login_at, u.password_changed_at, u.created_at, u.updated_at, c.username AS created_by_username
        FROM users u
        LEFT JOIN users c ON c.id = u.created_by_user_id
        WHERE u.id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

/** Return account totals for the administrator dashboard. */
function users_summary(): array
{
    if (!user_account_schema_ready()) {
        return ['total' => 0, 'active' => 0, 'admins' => 0, 'password_setup_pending' => 0, 'schema_ready' => false];
    }
    $row = db()->query("SELECT COUNT(*) AS total,
        SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN active = 1 AND role = 'admin' THEN 1 ELSE 0 END) AS admins,
        SUM(CASE WHEN active = 1 AND must_change_password = 1 THEN 1 ELSE 0 END) AS password_setup_pending
        FROM users")->fetch();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'active' => (int) ($row['active'] ?? 0),
        'admins' => (int) ($row['admins'] ?? 0),
        'password_setup_pending' => (int) ($row['password_setup_pending'] ?? 0),
        'schema_ready' => true,
    ];
}

/** Create one invitation-only account with a one-time login password. */
function user_create(string $username, string $displayName, string $role, string $temporaryPassword, ?int $createdByUserId = null): int
{
    require_user_account_schema();
    $username = normalize_account_username($username);
    $displayName = mb_substr(trim($displayName), 0, 190, 'UTF-8');
    $role = normalize_account_role($role);
    validate_account_password($temporaryPassword);
    $now = now_sql();

    try {
        $stmt = db()->prepare("INSERT INTO users (username, display_name, password_hash, role, must_change_password, temporary_password_consumed_at, active, created_by_user_id, last_login_at, password_changed_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NULL, 1, ?, NULL, NULL, ?, ?)");
        $stmt->execute([$username, $displayName !== '' ? $displayName : $username, password_hash($temporaryPassword, PASSWORD_DEFAULT), $role, $createdByUserId, $now, $now]);
    } catch (\PDOException $exception) {
        if ((string) $exception->getCode() === '23000') {
            throw new RuntimeException('Toto uživatelské jméno už existuje.');
        }
        throw $exception;
    }
    return (int) db()->lastInsertId();
}

/** Count active administrators. */
function active_admin_count(): int
{
    require_user_account_schema();
    return (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND active = 1")->fetchColumn();
}

/** Update profile, role, and active state with lockout protection. */
function user_admin_update(int $userId, string $displayName, string $role, bool $active, int $actorUserId): void
{
    require_user_account_schema();
    $existing = user_by_id($userId);
    if (!$existing) {
        throw new RuntimeException('Uživatelský profil nebyl nalezen.');
    }
    $displayName = mb_substr(trim($displayName), 0, 190, 'UTF-8');
    $role = normalize_account_role($role);

    if ($userId === $actorUserId && ($role !== 'admin' || !$active)) {
        throw new RuntimeException('Vlastní administrátorský účet zůstává aktivní s rolí Administrátor.');
    }
    $removesActiveAdmin = (string) $existing['role'] === 'admin' && !empty($existing['active']) && ($role !== 'admin' || !$active);
    if ($removesActiveAdmin && active_admin_count() <= 1) {
        throw new RuntimeException('Alespoň jeden aktivní administrátor musí zůstat k dispozici.');
    }

    $stmt = db()->prepare('UPDATE users SET display_name = ?, role = ?, active = ?, updated_at = ? WHERE id = ?');
    $stmt->execute([$displayName !== '' ? $displayName : (string) $existing['username'], $role, $active ? 1 : 0, now_sql(), $userId]);
}

/** Assign a new administrator-issued one-time password. */
function user_reset_temporary_password(int $userId, string $temporaryPassword): void
{
    require_user_account_schema();
    validate_account_password($temporaryPassword);
    if (!user_by_id($userId)) {
        throw new RuntimeException('Uživatelský profil nebyl nalezen.');
    }
    $stmt = db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 1, temporary_password_consumed_at = NULL, password_changed_at = NULL, updated_at = ? WHERE id = ?');
    $stmt->execute([password_hash($temporaryPassword, PASSWORD_DEFAULT), now_sql(), $userId]);
}

/** Set the authenticated user's permanent password. */
function user_set_own_password(int $userId, string $currentPassword, string $newPassword, bool $currentPasswordRequired): void
{
    require_user_account_schema();
    validate_account_password($newPassword);
    $user = user_by_id($userId);
    if (!$user || empty($user['active'])) {
        throw new RuntimeException('Uživatelský profil není aktivní.');
    }
    if ($currentPasswordRequired && !password_verify($currentPassword, (string) $user['password_hash'])) {
        throw new RuntimeException('Aktuální heslo nesouhlasí.');
    }
    if (password_verify($newPassword, (string) $user['password_hash'])) {
        throw new RuntimeException('Pro nové stálé heslo zvol jinou hodnotu než současné nebo dočasné heslo.');
    }
    $stmt = db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 0, temporary_password_consumed_at = NULL, password_changed_at = ?, updated_at = ? WHERE id = ?');
    $now = now_sql();
    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $now, $now, $userId]);
}
