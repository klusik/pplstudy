<?php

/**
 * Project: PPL Study
 * File: app/controllers.php
 * Module Type: Controller Loader
 *
 * Purpose:
 *   Loads public and administrator HTTP controllers in one deterministic place.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

require_once __DIR__ . '/controllers/public_home.php';
require_once __DIR__ . '/controllers/public_study.php';
require_once __DIR__ . '/controllers/public_drill.php';
require_once __DIR__ . '/controllers/public_search.php';
require_once __DIR__ . '/controllers/public_about.php';
require_once __DIR__ . '/controllers/api_quiz.php';
require_once __DIR__ . '/controllers/admin_auth.php';
require_once __DIR__ . '/controllers/account.php';
require_once __DIR__ . '/controllers/admin_dashboard.php';
require_once __DIR__ . '/controllers/admin_users.php';
require_once __DIR__ . '/controllers/admin_questions.php';
require_once __DIR__ . '/controllers/admin_sources.php';
require_once __DIR__ . '/controllers/admin_import.php';
require_once __DIR__ . '/controllers/admin_settings.php';
require_once __DIR__ . '/controllers/admin_diagnostics.php';
require_once __DIR__ . '/controllers/admin_updates.php';
