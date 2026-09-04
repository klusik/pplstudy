<?php

/**
 * Project: PPL Study
 * File: database/migrations/202609030003_user_accounts.php
 * Module Type: Database Migration
 *
 * Purpose:
 *   Expands the original administrator-only user table into invitation-only
 *   learner accounts with administrator roles and first-login password setup.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Existing administrator rows remain active administrators with their
 *     current password and no forced password change.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

return [
    "ALTER TABLE users MODIFY COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user'",
    "ALTER TABLE users ADD COLUMN display_name VARCHAR(190) NULL AFTER username",
    "ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER role",
    "ALTER TABLE users ADD COLUMN temporary_password_consumed_at DATETIME NULL AFTER must_change_password",
    "ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER temporary_password_consumed_at",
    "ALTER TABLE users ADD COLUMN created_by_user_id BIGINT UNSIGNED NULL AFTER active",
    "ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL AFTER created_by_user_id",
    "ALTER TABLE users ADD COLUMN password_changed_at DATETIME NULL AFTER last_login_at",
    "UPDATE users SET display_name = username, active = 1, must_change_password = 0, password_changed_at = COALESCE(password_changed_at, updated_at) WHERE display_name IS NULL OR display_name = ''",
    "ALTER TABLE users ADD KEY users_role_active_index (role, active)",
    "ALTER TABLE users ADD KEY users_created_by_index (created_by_user_id)",
    "ALTER TABLE users ADD CONSTRAINT users_created_by_foreign FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL"
];
