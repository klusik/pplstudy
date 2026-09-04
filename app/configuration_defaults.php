<?php

/**
 * Project: PPL Study
 * File: app/configuration_defaults.php
 * Module Type: Core Configuration
 *
 * Purpose:
 *   Centralizes operational limits that should not be scattered through
 *   controllers or services.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Deployment-specific overrides belong in config.php under runtime_limits.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

/** Return all default operational limits. */
function study_configuration_defaults(): array
{
    return [
        'session_lifetime_seconds' => 60 * 60 * 12,
        'admin_session_lifetime_seconds' => 60 * 60 * 12,
        'login_window_seconds' => 15 * 60,
        'login_max_failures' => 7,
        'admin_login_window_seconds' => 15 * 60,
        'admin_login_max_failures' => 7,
        'account_password_min_length' => 15,
        'question_search_max_length' => 160,
        'question_list_page_size' => 40,
        'public_search_page_size' => 50,
        'quiz_custom_max_questions' => 120,
        'import_max_bytes' => 5 * 1024 * 1024,
        'source_document_upload_max_bytes' => 30 * 1024 * 1024,
    ];
}

/** Return one centralized limit, honoring a config.php override. */
function study_limit(string $name): int
{
    $defaults = study_configuration_defaults();
    if (!array_key_exists($name, $defaults)) {
        throw new \InvalidArgumentException('Unknown runtime limit: ' . $name);
    }
    $config = study_config();
    $overrides = is_array($config['runtime_limits'] ?? null) ? $config['runtime_limits'] : [];
    $value = $overrides[$name] ?? $defaults[$name];
    return max(1, (int) $value);
}
