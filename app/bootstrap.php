<?php

/**
 * Project: PPL Study
 * File: app/bootstrap.php
 * Module Type: Core Module
 *
 * Purpose:
 *   Defines the project version, loads modular runtime layers, and exposes the
 *   single application run function used by public/index.php.
 *
 * Responsibilities:
 *   - Load configuration, database, security, services, views, controllers,
 *     routing, session, request, and dispatch modules in deterministic order
 *   - Redirect a not-yet-installed deployment to install.php
 *   - Keep the codebase framework-free and friendly to shared hosting
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 *   - Prefer small, readable changes over broad rewrites.
 */

declare(strict_types=1);

namespace PplStudy\Core;

const APP_VERSION = '0.1.1';
const APP_SCHEMA_VERSION = 4;

require_once __DIR__ . '/bootstrap/configuration.php';
require_once __DIR__ . '/configuration_defaults.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/migrations.php';
require_once __DIR__ . '/services.php';
require_once __DIR__ . '/views.php';
require_once __DIR__ . '/controllers.php';
require_once __DIR__ . '/bootstrap/routing.php';
require_once __DIR__ . '/bootstrap/session.php';
require_once __DIR__ . '/bootstrap/request.php';
require_once __DIR__ . '/bootstrap/dispatch.php';

/** Start the session, resolve the request, and dispatch the selected controller. */
function study_run(): void
{
    if (!study_has_config()) {
        study_redirect_to_installer();
    }

    $config = study_config();
    study_start_session($config);
    $page = study_initialize_request();
    study_dispatch_page($page);
}
