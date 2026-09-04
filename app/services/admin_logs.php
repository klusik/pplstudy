<?php

/**
 * Project: PPL Study
 * File: app/services/admin_logs.php
 * Module Type: Service
 *
 * Purpose:
 *   Records compact administrator audit events for content and configuration
 *   changes while keeping log-table details inside this service.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Services;

use function PplStudy\Core\current_admin_id;
use function PplStudy\Core\db;
use function PplStudy\Core\now_sql;

/** Record one administrator audit event. */
function admin_log(string $eventType, string $message, array $context = []): void
{
    $stmt = db()->prepare('INSERT INTO admin_logs (user_id, event_type, message, context_json, created_at) VALUES (?, ?, ?, ?, ?)');
    $json = $context === [] ? null : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt->execute([current_admin_id(), substr($eventType, 0, 100), substr($message, 0, 500), $json, now_sql()]);
}

/** Return recent audit events for the dashboard. */
function admin_log_recent(int $limit = 20): array
{
    $limit = max(1, min(100, $limit));
    $sql = 'SELECT l.*, u.username FROM admin_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT ' . $limit;
    return db()->query($sql)->fetchAll();
}
