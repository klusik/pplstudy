<?php

/**
 * Project: PPL Study
 * File: scripts/test_updater.php
 * Module Type: CLI regression test
 *
 * Purpose:
 *   Exercise the actual release parser, path allowlist, ZIP staging, digest
 *   verification, activation journal and interrupted-write recovery.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Core {
    /** Supply empty runtime limit overrides for isolated CLI tests. */
    function study_config(): array { return []; }
}

namespace {
    use function PplStudy\Update\allowed_download_url;
    use function PplStudy\Update\file_digest;
    use function PplStudy\Update\journal_path;
    use function PplStudy\Update\managed_path;
    use function PplStudy\Update\parse_release;
    use function PplStudy\Update\prepare_activation;
    use function PplStudy\Update\publish_file;
    use function PplStudy\Update\recover_activation;
    use function PplStudy\Update\remove_workspace;
    use function PplStudy\Update\safe_path;
    use function PplStudy\Update\stage_archive;
    use function PplStudy\Update\validate_manifest;
    use function PplStudy\Update\verify_migrations_unchanged;
    use function PplStudy\Update\write_journal;

    if (PHP_SAPI !== 'cli' || $argc !== 2) {
        exit(1);
    }
    $root = dirname(__DIR__);
    $metadata = json_decode(file_get_contents($root . '/release-metadata.json'), true, 32, JSON_THROW_ON_ERROR);
    $version = $metadata['current_version'];
    define('PplStudy\Core\APP_VERSION', $version);
    define('PplStudy\Core\APP_SCHEMA_VERSION', $metadata['releases'][$version]['application_schema']);
    require $root . '/app/configuration_defaults.php';
    require $root . '/app/update_gate.php';
    require $root . '/app/services/updater.php';

