<?php

/**
 * Project: PPL Study
 * File: app/helpers.php
 * Module Type: Core Helpers
 *
 * Purpose:
 *   Provides small presentation-neutral utility functions shared by modules.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

/** HTML-escape one value. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Return the current UTC timestamp in the SQL DATETIME format used by the project. */
function now_sql(): string
{
    return gmdate('Y-m-d H:i:s');
}

/** Clamp an integer to an inclusive range. */
function clamp_int(int $value, int $min, int $max): int
{
    return max($min, min($max, $value));
}

/** Return a normalized lowercase slug fragment suitable for route comparisons. */
function normalize_slug(string $value): string
{
    $value = strtolower(trim($value));
    return preg_replace('/[^a-z0-9_-]+/', '-', $value) ?: '';
}

/** Emit a JSON response and terminate request dispatch. */
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
