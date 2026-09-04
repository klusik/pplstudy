<?php

/**
 * Project: PPL Study
 * File: database/migrations/202609030002_question_choices.php
 * Module Type: Database Migration
 *
 * Purpose:
 *   Adds editable multiple-choice study options to each question while keeping
 *   the source-derived correct answer as the scoring authority.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Existing rows remain importable during the upgrade. The bundled bank
 *     import fills choices_json for all 880 cards after this migration runs.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

return [
    "ALTER TABLE questions ADD COLUMN choices_json MEDIUMTEXT NULL AFTER answer",
    "ALTER TABLE questions ADD COLUMN choices_generated TINYINT(1) NOT NULL DEFAULT 1 AFTER choices_json"
];
