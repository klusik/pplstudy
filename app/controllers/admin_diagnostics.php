<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_diagnostics.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Renders System Health and exposes the explicit administrator-triggered
 *   migration runner.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_admin;
use function PplStudy\Core\run_migrations;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Services\diagnostics_report;
use function PplStudy\Views\render_admin_diagnostics;

/** Handle System Health and optional migration execution. */
function admin_diagnostics(): void
{
    require_admin();
    $message = '';
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            if ((string) ($_POST['action'] ?? '') !== 'migrate') {
                throw new \RuntimeException('Neznámá diagnostická akce.');
            }
            $ran = run_migrations();
            $message = $ran === [] ? 'Schéma databáze je aktuální.' : 'Proběhly migrace: ' . implode(', ', $ran);
            admin_log('database.migrate', 'Administrator ran pending migrations.', ['versions' => $ran]);
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
    render_admin_diagnostics(diagnostics_report(), $message, $error);
}
