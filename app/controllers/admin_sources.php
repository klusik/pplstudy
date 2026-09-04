<?php

/**
 * Project: PPL Study
 * File: app/controllers/admin_sources.php
 * Module Type: Admin/Public Controller
 *
 * Purpose:
 *   Lets administrators attach local source PDFs to manifest entries and lets
 *   authenticated users open those locally stored sources.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Source files are served only after authentication.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Core\require_admin;
use function PplStudy\Core\require_user;
use function PplStudy\Core\verify_csrf;
use function PplStudy\Services\admin_log;
use function PplStudy\Services\source_document_by_key;
use function PplStudy\Services\source_document_local_path;
use function PplStudy\Services\source_document_store_upload;
use function PplStudy\Services\source_documents_all;
use function PplStudy\Views\render_admin_sources;
use function PplStudy\Views\render_not_found;

/** Render source-document inventory and handle administrator uploads. */
function admin_sources(): void
{
    require_admin();
    $message = '';
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        try {
            verify_csrf();
            $sourceKey = trim((string) ($_POST['source_key'] ?? ''));
            source_document_store_upload($sourceKey, is_array($_FILES['source_pdf'] ?? null) ? $_FILES['source_pdf'] : []);
            admin_log('source.upload', 'Uploaded source PDF ' . $sourceKey . '.', ['source_key' => $sourceKey]);
            $message = 'Zdrojové PDF bylo uloženo.';
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
    render_admin_sources(source_documents_all(), $message, $error);
}

/** Stream one locally stored source PDF to an authenticated user. */
function source_document_view(): void
{
    require_user();
    $sourceKey = trim((string) ($_GET['source_key'] ?? ''));
    $document = $sourceKey !== '' ? source_document_by_key($sourceKey) : null;
    $path = $sourceKey !== '' ? source_document_local_path($sourceKey) : null;
    if (!$document || !$path) {
        render_not_found();
        return;
    }

    header('Content-Type: application/pdf');
    header('Content-Length: ' . (string) filesize($path));
    header('Content-Disposition: inline; filename="' . basename((string) ($document['stored_filename'] ?? 'source.pdf')) . '"');
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}
