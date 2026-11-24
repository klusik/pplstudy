<?php

/**
 * Project: PPL Study
 * File: app/bootstrap/routing.php
 * Module Type: Bootstrap Module
 *
 * Purpose:
 *   Resolves query-string and pretty-path routes through the project router.
 *
 * Responsibilities:
 *   - Support shared hosting with Apache mod_rewrite
 *   - Preserve ?page=... fallback routing when rewrites are unavailable
 *   - Extract numeric card and user IDs from pretty paths into request parameters
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

/** Return true when an internal hosting directory leaked into the browser path. */
function study_request_has_internal_path_prefix(): bool
{
    $scriptDirectory = study_script_directory();
    if ($scriptDirectory === '' || $scriptDirectory === study_base_path()) {
        return false;
    }

    $uriPath = study_request_uri_path();
    $basePath = study_base_path();
    if ($basePath !== '' && ($uriPath === $basePath || str_starts_with($uriPath, $basePath . '/'))) {
        return false;
    }
    return $uriPath === $scriptDirectory || str_starts_with($uriPath, $scriptDirectory . '/');
}

/** Return the normalized application-relative request path. */
function study_request_path(): string
{
    $uriPath = study_request_uri_path();
    $basePath = study_base_path();
    if ($basePath !== '' && ($uriPath === $basePath || str_starts_with($uriPath, $basePath . '/'))) {
        $uriPath = substr($uriPath, strlen($basePath));
    } else {
        /*
         * WEDOS and similar shared hosts may report or leak an internal
         * deployment prefix such as /subdom/ppl. Strip that prefix only as a
         * request-normalization fallback; generated URLs still come solely
         * from config.php base_url.
         */
        $scriptDirectory = study_script_directory();
        if ($scriptDirectory !== ''
            && ($uriPath === $scriptDirectory || str_starts_with($uriPath, $scriptDirectory . '/'))) {
            $uriPath = substr($uriPath, strlen($scriptDirectory));
            if ($basePath !== '' && ($uriPath === $basePath || str_starts_with($uriPath, $basePath . '/'))) {
                $uriPath = substr($uriPath, strlen($basePath));
            }
        }
    }

    $uriPath = '/' . trim($uriPath, '/');
    return $uriPath === '/' ? '/' : rtrim($uriPath, '/');
}

/** Resolve the current route into an internal page identifier. */
function study_resolve_page(): string
{
    $explicit = trim((string) ($_GET['page'] ?? ''));
    if ($explicit !== '') {
        return $explicit;
    }

    $path = study_request_path();
    $static = [
        '/' => 'home',
        '/index.php' => 'home',
        '/login' => 'user_login',
        '/logout' => 'user_logout',
        '/account' => 'account_profile',
        '/account/password' => 'account_password',
        '/study' => 'study',
        '/drill' => 'drill',
        '/search' => 'search',
        '/about' => 'about',
        '/api/quiz' => 'api_quiz',
        '/admin' => 'admin_dashboard',
        '/admin/login' => 'user_login',
        '/admin/logout' => 'user_logout',
        '/admin/users' => 'admin_users',
        '/admin/users/new' => 'admin_user_new',
        '/admin/questions' => 'admin_questions',
        '/admin/sources' => 'admin_sources',
        '/admin/import' => 'admin_import',
        '/admin/settings' => 'admin_settings',
        '/admin/diagnostics' => 'admin_diagnostics',
        '/admin/updates' => 'admin_updates',
    ];
    if (isset($static[$path])) {
        return $static[$path];
    }
    if (preg_match('#^/source/([a-z0-9_-]{1,120})$#', $path, $match) === 1) {
        $_GET['source_key'] = $match[1];
        return 'source_document_view';
    }
    if (preg_match('#^/question/(\d{1,5})$#', $path, $match) === 1) {
        $_GET['sequence'] = $match[1];
        return 'question';
    }
    if (preg_match('#^/admin/question/(\d{1,5})$#', $path, $match) === 1) {
        $_GET['sequence'] = $match[1];
        return 'admin_question_edit';
    }
    if (preg_match('#^/admin/user/(\d{1,10})$#', $path, $match) === 1) {
        $_GET['user_id'] = $match[1];
        return 'admin_user_edit';
    }
    return 'not_found';
}
