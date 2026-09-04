<?php

/**
 * Project: PPL Study
 * File: app/security.php
 * Module Type: Security Core
 *
 * Purpose:
 *   Centralizes learner authentication, administrator authorization, CSRF
 *   protection, first-login password enforcement, and login throttling.
 *
 * Responsibilities:
 *   - Maintain the authenticated user session identity
 *   - Re-read role and active state from the database on each request
 *   - Require permanent-password setup after administrator-issued login
 *   - Create and verify per-session CSRF tokens
 *   - Rate-limit repeated failed logins by client address
 *   - Regenerate session identifiers on authentication state changes
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - The legacy admin_login_attempts table remains the login throttle ledger.
 *   - Compatibility helpers allow an uploaded release to reach System Health
 *     before migration 202609030003_user_accounts has been applied.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

use PDO;
use RuntimeException;

/** Return the best-effort direct client address used by the login throttle. */
function client_ip(): string
{
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    return substr($ip !== '' ? $ip : 'unknown', 0, 64);
}

/** Return true when the expanded learner-account columns are available. */
function user_account_schema_ready(?PDO $pdo = null, bool $refresh = false): bool
{
    static $cached = null;
    if (!$refresh && is_bool($cached)) {
        return $cached;
    }
    try {
        $pdo ??= db();
        $columns = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
        $present = array_fill_keys(array_map('strval', $columns), true);
        foreach (['display_name', 'must_change_password', 'temporary_password_consumed_at', 'active', 'created_by_user_id', 'last_login_at', 'password_changed_at'] as $required) {
            if (!isset($present[$required])) {
                $cached = false;
                return false;
            }
        }
        $cached = true;
        return true;
    } catch (\Throwable) {
        $cached = false;
        return false;
    }
}

/** Remove all authenticated identity fields from the current PHP session. */
function clear_authenticated_session(): void
{
    unset(
        $_SESSION['user_id'],
        $_SESSION['user_username'],
        $_SESSION['user_role'],
        $_SESSION['user_must_change_password'],
        $_SESSION['user_authenticated_at'],
        $_SESSION['user_last_activity'],
        $_SESSION['admin_user_id'],
        $_SESSION['admin_username'],
        $_SESSION['admin_authenticated_at'],
        $_SESSION['admin_last_activity'],
        $_SESSION['csrf_token']
    );
}

/** Return the current database-backed user or null. */
function current_user(bool $refresh = false): ?array
{
    static $cachedUser = null;
    static $cachedId = null;

    $sessionId = (int) ($_SESSION['user_id'] ?? $_SESSION['admin_user_id'] ?? 0);
    if ($sessionId <= 0) {
        $cachedUser = null;
        $cachedId = null;
        return null;
    }
    if (!$refresh && $cachedId === $sessionId && is_array($cachedUser)) {
        return $cachedUser;
    }

    $pdo = db();
    if (user_account_schema_ready($pdo)) {
        $stmt = $pdo->prepare('SELECT id, username, display_name, role, must_change_password, temporary_password_consumed_at, active, last_login_at, password_changed_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$sessionId]);
        $user = $stmt->fetch();
        if (!is_array($user) || empty($user['active'])) {
            clear_authenticated_session();
            $cachedUser = null;
            $cachedId = null;
            return null;
        }
    } else {
        $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$sessionId]);
        $legacy = $stmt->fetch();
        if (!is_array($legacy)) {
            clear_authenticated_session();
            $cachedUser = null;
            $cachedId = null;
            return null;
        }
        $user = [
            'id' => (int) $legacy['id'],
            'username' => (string) $legacy['username'],
            'display_name' => (string) $legacy['username'],
            'role' => 'admin',
            'must_change_password' => 0,
            'temporary_password_consumed_at' => null,
            'active' => 1,
            'last_login_at' => null,
            'password_changed_at' => null,
        ];
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_username'] = (string) $user['username'];
    $_SESSION['user_role'] = (string) $user['role'];
    $_SESSION['user_must_change_password'] = !empty($user['must_change_password']) ? 1 : 0;
    unset($_SESSION['admin_user_id'], $_SESSION['admin_username'], $_SESSION['admin_authenticated_at'], $_SESSION['admin_last_activity']);

    $cachedUser = $user;
    $cachedId = (int) $user['id'];
    return $cachedUser;
}

