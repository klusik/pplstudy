<?php

/**
 * Project: PPL Study
 * File: app/database.php
 * Module Type: Database Core
 *
 * Purpose:
 *   Owns the PDO connection boundary and keeps connection behavior consistent
 *   across controllers, services, migrations, installer, and diagnostics.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

use PDO;
use RuntimeException;

/** Build a MySQL PDO DSN from the current configuration. */
function study_database_dsn(array $database): string
{
    $host = (string) ($database['host'] ?? 'localhost');
    $port = (int) ($database['port'] ?? 3306);
    $name = (string) ($database['name'] ?? '');
    $charset = (string) ($database['charset'] ?? 'utf8mb4');
    if ($name === '') {
        throw new RuntimeException('Database name is missing from config.php.');
    }
    return 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=' . $charset;
}

/** Return the shared PDO connection for the current request. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $database = study_config()['database'];
    $pdo = new PDO(
        study_database_dsn($database),
        (string) ($database['user'] ?? ''),
        (string) ($database['password'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $pdo;
}
