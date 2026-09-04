<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_import.php
 * Module Type: Admin Controller
 *
 * Purpose:
 *   Provides controlled bundled and uploaded JSON imports for the question bank.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use RuntimeException;
use function PplStudy\Core\require_admin;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Services\import_bundled_question_bank;
use function PplStudy\Services\question_bank_import;
use function PplStudy\Services\question_bank_load_file;
use function PplStudy\Services\question_bank_validate_payload;
use function PplStudy\Views\render_admin_import;

/** Handle the administrator import route. */
function admin_import(): void
{
    require_admin();
    $bundledPath = dirname(__DIR__, 2) . '/data/question-bank.json';
    $bundledSummary = null;
    $message = '';
    $error = '';
    try {
        $bundledSummary = question_bank_validate_payload(question_bank_load_file($bundledPath));
    } catch (\Throwable $exception) {
        $error = 'Bundled bank validation failed: ' . $exception->getMessage();
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $prune = isset($_POST['prune']);
            $action = (string) ($_POST['action'] ?? '');
            if ($action === 'bundled') {
                $summary = import_bundled_question_bank($prune);
            } elseif ($action === 'upload') {
                $file = $_FILES['bank_file'] ?? null;
                if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('JSON soubor se nepodařilo nahrát.');
                }
                if ((int) ($file['size'] ?? 0) > study_limit('import_max_bytes')) {
                    throw new RuntimeException('JSON soubor překračuje povolenou velikost importu.');
                }
                $tmp = (string) ($file['tmp_name'] ?? '');
                if ($tmp === '' || !is_uploaded_file($tmp)) {
                    throw new RuntimeException('Upload vyžaduje soubor přijatý přes tento formulář.');
                }
                $summary = question_bank_import(question_bank_load_file($tmp), $prune);
            } else {
                throw new RuntimeException('Neznámá importní akce.');
            }
            admin_log('question_bank.import', 'Question bank imported.', ['subjects' => $summary['subjects'], 'questions' => $summary['questions'], 'prune' => $prune]);
            $message = 'Import dokončen: ' . $summary['questions'] . ' karet v ' . $summary['subjects'] . ' předmětech.';
            $bundledSummary = question_bank_validate_payload(question_bank_load_file($bundledPath));
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }

    render_admin_import($bundledSummary, $message, $error);
}
