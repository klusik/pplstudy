<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_updates.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Authorize release discovery and explicit, CSRF-protected administrator
 *   update/reinstall requests. Never expose repository credentials.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use RuntimeException;
use const PplStudy\Core\APP_VERSION;
use function PplStudy\Core\require_admin;
use function PplStudy\Core\study_url;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Update\install_release;
use function PplStudy\Update\latest_release;
use function PplStudy\Update\readiness;
use function PplStudy\Views\render_admin_updates;

/** Handle administrator-only update discovery and explicit installation. */
function admin_updates(): void
{
    require_admin();
    $message = (string) ($_SESSION['ppl_update_notice'] ?? '');
    unset($_SESSION['ppl_update_notice']);
    $error = '';
    $release = null;
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $action = (string) ($_POST['action'] ?? '');
            if (!in_array($action, ['check', 'install', 'force'], true)) {
                throw new RuntimeException('Neznámá akce updateru.');
            }
            $release = latest_release();
            if ($action !== 'check') {
                $expectedVersion = (string) ($_POST['expected_version'] ?? '');
                $expectedDigest = (string) ($_POST['expected_digest'] ?? '');
                $expectedAsset = (string) ($_POST['expected_asset'] ?? '');
                if ($expectedVersion !== $release['version']
                    || $expectedAsset !== (string) $release['asset_id']
                    || !hash_equals($release['sha256'], $expectedDigest)) {
                    throw new RuntimeException('Release se od poslední kontroly změnil. Ověřte nový balíček.');
                }
                if (version_compare($release['version'], APP_VERSION, '<')
                    || ($action === 'install' && !version_compare($release['version'], APP_VERSION, '>'))) {
                    throw new RuntimeException('Tato aktualizace není povolena.');
                }
                $changed = install_release($release);
                $message = 'Verze ' . $release['version'] . ' ověřena a nasazena. Změněné soubory: ' . $changed . '.';
                try {
                    admin_log('application.update', 'Administrator installed a verified GitHub release.', [
                        'from' => APP_VERSION,
                        'to' => $release['version'],
                        'forced' => $action === 'force',
                        'asset_id' => $release['asset_id'],
                        'changed_files' => $changed,
                    ]);
                } catch (\Throwable $exception) {
                    // A database logging failure must not mark a committed file
                    // deployment as unsuccessful or disclose database details.
                    $message .= ' Auditní záznam se nepodařilo uložit.';
                }
                $_SESSION['ppl_update_notice'] = $message;
                header('Location: ' . study_url('/admin/updates'), true, 303);
                exit;
            }
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        } catch (\Throwable $exception) {
            $error = 'Aktualizaci nebylo možné dokončit. Opakujte kontrolu; při nedostupnosti webu postupujte podle dokumentace obnovy.';
        }
    }
    render_admin_updates(readiness(), $release, $message, $error);
}