/** Return the authenticated user ID or null. */
function current_user_id(): ?int
{
    $user = current_user();
    return $user ? (int) $user['id'] : null;
}

/** Return the current administrator user ID or null. */
function current_admin_id(): ?int
{
    $user = current_user();
    return $user && (string) $user['role'] === 'admin' ? (int) $user['id'] : null;
}

/** Return true when any active user is authenticated. */
function is_authenticated(): bool
{
    return current_user() !== null;
}

/** Return true when an active administrator is authenticated. */
function is_admin(): bool
{
    $user = current_user();
    return $user !== null && (string) $user['role'] === 'admin';
}

/** Return true when the authenticated account still requires permanent setup. */
function password_change_required(): bool
{
    $user = current_user();
    return $user !== null && !empty($user['must_change_password']);
}

/** Remember one safe application-relative return target. */
function remember_user_return_to(?string $path = null): void
{
    $path ??= study_request_path();
    if ($path === '/login' || $path === '/logout' || $path === '/account/password' || $path === '/install.php') {
        return;
    }
    $query = trim((string) ($_SERVER['QUERY_STRING'] ?? ''));
    if ($query !== '' && !str_contains($path, '?')) {
        $path .= '?' . substr($query, 0, 800);
    }
    if (str_starts_with($path, '/') && !str_starts_with($path, '//')) {
        $_SESSION['user_return_to'] = substr($path, 0, 1000);
    }
}

/** Consume a previously stored safe application-relative return target. */
function consume_user_return_to(string $fallback = '/'): string
{
    $target = (string) ($_SESSION['user_return_to'] ?? '');
    unset($_SESSION['user_return_to']);
    if ($target === '' || !str_starts_with($target, '/') || str_starts_with($target, '//')) {
        return $fallback;
    }

    $parts = parse_url($target);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }
    $path = '/' . trim((string) ($parts['path'] ?? '/'), '/');
    $path = $path === '/' ? '/' : rtrim($path, '/');

    $basePath = study_base_path();
    if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
        $path = substr($path, strlen($basePath));
        $path = '/' . trim($path, '/');
    }

    $scriptDirectory = study_script_directory();
    if ($scriptDirectory !== '' && ($path === $scriptDirectory || str_starts_with($path, $scriptDirectory . '/'))) {
        $path = substr($path, strlen($scriptDirectory));
        $path = '/' . trim($path, '/');
        if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            $path = substr($path, strlen($basePath));
            $path = '/' . trim($path, '/');
        }
    }

    if (in_array($path, ['/login', '/logout', '/account/password', '/install.php'], true)) {
        return $fallback;
    }

    $query = isset($parts['query']) && (string) $parts['query'] !== '' ? '?' . (string) $parts['query'] : '';
    return ($path === '' ? '/' : $path) . $query;
}

/** Require an authenticated user with a completed permanent password. */
function require_user(bool $allowPasswordSetup = false): array
{
    $user = current_user();
    if (!$user) {
        remember_user_return_to();
        header('Location: ' . study_url('/login'), true, 302);
        exit;
    }
    header('Cache-Control: private, no-store');
    header('Vary: Cookie');
    header('X-Robots-Tag: noindex, nofollow');
    if (!$allowPasswordSetup && !empty($user['must_change_password'])) {
        remember_user_return_to();
        header('Location: ' . study_url('/account/password'), true, 302);
        exit;
    }
    return $user;
}

/** Require an authenticated administrator or redirect to the account page. */
function require_admin(): array
{
    $user = require_user();
    if ((string) $user['role'] === 'admin') {
        return $user;
    }
    $_SESSION['account_notice'] = 'Administrace je dostupná administrátorům.';
    header('Location: ' . study_url('/account'), true, 302);
    exit;
}

/** Require a completed learner session for a JSON endpoint. */
function require_user_json(): array
{
    $user = current_user();
    if (!$user) {
        json_response(['ok' => false, 'error' => 'Přihlášení je vyžadováno.', 'login_url' => study_url('/login')], 401);
    }
    if (!empty($user['must_change_password'])) {
        json_response(['ok' => false, 'error' => 'Dokonči nastavení stálého hesla.', 'password_url' => study_url('/account/password')], 428);
    }
    return $user;
}

