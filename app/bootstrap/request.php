<?php

/**
 * Project: PPL Study
 * File: app/bootstrap/request.php
 * Module Type: Bootstrap Module
 *
 * Purpose:
 *   Applies small request-wide HTTP policies before controller dispatch.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Core;

/** Initialize request-wide headers and resolve the route identifier. */
function study_initialize_request(): string
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    if (study_request_has_internal_path_prefix()) {
        $target = study_url(study_request_path());
        $query = trim((string) ($_SERVER['QUERY_STRING'] ?? ''));
        if ($query !== '') {
            $target .= '?' . $query;
        }
        header('Location: ' . $target, true, 307);
        exit;
    }

    return study_resolve_page();
}
