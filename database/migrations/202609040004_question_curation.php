<?php

/**
 * Project: PPL Study
 * File: database/migrations/202609040004_question_curation.php
 * Module Type: Database Migration
 *
 * Purpose:
 *   Adds source-fidelity tracking and structured explanation metadata so the
 *   question bank can be curated sequentially against administrator-supplied
 *   source documents.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Existing cards remain usable after the migration.
 *   - Exact-source wording is represented explicitly by wording_status.
 *   - Source PDFs are stored outside the public document root and are served
 *     only through authenticated application routes.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

return [
    "ALTER TABLE questions ADD COLUMN curation_status VARCHAR(32) NOT NULL DEFAULT 'draft' AFTER choices_generated",
    "ALTER TABLE questions ADD COLUMN wording_status VARCHAR(32) NOT NULL DEFAULT 'study_wording' AFTER curation_status",
    "ALTER TABLE questions ADD COLUMN explanation_sections_json MEDIUMTEXT NULL AFTER explanation",
    "ALTER TABLE questions ADD COLUMN source_document_key VARCHAR(120) NULL AFTER source_page",
    "ALTER TABLE questions ADD COLUMN source_document_page SMALLINT UNSIGNED NULL AFTER source_document_key",
    "ALTER TABLE questions ADD COLUMN source_verified_at DATETIME NULL AFTER source_document_page",
    "ALTER TABLE questions ADD KEY questions_curation_index (curation_status, wording_status, sequence_no)",
    "ALTER TABLE questions ADD KEY questions_source_document_index (source_document_key, source_document_page)",
    "CREATE TABLE IF NOT EXISTS source_documents (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        source_key VARCHAR(120) NOT NULL,
        subject_id TINYINT UNSIGNED NULL,
        title VARCHAR(255) NOT NULL,
        version_label VARCHAR(120) NULL,
        publisher VARCHAR(190) NULL,
        official_url VARCHAR(1024) NULL,
        stored_filename VARCHAR(255) NULL,
        mime_type VARCHAR(100) NULL,
        sha256 CHAR(64) NULL,
        file_size BIGINT UNSIGNED NULL,
        uploaded_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY source_documents_key_unique (source_key),
        KEY source_documents_subject_index (subject_id, source_key),
        CONSTRAINT source_documents_subject_foreign FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];