/** Return the stable CSRF token for the active session. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Return a ready-to-render hidden CSRF input. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify the posted CSRF token or stop the request with HTTP 403. */
function verify_csrf(): void
{
    $posted = (string) ($_POST['csrf_token'] ?? '');
    if ($posted === '' || !hash_equals(csrf_token(), $posted)) {
        http_response_code(403);
        throw new RuntimeException('Invalid CSRF token. Reload the page and try again.');
    }
}

/** Remove expired login-attempt rows before calculating a throttle decision. */
function prune_login_attempts(PDO $pdo): void
{
    $cutoff = gmdate('Y-m-d H:i:s', time() - study_limit('login_window_seconds'));
    $stmt = $pdo->prepare('DELETE FROM admin_login_attempts WHERE attempted_at < ?');
    $stmt->execute([$cutoff]);
}

/** Return true when the current client has exceeded the failed-login threshold. */
function login_is_throttled(PDO $pdo, string $ip): bool
{
    prune_login_attempts($pdo);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_login_attempts WHERE client_ip = ? AND success = 0');
    $stmt->execute([$ip]);
    return (int) $stmt->fetchColumn() >= study_limit('login_max_failures');
}

/** Record one login attempt. */
function record_login_attempt(PDO $pdo, string $ip, string $username, bool $success): void
{
    $stmt = $pdo->prepare('INSERT INTO admin_login_attempts (client_ip, username, success, attempted_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$ip, substr($username, 0, 190), $success ? 1 : 0, now_sql()]);
}

/** Authenticate an active username/password pair. */
function authenticate_user(string $username, string $password): bool
{
    $pdo = db();
    $ip = client_ip();
    if (login_is_throttled($pdo, $ip)) {
        return false;
    }

    $profileSchema = user_account_schema_ready($pdo);
    if ($profileSchema) {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role, must_change_password, temporary_password_consumed_at, active FROM users WHERE username = ? LIMIT 1');
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, 0 AS must_change_password, NULL AS temporary_password_consumed_at, 1 AS active FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
    }
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $ok = is_array($user)
        && !empty($user['active'])
        && password_verify($password, (string) $user['password_hash'])
        && (empty($user['must_change_password']) || empty($user['temporary_password_consumed_at']));

    if ($ok && $profileSchema) {
        $now = now_sql();
        if (!empty($user['must_change_password'])) {
            $claim = $pdo->prepare('UPDATE users SET temporary_password_consumed_at = ?, last_login_at = ?, updated_at = ? WHERE id = ? AND active = 1 AND must_change_password = 1 AND temporary_password_consumed_at IS NULL');
            $claim->execute([$now, $now, $now, (int) $user['id']]);
            $ok = $claim->rowCount() === 1;
        } else {
            $touch = $pdo->prepare('UPDATE users SET last_login_at = ?, updated_at = ? WHERE id = ? AND active = 1');
            $touch->execute([$now, $now, (int) $user['id']]);
        }
    }

    record_login_attempt($pdo, $ip, $username, $ok);
    if (!$ok) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_username'] = (string) $user['username'];
    $_SESSION['user_role'] = (string) $user['role'];
    $_SESSION['user_must_change_password'] = !empty($user['must_change_password']) ? 1 : 0;
    $_SESSION['user_authenticated_at'] = time();
    $_SESSION['user_last_activity'] = time();
    unset($_SESSION['csrf_token'], $_SESSION['admin_user_id'], $_SESSION['admin_username'], $_SESSION['admin_authenticated_at'], $_SESSION['admin_last_activity']);

    current_user(true);
    return true;
}

/** End the current authenticated session while preserving unrelated PHP state. */
function logout_user(): void
{
    clear_authenticated_session();
    unset($_SESSION['user_return_to'], $_SESSION['account_notice']);
    session_regenerate_id(true);
}

/** Compatibility wrapper for older controller call sites. */
function authenticate_admin(string $username, string $password): bool
{
    if (!authenticate_user($username, $password)) {
        return false;
    }
    if (is_admin()) {
        return true;
    }
    logout_user();
    return false;
}

/** Compatibility wrapper for older controller call sites. */
function logout_admin(): void
{
    logout_user();
}
