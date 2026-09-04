<?php

/**
 * Project: PPL Study
 * File: app/bootstrap/configuration.php
 * Module Type: Bootstrap Module
 *
 * Purpose:
 *   Loads config.php once, validates its basic shape, and exposes base URL and
 *   project path helpers while keeping filesystem details centralized.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

use RuntimeException;

/** Return the absolute project root. */
function study_project_root(): string
{
    return dirname(__DIR__, 2);
}

/** Return true when the deployment has a readable config.php. */
function study_has_config(): bool
{
    return is_file(study_project_root() . '/config.php') && is_readable(study_project_root() . '/config.php');
}

/** Load and cache config.php. */
function study_config(): array
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }
    $path = study_project_root() . '/config.php';
    if (!is_file($path)) {
        throw new RuntimeException('Application config.php is missing.');
    }
    $loaded = require $path;
    if (!is_array($loaded) || !is_array($loaded['database'] ?? null)) {
        throw new RuntimeException('config.php must return an array containing database settings.');
    }
    $config = $loaded;
    return $config;
}

/** Redirect an uninstalled request to the root installer. */
function study_redirect_to_installer(): never
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $base = rtrim(dirname($script), '/.');
    if (str_ends_with($base, '/public')) {
        $base = substr($base, 0, -7);
    }
    header('Location: ' . ($base !== '' ? $base : '') . '/install.php', true, 302);
    exit;
}

/** Return the configured absolute public base URL normalized to omit a trailing slash. */
function study_base_url(): string
{
    return rtrim((string) (study_config()['base_url'] ?? ''), '/');
}

/** Build an absolute application URL from a route path. */
function study_url(string $path = ''): string
{
    $base = study_base_url();
    $path = '/' . ltrim($path, '/');
    return $path === '/' ? $base . '/' : $base . $path;
}

/** Return the path component of base_url for installations in a subdirectory. */
function study_base_path(): string
{
    $path = (string) parse_url(study_base_url(), PHP_URL_PATH);
    $path = '/' . trim($path, '/');
    return $path === '/' ? '' : rtrim($path, '/');
}
