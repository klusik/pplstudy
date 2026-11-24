<?php

/**
 * Project: PPL Study
 * File: public/index.php
 * Module Type: Public Entry Point
 *
 * Purpose:
 *   Loads dependency-free fatal error handling and the normal application
 *   bootstrap, then dispatches the current request.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/early_runtime.php';
\PplStudy\EarlyRuntime\register_emergency_handler();

require_once dirname(__DIR__) . '/app/update_gate.php';
\PplStudy\Update\enter_gate(dirname(__DIR__));

require_once dirname(__DIR__) . '/app/bootstrap.php';
\PplStudy\Core\study_run();
