<?php

/**
 * Project: PPL Study
 * File: app/update_gate.php
 * Module Type: Early Runtime / Updater Recovery
 *
 * Purpose:
 *   Serialize application requests against file activation and recover an
 *   interrupted code-only update before loading potentially mixed PHP modules.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Update;

use RuntimeException;

/** Return whether a package path belongs to the managed deployment inventory. */
function managed_path(string $path): bool
{
    if (in_array($path, [
        '.htaccess', 'index.php', 'install.php', 'reset.php', 'config.example.php',
        'public/index.php', 'release-metadata.json', 'README.md', 'PATCH_NOTES.md',
        'ARCHITECTURE.md', 'CODEMAP.md', 'DATABASE.md', 'TESTING.md', 'LICENSE',
        'app/.htaccess', 'database/.htaccess', 'data/.htaccess',
        'data/README.md', 'data/source-manifest.json', 'data/question-bank.json',
        'data/source-documents/.htaccess', 'data/source-documents/README.md',
    ], true)) {
        return true;
    }

    return preg_match(
        '~\A(?:app/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.php'
        . '|database/migrations/[A-Za-z0-9_-]+\.php'
        . '|public/assets/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9._-]+\.(?:css|js|svg|png|jpe?g|webp|ico|woff2?)'
        . '|data/(?:curation|source-exact)/[A-Za-z0-9_-]+\.json)\z~D',
        $path
    ) === 1;
}

/** Resolve a managed relative file without following symlinks or special files. */
function safe_path(string $root, string $path, bool $createParents = false): string
{
    if (!managed_path($path) || is_link($root) || !is_dir($root)) {
        throw new RuntimeException('Nepovolená cesta aktualizace.');
    }

    $segments = explode('/', $path);
    $name = array_pop($segments);
    $directory = $root;
    foreach ($segments as $segment) {
        $directory .= '/' . $segment;
        if (is_link($directory) || (file_exists($directory) && !is_dir($directory))) {
            throw new RuntimeException('Nebezpečný adresář aktualizace.');
        }
        if (!is_dir($directory)) {
            if (!$createParents || !mkdir($directory, 0755)) {
                throw new RuntimeException('Nelze vytvořit adresář aktualizace.');
            }
        }
    }

    $target = $directory . '/' . $name;
    if (is_link($target) || (file_exists($target) && !is_file($target))) {
        throw new RuntimeException('Nebezpečný cílový soubor aktualizace.');
    }
    return $target;
}

/** Return a file digest, failing closed when a read fails. */
function file_digest(string $path): string
{
    $digest = hash_file('sha256', $path);
    if (!is_string($digest)) {
        throw new RuntimeException('Nelze ověřit soubor aktualizace.');
    }
    return $digest;
}

/** Atomically replace one file using a verified sibling temporary file. */
function publish_file(string $source, string $target, string $digest, int $mode): void
{
    $temporary = dirname($target) . '/.ppl-update-' . bin2hex(random_bytes(12)) . '.tmp';
    try {
        if (!copy($source, $temporary) || !hash_equals($digest, file_digest($temporary))
            || !chmod($temporary, $mode & 0777) || !rename($temporary, $target)) {
            throw new RuntimeException('Nelze bezpečně nahradit soubor aplikace.');
        }
        clearstatcache(true, $target);
        if (function_exists('opcache_invalidate') && str_ends_with($target, '.php')) {
            @opcache_invalidate($target, true);
        }
    } finally {
        if (is_file($temporary)) {
            @unlink($temporary);
        }
    }
}

/** Return the fixed private updater directory. */
function work_directory(string $root): string
{
    return $root . '/cache/ppl-update';
}

/** Return the journal path; existence means that activation is not committed. */
function journal_path(string $root): string
{
    return work_directory($root) . '/activation.json';
}

/** Atomically persist the activation journal before changing any live files. */
function write_journal(string $root, array $files): void
{
    $path = journal_path($root);
    $temporary = $path . '.tmp';
    $json = json_encode(['format' => 1, 'files' => $files], JSON_THROW_ON_ERROR);
    if (file_put_contents($temporary, $json, LOCK_EX) !== strlen($json)
        || !chmod($temporary, 0600) || !rename($temporary, $path)) {
        throw new RuntimeException('Nelze uložit záchranný žurnál.');
    }
}

/** Validate a journal entry independently of the release transport. */
function valid_journal_entry(array $entry): bool
{
    return is_string($entry['path'] ?? null) && managed_path($entry['path'])
        && array_key_exists('old', $entry)
        && ($entry['old'] === null || (is_string($entry['old']) && preg_match('/\A[a-f0-9]{64}\z/D', $entry['old']) === 1))
        && is_string($entry['new'] ?? null) && preg_match('/\A[a-f0-9]{64}\z/D', $entry['new']) === 1
        && is_int($entry['mode'] ?? null) && $entry['mode'] >= 0 && $entry['mode'] <= 0777;
}