    /** Assert a runtime invariant even when PHP assertions are disabled. */
    function check(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    /** Expect a rejected input without relying on PHP assertion configuration. */
    function rejected(callable $operation, string $label): void
    {
        try {
            $operation();
        } catch (Throwable $exception) {
            return;
        }
        throw new RuntimeException('Expected rejection: ' . $label);
    }

    $archive = realpath($argv[1]);
    check(is_string($archive), 'Release ZIP not found');
    $temporary = sys_get_temp_dir() . '/ppl-updater-test-' . bin2hex(random_bytes(8));
    check(mkdir($temporary, 0700), 'Cannot create test workspace');
    try {
        foreach (['config.php', '.git/config', 'cache/installed.lock', '../app/bootstrap.php',
            'app/../config.php', 'public/assets/evil.php', 'data/source-documents/secret.pdf',
            'app//bootstrap.php', '/app/bootstrap.php', 'app\\bootstrap.php'] as $unsafe) {
            check(!managed_path($unsafe), 'Unsafe path accepted: ' . $unsafe);
        }
        check(managed_path('app/bootstrap.php'), 'Expected runtime path rejected');
        check(allowed_download_url('https://api.github.com/repos/klusik/pplstudy/releases/latest', true), 'Canonical API rejected');
        check(allowed_download_url('https://release-assets.githubusercontent.com/example?signature=123', false), 'Expected redirect rejected');
        foreach (['http://release-assets.githubusercontent.com/example', 'https://evil.example/a',
            'https://release-assets.githubusercontent.com.evil.example/a', 'https://user@release-assets.githubusercontent.com/a',
            'https://release-assets.githubusercontent.com:444/a'] as $unsafe) {
            check(!allowed_download_url($unsafe, false), 'Unsafe redirect accepted');
        }

        $release = ['tag_name' => 'v_' . $version, 'draft' => false, 'prerelease' => false, 'assets' => [[
            'name' => 'PPL-Study-' . $version . '-deploy.zip', 'id' => 123, 'size' => filesize($archive),
            'state' => 'uploaded', 'digest' => 'sha256:' . file_digest($archive),
            'browser_download_url' => 'https://github.com/klusik/pplstudy/releases/download/v_' . $version . '/PPL-Study-' . $version . '-deploy.zip',
        ]]];
        check(parse_release($release)['sha256'] === file_digest($archive), 'Release digest mismatch');
        check(allowed_download_url($release['assets'][0]['browser_download_url'], false), 'Canonical public asset rejected');
        $bad = $release;
        $bad['assets'][0]['browser_download_url'] = 'https://github.com/other/repo/releases/download/v_' . $version . '/PPL-Study-' . $version . '-deploy.zip';
        rejected(static fn () => parse_release($bad), 'foreign asset URL');
        check(!allowed_download_url('https://github.com/klusik/pplstudy/releases/download/v_0.2.0/PPL-Study-0.3.0-deploy.zip', false), 'Mismatched version accepted');
        $bad = $release;
        $bad['draft'] = true;
        rejected(static fn () => parse_release($bad), 'draft release');
        $bad = $release;
        $bad['assets'][0]['digest'] = null;
        rejected(static fn () => parse_release($bad), 'missing digest');
        $bad = $release;
        $bad['assets'][] = $bad['assets'][0];
        rejected(static fn () => parse_release($bad), 'duplicate asset');

        check(mkdir($temporary . '/stage'), 'Stage directory');
        $files = stage_archive($archive, $temporary . '/stage', $version);
        check(count($files) >= 100, 'Package inventory unexpectedly small');
        verify_migrations_unchanged($root, $files);
        $changedMigration = $files;
        $migration = array_values(array_filter(array_keys($files), static fn (string $path): bool => str_starts_with($path, 'database/migrations/')))[0];
        $changedMigration[$migration]['sha256'] = str_repeat('0', 64);
        rejected(static fn () => verify_migrations_unchanged($root, $changedMigration), 'changed migration');
        rejected(static fn () => stage_archive($archive, $temporary . '/stage', '99.0.0'), 'wrong release version');

        // A tampered archive must never get as far as activation.
        $badArchive = $temporary . '/bad.zip';
        check(copy($archive, $badArchive), 'Copy fixture archive');
        $zip = new ZipArchive();
        check($zip->open($badArchive) === true, 'Open fixture archive');
        check($zip->addFromString('../escape.php', '<?php echo 1;'), 'Append traversal fixture');
        check($zip->close(), 'Close fixture archive');
        check(mkdir($temporary . '/bad-stage'), 'Bad stage directory');
        rejected(static fn () => stage_archive($badArchive, $temporary . '/bad-stage', $version), 'archive traversal');
        check(!file_exists($temporary . '/escape.php'), 'Archive escaped staging');

        $fixture = $temporary . '/installation';
        check(mkdir($fixture), 'Installation fixture');
        check(mkdir($fixture . '/app'), 'Installation app');
        check(mkdir($fixture . '/cache/ppl-update/backup', 0700, true), 'Recovery workspace');
        check(file_put_contents($fixture . '/config.php', 'local-secret') !== false, 'Local config');
        check(file_put_contents($fixture . '/app/bootstrap.php', 'old bootstrap') !== false, 'Original app file');
        $subset = array_intersect_key($files, array_flip(['app/bootstrap.php', 'app/services/updater.php']));
        $journal = prepare_activation($fixture, $fixture . '/cache/ppl-update/backup', $subset);
        check(count($journal) === 2, 'Expected two changed files');
        write_journal($fixture, $journal);
        foreach ($journal as $entry) {
            publish_file(safe_path($temporary . '/stage', $entry['path']), safe_path($fixture, $entry['path']), $entry['new'], $entry['mode']);
        }
        check(is_file(journal_path($fixture)), 'Activation journal was not durable');
        check(file_get_contents($fixture . '/config.php') === 'local-secret', 'Local config changed');
        recover_activation($fixture);
        check(file_get_contents($fixture . '/app/bootstrap.php') === 'old bootstrap', 'Original file was not restored');
        check(!file_exists($fixture . '/app/services/updater.php'), 'Previously absent file was not removed');
        check(!file_exists(journal_path($fixture)), 'Recovery journal was not cleared');
        check(file_get_contents($fixture . '/config.php') === 'local-secret', 'Recovery modified local config');

        if (function_exists('symlink') && @symlink($root . '/app', $fixture . '/app/linked')) {
            rejected(static fn () => safe_path($fixture, 'app/linked/bootstrap.php'), 'symlink traversal');
            unlink($fixture . '/app/linked');
        }
        echo "Updater regression checks passed.\n";
    } finally {
        remove_workspace($temporary);
    }
}
