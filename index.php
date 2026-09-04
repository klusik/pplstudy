<?php

/**
 * Project: PPL Study
 * File: index.php
 * Module Type: Core Entry Point
 *
 * Purpose:
 *   Keeps the project root usable as a web document root while delegating all
 *   HTTP handling to the public runtime entry point.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE and data/README.md.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 *   - Prefer small, readable changes over broad rewrites.
 */

declare(strict_types=1);

require __DIR__ . '/public/index.php';