/** Restore original bytes after an interrupted or failed activation; caller owns EX lock. */
function recover_activation(string $root): void
{
    $journal = journal_path($root);
    if (!is_file($journal)) {
        return;
    }
    $work = work_directory($root);
    if (is_link($journal) || is_link($work) || is_link($root . '/cache')) {
        throw new RuntimeException('Nebezpečný záchranný žurnál.');
    }
    $raw = file_get_contents($journal);
    $state = is_string($raw) ? json_decode($raw, true, 32, JSON_THROW_ON_ERROR) : null;
    if (!is_array($state) || ($state['format'] ?? null) !== 1
        || !is_array($state['files'] ?? null) || !array_is_list($state['files'])) {
        throw new RuntimeException('Neplatný záchranný žurnál.');
    }

    // Verify the complete journal and all required backups before any restoration.
    $seen = [];
    foreach ($state['files'] as $entry) {
        if (!is_array($entry) || !valid_journal_entry($entry) || isset($seen[$entry['path']])) {
            throw new RuntimeException('Neplatný záznam záchranného žurnálu.');
        }
        $seen[$entry['path']] = true;
        $target = safe_path($root, $entry['path'], true);
        $actual = is_file($target) ? file_digest($target) : null;
        if ($actual !== $entry['old'] && $actual !== $entry['new']) {
            throw new RuntimeException('Aktualizovaný soubor byl změněn mimo updater.');
        }
        if ($entry['old'] !== null) {
            $backup = safe_path($work . '/backup', $entry['path']);
            if (!is_file($backup) || !hash_equals($entry['old'], file_digest($backup))) {
                throw new RuntimeException('Záloha aktualizace není úplná.');
            }
        }
    }

    foreach (array_reverse($state['files']) as $entry) {
        $target = safe_path($root, $entry['path'], true);
        $actual = is_file($target) ? file_digest($target) : null;
        if ($actual === $entry['old']) {
            continue;
        }
        if ($entry['old'] === null) {
            if (!unlink($target)) {
                throw new RuntimeException('Nelze odstranit nový soubor při obnově.');
            }
            if (function_exists('opcache_invalidate') && str_ends_with($target, '.php')) {
                @opcache_invalidate($target, true);
            }
        } else {
            $backup = safe_path($work . '/backup', $entry['path']);
            publish_file($backup, $target, $entry['old'], $entry['mode']);
        }
    }

    if (!unlink($journal)) {
        throw new RuntimeException('Nelze ukončit obnovu aktualizace.');
    }
}

/** Emit a short maintenance response without loading the normal runtime. */
function maintenance_response(): never
{
    http_response_code(503);
    header('Retry-After: 10');
    header('Cache-Control: no-store');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'PPL Study: probíhá aktualizace nebo její obnova. Obnovte stránku za chvíli.';
    exit;
}

/** Enter the shared runtime gate; retain the resource for the entire request. */
function enter_gate(string $root): void
{
    if (!is_file($root . '/config.php')) {
        return;
    }
    if (is_link($root . '/cache') || is_link($root . '/cache/ppl-update.lock')) {
        maintenance_response();
    }
    $handle = @fopen($root . '/cache/ppl-update.lock', 'c+b');
    if ($handle === false || !flock($handle, LOCK_SH | LOCK_NB)) {
        maintenance_response();
    }
    $GLOBALS['ppl_update_gate_handle'] = $handle;

    if (is_file(journal_path($root))) {
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            maintenance_response();
        }
        try {
            recover_activation($root);
        } catch (\Throwable $exception) {
            // Do not load mixed application modules or disclose paths/secrets.
            maintenance_response();
        }
        // Start a fresh request after recovery; this request may have loaded a
        // different generation of the early gate before recovery completed.
        maintenance_response();
    }
}

/** Upgrade this request's shared lock to exclusive before staging/activation. */
function acquire_exclusive_gate(): void
{
    $handle = $GLOBALS['ppl_update_gate_handle'] ?? null;
    if (!is_resource($handle) || !flock($handle, LOCK_EX | LOCK_NB)) {
        throw new RuntimeException('Web právě zpracovává jiný požadavek. Zkuste aktualizaci znovu.');
    }
}

/** Release exclusive ownership after the updating request has finished its work. */
function release_gate(): void
{
    $handle = $GLOBALS['ppl_update_gate_handle'] ?? null;
    if (is_resource($handle)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        unset($GLOBALS['ppl_update_gate_handle']);
    }
}
