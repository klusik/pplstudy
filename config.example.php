<?php

/**
 * Project: PPL Study
 * File: config.example.php
 * Module Type: Deployment Configuration Example
 *
 * Purpose:
 *   Documents the configuration written automatically by install.php.
 *
 * Notes:
 *   - Copy to config.php only for manual installation.
 *   - The normal installer is recommended because it validates database access.
 */

declare(strict_types=1);

return [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'ppl_study',
        'user' => 'ppl_study_user',
        'password' => 'change-me',
        'charset' => 'utf8mb4',
    ],
    'base_url' => 'https://study.example.com',
    'session_name' => 'ppl_study_session',
    'csrf_secret' => 'replace-with-a-long-random-secret',
    'setup_key' => 'replace-with-a-separate-long-random-recovery-key',
    'runtime_limits' => [
        // Optional deployment overrides. Defaults live in app/configuration_defaults.php.
    ],
];
