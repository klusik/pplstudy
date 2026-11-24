<?php

/**
 * Project: PPL Study
 * File: app/views.php
 * Module Type: View Loader
 *
 * Purpose:
 *   Loads presentation helpers after services and before controllers.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Core;

require_once __DIR__ . '/views/layout.php';
require_once __DIR__ . '/views/account.php';
require_once __DIR__ . '/views/public.php';
require_once __DIR__ . '/views/admin.php';
require_once __DIR__ . '/views/admin_updates.php';
require_once __DIR__ . '/views/theory.php';
