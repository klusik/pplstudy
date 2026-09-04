<?php

/**
 * Project: PPL Study
 * File: app/services/app_settings.php
 * Module Type: Service
 *
 * Purpose:
 *   Provides centralized database-backed application settings with explicit
 *   defaults for a freshly installed deployment.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use function PplStudy\Core\db;
use function PplStudy\Core\now_sql;

/** Return built-in settings used when no database value exists. */
function app_setting_defaults(): array
{
    return [
        'site_name' => 'PPL(A) Study',
        'site_subtitle' => 'Dynamický studijní systém pro teoretickou zkoušku PPL(A)',
        'home_intro' => 'Studuj aktuální banku otázek z devíti předmětů, procvičuj náhodné otázky a spusť paper drill se stejným počtem otázek a časem jako v přehledu zkoušky.',
        'source_disclaimer' => 'První předmět používá přesné znění a možnosti z dodaného oficiálního PDF ÚCL; další předměty se převádějí stejným postupem. Pro skutečný provoz mají přednost aktuální předpisy, AIP, NOTAM, AFM/POH a instrukce ATS.',
        'default_drill_count' => '20',
    ];
}

/** Read one setting with default fallback. */
function app_setting(string $key): string
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $stmt = db()->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    if ($value === false) {
        $defaults = app_setting_defaults();
        $value = (string) ($defaults[$key] ?? '');
    }
    $cache[$key] = (string) $value;
    return $cache[$key];
}

/** Persist one setting value. */
function app_setting_set(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO app_settings (setting_key, setting_value, updated_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)');
    $stmt->execute([$key, $value, now_sql()]);
}

/** Persist all known editable site settings. */
function app_settings_save(array $values): void
{
    foreach (array_keys(app_setting_defaults()) as $key) {
        if (!array_key_exists($key, $values)) {
            continue;
        }
        app_setting_set($key, (string) $values[$key]);
    }
}
