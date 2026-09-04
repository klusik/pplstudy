<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_settings.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Edits centralized public-site text and drill defaults.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\clamp_int;
use function PplStudy\Core\require_admin;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Services\app_setting;
use function PplStudy\Services\app_setting_defaults;
use function PplStudy\Services\app_settings_save;
use function PplStudy\Views\render_admin_settings;

/** Handle administrator site settings. */
function admin_settings(): void
{
    require_admin();
    $message = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        verify_csrf();
        $values = [
            'site_name' => substr(trim((string) ($_POST['site_name'] ?? '')), 0, 120),
            'site_subtitle' => substr(trim((string) ($_POST['site_subtitle'] ?? '')), 0, 220),
            'home_intro' => trim((string) ($_POST['home_intro'] ?? '')),
            'source_disclaimer' => trim((string) ($_POST['source_disclaimer'] ?? '')),
            'default_drill_count' => (string) clamp_int((int) ($_POST['default_drill_count'] ?? 20), 1, 120),
        ];
        app_settings_save($values);
        admin_log('settings.update', 'Updated public site settings.');
        $message = 'Nastavení bylo uloženo.';
    }
    $settings = [];
    foreach (array_keys(app_setting_defaults()) as $key) {
        $settings[$key] = app_setting($key);
    }
    render_admin_settings($settings, $message);
}
