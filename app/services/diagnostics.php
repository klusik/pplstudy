<?php

/**
 * Project: PPL Study
 * File: app/services/diagnostics.php
 * Module Type: Service
 *
 * Purpose:
 *   Produces a read-only system-health report covering runtime prerequisites,
 *   database schema, bundled data integrity, and imported question counts.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Diagnostics must not expose database credentials or config secrets.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use PDO;
use Throwable;
use function PplStudy\Core\db;
use function PplStudy\Core\discover_migration_files;
use function PplStudy\Core\pending_migration_files;
use function PplStudy\Core\study_project_root;

/** Check whether an exact table name exists in the currently selected database. */
function diagnostics_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

/** Check whether an exact column name exists on a table in the current database. */
function diagnostics_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1');
    $stmt->execute([$table, $column]);
    return (bool) $stmt->fetchColumn();
}

/** Build the administrator System Health report. */
function diagnostics_report(): array
{
    $checks = [];
    $add = static function (string $name, bool $ok, string $detail) use (&$checks): void {
        $checks[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail];
    };

    $add('PHP version', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION . ' (requires 8.1+)');
    $add('PDO MySQL', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'pdo_mysql loaded' : 'pdo_mysql missing');
    $add('JSON', extension_loaded('json'), extension_loaded('json') ? 'JSON support loaded' : 'JSON support missing');
    $add('mbstring', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'mbstring loaded' : 'mbstring missing');
    $add('Random source', function_exists('random_bytes'), function_exists('random_bytes') ? 'random_bytes available' : 'random_bytes missing');

    $bankPath = study_project_root() . '/data/question-bank.json';
    $add('Bundled bank file', is_file($bankPath) && is_readable($bankPath), is_file($bankPath) ? basename($bankPath) . ' present' : 'data/question-bank.json missing');
    if (is_file($bankPath)) {
        try {
            $summary = question_bank_validate_payload(question_bank_load_file($bankPath));
            $add('Bundled bank validation', true, $summary['questions'] . ' questions, ' . $summary['subjects'] . ' subjects, ' . $summary['attachments'] . ' cards referencing attachments');
        } catch (Throwable $exception) {
            $add('Bundled bank validation', false, $exception->getMessage());
        }
    }

    try {
        $pdo = db();
        $pdo->query('SELECT 1');
        $add('Database connection', true, 'Connection successful');
        foreach (['users', 'subjects', 'questions', 'app_settings', 'admin_logs', 'admin_login_attempts', 'schema_migrations'] as $table) {
            $exists = diagnostics_table_exists($pdo, $table);
            $add('Table ' . $table, $exists, $exists ? 'present' : 'missing');
        }
        $profileColumns = ['display_name', 'must_change_password', 'temporary_password_consumed_at', 'active', 'created_by_user_id', 'last_login_at', 'password_changed_at'];
        $presentProfileColumns = 0;
        foreach ($profileColumns as $column) {
            if (diagnostics_column_exists($pdo, 'users', $column)) {
                $presentProfileColumns++;
            }
        }
        $add('User account schema', $presentProfileColumns === count($profileColumns), $presentProfileColumns . '/' . count($profileColumns) . ' profile columns present');
        if ($presentProfileColumns === count($profileColumns)) {
            $activeUsers = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE active = 1')->fetchColumn();
            $activeAdmins = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE active = 1 AND role = 'admin'")->fetchColumn();
            $add('Active user accounts', $activeUsers >= 1, $activeUsers . ' active accounts');
            $add('Active administrators', $activeAdmins >= 1, $activeAdmins . ' active administrators');
        }
        $summary = question_bank_summary();
        $add('Imported subjects', $summary['subjects'] === 9, $summary['subjects'] . ' total, expected 9 for bundled bank');
        $add('Imported questions', $summary['questions'] === 880, $summary['questions'] . ' total, expected 880 for bundled bank');

        $migrationPath = study_project_root() . '/database/migrations';
        $pending = pending_migration_files($pdo, $migrationPath);
        $add('Pending migrations', $pending === [], $pending === [] ? 'none' : implode(', ', array_map('basename', $pending)));
    } catch (Throwable $exception) {
        $add('Database inspection', false, $exception->getMessage());
    }

    return $checks;
}
