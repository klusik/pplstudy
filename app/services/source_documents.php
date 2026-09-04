<?php

/**
 * Project: PPL Study
 * File: app/services/source_documents.php
 * Module Type: Service
 *
 * Purpose:
 *   Manages verified source PDFs used to support exact question wording while
 *   keeping those files outside direct public HTTP access.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Releases may bundle a source PDF supplied directly by the project owner.
 *   - Administrator uploads can replace the bundled local copy for the same source key.
 *   - Source documents are never fetched automatically by production.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use RuntimeException;
use Throwable;
use function PplStudy\Core\db;
use function PplStudy\Core\now_sql;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\study_project_root;

/** Load the versioned source-document manifest shipped with the project. */
function source_manifest(): array
{
    $path = study_project_root() . '/data/source-manifest.json';
    if (!is_file($path) || !is_readable($path)) {
        return [];
    }
    try {
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        return [];
    }
    $documents = is_array($decoded['documents'] ?? null) ? $decoded['documents'] : [];
    $result = [];
    foreach ($documents as $document) {
        if (!is_array($document)) {
            continue;
        }
        $key = trim((string) ($document['source_key'] ?? ''));
        if ($key === '') {
            continue;
        }
        $result[$key] = $document;
    }
    return $result;
}

/** Return one manifest entry by source key. */
function source_manifest_entry(string $sourceKey): ?array
{
    $manifest = source_manifest();
    return isset($manifest[$sourceKey]) && is_array($manifest[$sourceKey]) ? $manifest[$sourceKey] : null;
}

/** Return the protected local storage directory for source documents. */
function source_document_storage_dir(): string
{
    return study_project_root() . '/data/source-documents';
}

/** Return persisted upload metadata keyed by source_key. */
function source_document_db_rows(): array
{
    try {
        $rows = db()->query('SELECT * FROM source_documents ORDER BY subject_id, source_key')->fetchAll();
    } catch (Throwable) {
        return [];
    }
    $result = [];
    foreach ($rows as $row) {
        $result[(string) $row['source_key']] = $row;
    }
    return $result;
}

/** Return manifest entries enriched with current local upload metadata. */
function source_documents_all(): array
{
    $dbRows = source_document_db_rows();
    $documents = [];
    foreach (source_manifest() as $sourceKey => $manifest) {
        $row = $dbRows[$sourceKey] ?? [];
        $stored = trim((string) ($row['stored_filename'] ?? ''));
        if ($stored === '') {
            $stored = basename((string) ($manifest['expected_local_filename'] ?? ''));
        }
        $localPath = $stored !== '' ? source_document_storage_dir() . '/' . basename($stored) : '';
        $localAvailable = $localPath !== '' && is_file($localPath) && is_readable($localPath);
        $sha256 = (string) ($row['sha256'] ?? '');
        $fileSize = isset($row['file_size']) ? (int) $row['file_size'] : 0;
        $mimeType = (string) ($row['mime_type'] ?? '');
        if ($localAvailable && $sha256 === '') {
            $calculated = hash_file('sha256', $localPath);
            $sha256 = is_string($calculated) ? $calculated : '';
        }
        if ($localAvailable && $fileSize < 1) {
            $size = filesize($localPath);
            $fileSize = is_int($size) ? $size : 0;
        }
        if ($localAvailable && $mimeType === '') {
            $mimeType = 'application/pdf';
        }
        $documents[] = [
            ...$manifest,
            'stored_filename' => $stored,
            'mime_type' => $mimeType,
            'sha256' => $sha256,
            'file_size' => $fileSize,
            'uploaded_at' => (string) ($row['uploaded_at'] ?? ''),
            'local_available' => $localAvailable,
        ];
    }
    return $documents;
}

/** Return one enriched source-document row. */
function source_document_by_key(string $sourceKey): ?array
{
    foreach (source_documents_all() as $document) {
        if ((string) ($document['source_key'] ?? '') === $sourceKey) {
            return $document;
        }
    }
    return null;
}

/** Store one administrator-provided replacement PDF for a known source key. */
function source_document_store_upload(string $sourceKey, array $upload): array
{
    $manifest = source_manifest_entry($sourceKey);
    if (!$manifest) {
        throw new RuntimeException('Neznámý zdrojový dokument.');
    }
    $error = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Nahrání PDF selhalo s upload kódem ' . $error . '.');
    }
    $size = (int) ($upload['size'] ?? 0);
    if ($size < 1 || $size > study_limit('source_document_upload_max_bytes')) {
        throw new RuntimeException('PDF překračuje povolenou velikost zdrojového dokumentu.');
    }
    $tmp = (string) ($upload['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Dočasný upload soubor není platný.');
    }

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);
    if ($mime !== 'application/pdf') {
        throw new RuntimeException('Zdrojový dokument musí být PDF.');
    }

    $storage = source_document_storage_dir();
    if (!is_dir($storage) && !mkdir($storage, 0775, true) && !is_dir($storage)) {
        throw new RuntimeException('Adresář pro zdrojové dokumenty nelze vytvořit.');
    }
    if (!is_writable($storage)) {
        throw new RuntimeException('Adresář data/source-documents nemá právo zápisu.');
    }

    $expected = basename((string) ($manifest['expected_local_filename'] ?? ($sourceKey . '.pdf')));
    if (!str_ends_with(strtolower($expected), '.pdf')) {
        $expected .= '.pdf';
    }
    $destination = $storage . '/' . $expected;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('Zdrojové PDF se nepodařilo uložit.');
    }
    @chmod($destination, 0640);

    $sha256 = hash_file('sha256', $destination);
    if (!is_string($sha256) || $sha256 === '') {
        @unlink($destination);
        throw new RuntimeException('Kontrolní součet zdrojového PDF se nepodařilo vytvořit.');
    }

    $now = now_sql();
    $stmt = db()->prepare('INSERT INTO source_documents (source_key, subject_id, title, version_label, publisher, official_url, stored_filename, mime_type, sha256, file_size, uploaded_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE subject_id = VALUES(subject_id), title = VALUES(title), version_label = VALUES(version_label), publisher = VALUES(publisher), official_url = VALUES(official_url), stored_filename = VALUES(stored_filename), mime_type = VALUES(mime_type), sha256 = VALUES(sha256), file_size = VALUES(file_size), uploaded_at = VALUES(uploaded_at), updated_at = VALUES(updated_at)');
    $stmt->execute([
        $sourceKey,
        (int) ($manifest['subject_id'] ?? 0) ?: null,
        substr((string) ($manifest['title'] ?? $sourceKey), 0, 255),
        substr((string) ($manifest['version_label'] ?? ''), 0, 120),
        substr((string) ($manifest['publisher'] ?? ''), 0, 190),
        (string) ($manifest['official_url'] ?? ''),
        $expected,
        $mime,
        $sha256,
        (int) filesize($destination),
        $now,
        $now,
        $now,
    ]);

    return source_document_by_key($sourceKey) ?? [];
}

/** Resolve one uploaded source PDF to a local absolute path. */
function source_document_local_path(string $sourceKey): ?string
{
    $document = source_document_by_key($sourceKey);
    if (!$document || empty($document['local_available'])) {
        return null;
    }
    $stored = basename((string) ($document['stored_filename'] ?? ''));
    if ($stored === '') {
        return null;
    }
    $path = source_document_storage_dir() . '/' . $stored;
    return is_file($path) && is_readable($path) ? $path : null;
}
