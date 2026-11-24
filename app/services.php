<?php

/**
 * Project: PPL Study
 * File: app/services.php
 * Module Type: Service Loader
 *
 * Purpose:
 *   Loads domain services in dependency order before views and controllers.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

require_once __DIR__ . '/services/app_settings.php';
require_once __DIR__ . '/services/admin_logs.php';
require_once __DIR__ . '/services/users.php';
require_once __DIR__ . '/services/question_bank.php';
require_once __DIR__ . '/services/source_documents.php';
require_once __DIR__ . '/services/quiz.php';
require_once __DIR__ . '/services/importer.php';
require_once __DIR__ . '/services/diagnostics.php';
require_once __DIR__ . '/services/updater.php';
require_once __DIR__ . '/services/theory.php';
