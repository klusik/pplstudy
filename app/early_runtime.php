<?php

/**
 * Project: PPL Study
 * File: app/early_runtime.php
 * Module Type: Core Module
 *
 * Purpose:
 *   Provides dependency-free request hardening before configuration, database,
 *   services, or views are loaded.
 *
 * Responsibilities:
 *   - Convert uncaught exceptions and fatal PHP errors to safe HTTP responses
 *   - Emit a short correlation reference for server-side troubleshooting
 *   - Avoid leaking stack traces, credentials, SQL, or filesystem paths
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep this file dependency-free.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\EarlyRuntime;

use Throwable;

/** Return a short public error correlation reference. */
function request_error_reference(): string
{
    try {
        return strtoupper(bin2hex(random_bytes(8)));
    } catch (Throwable) {
        return strtoupper(substr(hash('sha256', uniqid('ppl-study-error-', true)), 0, 16));
    }
}

/** Return true when the current request expects a JSON response. */
function request_expects_json(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    if (str_contains($accept, 'application/json')) {
        return true;
    }
    return str_starts_with((string) ($_GET['page'] ?? ''), 'api_');
}

/** Record one emergency event directly through the early-runtime log path. */
function log_emergency(string $kind, string $reference, Throwable|string|null $error = null): void
{
    $detail = '';
    if ($error instanceof Throwable) {
        $detail = get_class($error) . ': ' . $error->getMessage() . ' @ ' . $error->getFile() . ':' . $error->getLine();
    } elseif (is_string($error)) {
        $detail = $error;
    }
    $detail = preg_replace('/[\r\n\x00-\x1F\x7F]+/', ' ', $detail) ?? '';
    if (strlen($detail) > 1800) {
        $detail = substr($detail, 0, 1800) . '...';
    }
    error_log('[PPL Study] ' . $kind . ' reference=' . $reference . ($detail !== '' ? ' detail=' . $detail : ''));
}

/** Emit the safe top-level HTTP 500 response. */
function emit_server_error(string $reference): void
{
    if (headers_sent()) {
        return;
    }
    http_response_code(500);
    header('Cache-Control: no-store, private, max-age=0');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    if (request_expects_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'Internal server error.',
            'reference' => $reference,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>Server error</title></head><body><h1>Server error</h1><p>The request failed.</p><p>Reference: ' . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
}

/** Register exception and fatal shutdown handlers once. */
function register_emergency_handler(): void
{
    if (!empty($GLOBALS['ppl_study_emergency_registered'])) {
        return;
    }
    $GLOBALS['ppl_study_emergency_registered'] = true;

    set_exception_handler(static function (Throwable $exception): void {
        $GLOBALS['ppl_study_emergency_handled'] = true;
        $reference = request_error_reference();
        log_emergency('uncaught', $reference, $exception);
        emit_server_error($reference);
    });

    register_shutdown_function(static function (): void {
        if (!empty($GLOBALS['ppl_study_emergency_handled'])) {
            return;
        }
        $error = error_get_last();
        if (!is_array($error)) {
            return;
        }
        if (!in_array((int) ($error['type'] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            return;
        }
        $GLOBALS['ppl_study_emergency_handled'] = true;
        $reference = request_error_reference();
        $message = (string) ($error['message'] ?? '') . ' @ ' . (string) ($error['file'] ?? '') . ':' . (int) ($error['line'] ?? 0);
        log_emergency('fatal', $reference, $message);
        emit_server_error($reference);
    });
}
