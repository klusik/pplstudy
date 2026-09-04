<?php

/**
 * Project: PPL Study
 * File: app/migrations.php
 * Module Type: Database Migration Core
 *
 * Purpose:
 *   Discovers, validates, and applies incremental PHP migration files in
 *   deterministic filename order.
 *
 * Responsibilities:
 *   - Keep installer and future upgrade tooling on one migration contract
 *   - Record each completed migration in schema_migrations
 *   - Validate the complete pending set before changing the database
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Migration files return a list of non-empty SQL strings.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

use PDO;
use RuntimeException;

/** Discover migration files in deterministic filename order. */
function discover_migration_files(string $path): array
{
    $files = glob(rtrim($path, '/\\') . '/*.php') ?: [];
    sort($files, SORT_STRING);
    return array_values($files);
}

/** Load and validate one migration file. */
function load_migration_definition(string $file): array
{
    $definition = require $file;
    if (!is_array($definition)) {
        throw new RuntimeException('Migration ' . basename($file) . ' must return an array.');
    }
    $expected = 0;
    foreach ($definition as $key => $statement) {
        if ($key !== $expected || !is_string($statement) || trim($statement) === '') {
            throw new RuntimeException('Migration ' . basename($file) . ' must return a list of non-empty SQL strings.');
        }
        $expected++;
    }
    return array_values($definition);
}

/** Return pending migration files after reading schema_migrations. */
function pending_migration_files(PDO $pdo, string $path): array
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
        version VARCHAR(64) NOT NULL PRIMARY KEY,
        applied_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    $seen = array_fill_keys(array_map('strval', $applied), true);
    return array_values(array_filter(
        discover_migration_files($path),
        static fn (string $file): bool => !isset($seen[basename($file, '.php')])
    ));
}

/** Apply every pending migration and return the versions that ran. */
function run_migrations(?PDO $pdo = null): array
{
    $pdo ??= db();
    $path = study_project_root() . '/database/migrations';
    $pending = pending_migration_files($pdo, $path);
    $definitions = [];
    foreach ($pending as $file) {
        $definitions[$file] = load_migration_definition($file);
    }

    $ran = [];
    foreach ($pending as $file) {
        $version = basename($file, '.php');
        foreach ($definitions[$file] as $statement) {
            $pdo->exec($statement);
        }
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (version, applied_at) VALUES (?, ?)');
        $stmt->execute([$version, now_sql()]);
        $ran[] = $version;
    }
    return $ran;
}
