<?php

/**
 * Project: PPL Study
 * File: database/migrations/202609030001_initial_schema.php
 * Module Type: Database Migration
 *
 * Purpose:
 *   Creates the initial administrator, question-bank, settings, diagnostics,
 *   and audit schema used by version 0.1.0.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(190) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin') NOT NULL DEFAULT 'admin',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY users_username_unique (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS subjects (
        id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
        slug VARCHAR(100) NOT NULL,
        name VARCHAR(190) NOT NULL,
        description TEXT NULL,
        source_version VARCHAR(100) NULL,
        exam_question_count SMALLINT UNSIGNED NOT NULL,
        exam_time_minutes SMALLINT UNSIGNED NOT NULL,
        expected_cards SMALLINT UNSIGNED NOT NULL,
        official_pdf_url VARCHAR(1024) NULL,
        attachments_url VARCHAR(1024) NULL,
        sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY subjects_slug_unique (slug),
        KEY subjects_active_sort_index (active, sort_order, id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS questions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        sequence_no SMALLINT UNSIGNED NOT NULL,
        subject_id TINYINT UNSIGNED NOT NULL,
        title VARCHAR(500) NULL,
        source_trace VARCHAR(500) NOT NULL,
        source_question_ids_json TEXT NOT NULL,
        merged TINYINT(1) NOT NULL DEFAULT 0,
        prompt TEXT NOT NULL,
        answer TEXT NOT NULL,
        explanation MEDIUMTEXT NOT NULL,
        warning TEXT NULL,
        attachment_code VARCHAR(32) NULL,
        attachment_url VARCHAR(1024) NULL,
        attachment_indexed TINYINT(1) NOT NULL DEFAULT 0,
        source_page SMALLINT UNSIGNED NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY questions_sequence_unique (sequence_no),
        KEY questions_subject_sequence_index (subject_id, active, sequence_no),
        KEY questions_attachment_index (attachment_code),
        CONSTRAINT questions_subject_foreign FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS app_settings (
        setting_key VARCHAR(120) NOT NULL PRIMARY KEY,
        setting_value MEDIUMTEXT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS admin_logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        event_type VARCHAR(100) NOT NULL,
        message VARCHAR(500) NOT NULL,
        context_json MEDIUMTEXT NULL,
        created_at DATETIME NOT NULL,
        KEY admin_logs_created_index (created_at),
        KEY admin_logs_event_index (event_type, created_at),
        CONSTRAINT admin_logs_user_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS admin_login_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        client_ip VARCHAR(64) NOT NULL,
        username VARCHAR(190) NOT NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        attempted_at DATETIME NOT NULL,
        KEY admin_login_attempts_ip_time_index (client_ip, attempted_at),
        KEY admin_login_attempts_time_index (attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];
