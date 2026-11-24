<?php

/**
 * Project: PPL Study
 * File: app/services/updater.php
 * Module Type: Administrator Update Service
 *
 * Purpose:
 *   Discover stable releases from the fixed public GitHub repository, verify
 *   the exact release asset, stage a bounded managed-file inventory, and publish
 *   it under the early runtime gate with a durable rollback journal.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Update;

use RuntimeException;
use ZipArchive;
use const PplStudy\Core\APP_SCHEMA_VERSION;
use const PplStudy\Core\APP_VERSION;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\study_project_root;

const REPOSITORY_API = 'https://api.github.com/repos/klusik/pplstudy';

/** Return readiness problems without exposing credentials or filesystem paths. */
function readiness(): array
{
    $problems = [];
    if (!extension_loaded('curl') || !class_exists(ZipArchive::class)) {
        $problems[] = 'PHP vyžaduje rozšíření cURL a ZipArchive.';
    }
    if (!is_writable(study_project_root() . '/cache')) {
        $problems[] = 'Adresář cache není zapisovatelný.';
    }
    if (filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)
        && !filter_var(ini_get('opcache.validate_timestamps'), FILTER_VALIDATE_BOOLEAN)
        && !function_exists('opcache_invalidate')) {
        $problems[] = 'OPcache neumožňuje bezpečně obnovit změněné PHP soubory.';
    }
    return $problems;
}

/** Restrict outbound HTTPS to the repository API and GitHub's asset storage. */
function allowed_download_url(string $url, bool $api): bool
{
    $parts = parse_url($url);
    if (!is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
        || (isset($parts['port']) && $parts['port'] !== 443)) {
        return false;
    }
    $host = strtolower((string) ($parts['host'] ?? ''));
    if ($api) {
        return $host === 'api.github.com'
            && ($parts['path'] ?? null) === '/repos/klusik/pplstudy/releases/latest'
            && !isset($parts['query']);
    }
    if ($host === 'github.com') {
        return !isset($parts['query'])
            && preg_match('~\A/klusik/pplstudy/releases/download/v_?([0-9]+\.[0-9]+\.[0-9]+)/PPL-Study-\1-deploy\.zip\z~D', (string) ($parts['path'] ?? '')) === 1;
    }
    return in_array($host, ['release-assets.githubusercontent.com', 'objects.githubusercontent.com', 'github-releases.githubusercontent.com'], true);
}

/** Fetch one bounded public HTTPS response without repository credentials. */
function github_request(string $url, $output, int $limit, bool $asset, bool $api): array
{
    if (!allowed_download_url($url, $api)) {
        throw new RuntimeException('GitHub vrátil nepovolenou adresu ke stažení.');
    }
    $curl = curl_init($url);
    if ($curl === false) {
        throw new RuntimeException('Nelze navázat spojení s GitHubem.');
    }

    $location = '';
    $bytes = 0;
    $headers = ['Accept: ' . ($asset ? 'application/octet-stream' : 'application/vnd.github+json')];
    if ($api) {
        $headers[] = 'X-GitHub-Api-Version: 2022-11-28';
    }
    try {
        $configured = curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'PPL-Study-Updater/' . APP_VERSION,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => study_limit('update_http_timeout_seconds'),
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$location): int {
                if (stripos($line, 'Location:') === 0) {
                    $location = trim(substr($line, 9));
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use ($output, $limit, &$bytes): int {
                $length = strlen($chunk);
                if ($bytes + $length > $limit) {
                    return 0;
                }
                $written = fwrite($output, $chunk);
                if ($written !== $length) {
                    return 0;
                }
                $bytes += $written;
                return $written;
            },
        ]);
        if (!$configured) {
            throw new RuntimeException('Nelze bezpečně nakonfigurovat HTTPS přenos.');
        }
        $success = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if ($success === false) {
            throw new RuntimeException('Stažení z GitHubu selhalo nebo překročilo bezpečnostní limit.');
        }
        return ['status' => $status, 'location' => $location, 'bytes' => $bytes];
    } finally {
        curl_close($curl);
    }
}

/** Validate the stable release and bind the asset name, ID, length and digest. */
function parse_release(array $release): array
{
    $tag = $release['tag_name'] ?? null;
    if (!is_string($tag) || preg_match('/\Av_?((?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*))\z/D', $tag, $matches) !== 1
        || ($release['draft'] ?? true) !== false || ($release['prerelease'] ?? true) !== false) {
        throw new RuntimeException('Nejnovější GitHub release nemá platnou stabilní verzi.');
    }
    $version = $matches[1];
    $name = 'PPL-Study-' . $version . '-deploy.zip';
    $assets = array_values(array_filter(
        is_array($release['assets'] ?? null) ? $release['assets'] : [],
        static fn ($asset): bool => is_array($asset) && ($asset['name'] ?? null) === $name
    ));
    if (count($assets) !== 1) {
        throw new RuntimeException('Release nemá právě jeden distribuční ZIP.');
    }
    $asset = $assets[0];
    $digest = $asset['digest'] ?? null;
    $id = $asset['id'] ?? null;
    $size = $asset['size'] ?? null;
    $url = 'https://github.com/klusik/pplstudy/releases/download/' . $tag . '/' . $name;
    if (($asset['browser_download_url'] ?? null) !== $url || !allowed_download_url($url, false)) {
        throw new RuntimeException('Release neobsahuje kanonickou adresu distribučního balíčku.');
    }
    if (!is_int($id) || $id <= 0 || !is_int($size) || $size <= 0
        || $size > study_limit('update_archive_max_bytes')
        || ($asset['state'] ?? null) !== 'uploaded'
        || !is_string($digest) || preg_match('/\Asha256:([a-f0-9]{64})\z/D', $digest, $hash) !== 1) {
        throw new RuntimeException('Release asset nemá ověřitelnou identitu, velikost a SHA-256.');
    }

    return ['version' => $version, 'tag' => $tag, 'asset_id' => $id, 'size' => $size, 'sha256' => $hash[1], 'url' => $url];
}

/** Read the newest stable release using the public GitHub API. */
function latest_release(): array
{
    $problems = readiness();
    if ($problems !== []) {
        throw new RuntimeException(implode(' ', $problems));
    }
    $stream = fopen('php://temp', 'w+b');
    if ($stream === false) {
        throw new RuntimeException('Nelze připravit GitHub odpověď.');
    }
    try {
        $response = github_request(REPOSITORY_API . '/releases/latest', $stream, study_limit('update_metadata_max_bytes'), false, true);
        if ($response['status'] !== 200) {
            if (in_array($response['status'], [403, 429], true)) {
                throw new RuntimeException('GitHub omezil požadavky. Zkuste kontrolu později.');
            }
            throw new RuntimeException('Veřejný GitHub release není dostupný (HTTP ' . $response['status'] . ').');
        }
        rewind($stream);
        $data = json_decode(stream_get_contents($stream), true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException('GitHub vrátil neplatný release.');
        }
        return parse_release($data);
    } finally {
        fclose($stream);
    }
}

/** Download a pinned asset to disk and check its exact length and provider digest. */
function download_release(array $release, string $archive): void
{
    $stream = fopen($archive, 'xb');
    if ($stream === false) {
        throw new RuntimeException('Nelze vytvořit dočasný ZIP.');
    }
    try {
        $url = $release['url'];
        $response = github_request($url, $stream, $release['size'], true, false);
        for ($redirect = 0; $redirect < 2 && in_array($response['status'], [301, 302, 303, 307, 308], true); $redirect++) {
            if (!ftruncate($stream, 0) || !rewind($stream)) {
                throw new RuntimeException('Nelze připravit přesměrované stažení.');
            }
            $response = github_request($response['location'], $stream, $release['size'], true, false);
        }
        if ($response['status'] !== 200 || $response['bytes'] !== $release['size'] || !fflush($stream)) {
            throw new RuntimeException('Stažení ZIP není úplné.');
        }
    } finally {
        fclose($stream);
    }
    if (!hash_equals($release['sha256'], file_digest($archive))) {
        throw new RuntimeException('SHA-256 distribučního ZIP nesouhlasí s GitHubem.');
    }
}

/** Remove only the updater's isolated workspace, never following symlinks. */
function remove_workspace(string $path): void
{
    if (is_link($path)) {
        throw new RuntimeException('Nebezpečný pracovní adresář updateru.');
    }
    if (!is_dir($path)) {
        if (file_exists($path)) {
            throw new RuntimeException('Neplatný pracovní adresář updateru.');
        }
        return;
    }
    foreach (new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS) as $item) {
        if ($item->isLink() || (!$item->isDir() && !$item->isFile())) {
            throw new RuntimeException('Nebezpečný soubor v pracovním adresáři updateru.');
        }
        if ($item->isDir()) {
            remove_workspace($item->getPathname());
        } elseif (!unlink($item->getPathname())) {
            throw new RuntimeException('Nelze vyčistit pracovní adresář updateru.');
        }
    }
    if (!rmdir($path)) {
        throw new RuntimeException('Nelze vyčistit pracovní adresář updateru.');
    }
}

/** Read and validate a complete manifest, including a bounded expanded size. */
function validate_manifest(array $manifest, string $version): array
{
    if (($manifest['format'] ?? null) !== 1 || ($manifest['project'] ?? null) !== 'PPL Study'
        || ($manifest['version'] ?? null) !== $version || ($manifest['schema'] ?? null) !== APP_SCHEMA_VERSION) {
        throw new RuntimeException('Balíček má nekompatibilní verzi nebo databázové schéma. Změna schématu vyžaduje ruční nasazení a migraci.');
    }
    $files = $manifest['files'] ?? null;
    if (!is_array($files) || !array_is_list($files) || count($files) === 0
        || count($files) > study_limit('update_max_files')) {
        throw new RuntimeException('Neplatný počet souborů v manifestu.');
    }
    $total = 0;
    $byPath = [];
    $caseFold = [];
    foreach ($files as $entry) {
        $path = $entry['path'] ?? null;
        $size = $entry['size'] ?? null;
        $digest = $entry['sha256'] ?? null;
        if (!is_string($path) || !managed_path($path) || isset($caseFold[strtolower($path)])
            || !is_int($size) || $size < 0 || !is_string($digest)
            || preg_match('/\A[a-f0-9]{64}\z/D', $digest) !== 1) {
            throw new RuntimeException('Manifest obsahuje nepovolený nebo duplicitní soubor.');
        }
        $total += $size;
        if ($total > study_limit('update_expanded_max_bytes')) {
            throw new RuntimeException('Rozbalený balíček překračuje bezpečnostní limit.');
        }
        $caseFold[strtolower($path)] = true;
        $byPath[$path] = $entry;
    }
    foreach (['.htaccess', 'index.php', 'public/index.php', 'app/bootstrap.php', 'app/update_gate.php',
        'app/services/updater.php', 'release-metadata.json', 'data/question-bank.json'] as $required) {
        if (!isset($byPath[$required])) {
            throw new RuntimeException('Balíček neobsahuje všechny povinné soubory.');
        }
    }
    return $byPath;
}

/** Stage each expected regular file, never using extractTo or trusting ZIP paths. */
function stage_archive(string $archive, string $stage, string $version): array
{
    $zip = new ZipArchive();
    if ($zip->open($archive, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
        throw new RuntimeException('Distribuční ZIP je poškozený.');
    }
    try {
        $manifestStat = $zip->statName('update-manifest.json');
        if (!is_array($manifestStat) || $manifestStat['size'] > study_limit('update_metadata_max_bytes')) {
            throw new RuntimeException('ZIP nemá platný manifest.');
        }
        $raw = $zip->getFromName('update-manifest.json', study_limit('update_metadata_max_bytes') + 1);
        $manifest = is_string($raw) ? json_decode($raw, true, 32, JSON_THROW_ON_ERROR) : null;
        if (!is_array($manifest)) {
            throw new RuntimeException('Neplatný manifest aktualizace.');
        }
        $files = validate_manifest($manifest, $version);
        if ($zip->numFiles !== count($files) + 1) {
            throw new RuntimeException('ZIP obsahuje neočekávané položky.');
        }
        $seen = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name) || isset($seen[$name])
                || ($name !== 'update-manifest.json' && !isset($files[$name]))) {
                throw new RuntimeException('ZIP obsahuje nepovolenou nebo duplicitní cestu.');
            }
            $seen[$name] = true;
            if ($name === 'update-manifest.json') {
                continue;
            }
            $stat = $zip->statIndex($i);
            $entry = $files[$name];
            if (!is_array($stat) || $stat['size'] !== $entry['size']) {
                throw new RuntimeException('Velikost souboru neodpovídá manifestu.');
            }
            $target = safe_path($stage, $name, true);
            $input = $zip->getStream($name);
            if ($input === false) {
                throw new RuntimeException('Nelze číst soubor ze ZIP.');
            }
            $output = fopen($target, 'xb');
            try {
                if ($output === false || stream_copy_to_stream($input, $output, $entry['size'] + 1) !== $entry['size']
                    || !fflush($output)) {
                    throw new RuntimeException('Nelze úplně rozbalit soubor.');
                }
            } finally {
                fclose($input);
                if (is_resource($output)) {
                    fclose($output);
                }
            }
            if (!hash_equals($entry['sha256'], file_digest($target))) {
                throw new RuntimeException('Soubor ze ZIP neodpovídá manifestu.');
            }
        }
        return $files;
    } finally {
        $zip->close();
    }
}

/** Verify that same-schema code updates do not add, change, or remove migrations. */
function verify_migrations_unchanged(string $root, array $files): void
{
    $current = glob($root . '/database/migrations/*.php');
    if ($current === false) {
        throw new RuntimeException('Nelze ověřit databázové migrace.');
    }
    $known = [];
    foreach ($current as $path) {
        $relative = 'database/migrations/' . basename($path);
        $safe = safe_path($root, $relative);
        if (!isset($files[$relative]) || !hash_equals($files[$relative]['sha256'], file_digest($safe))) {
            throw new RuntimeException('Balíček mění databázové migrace. Je nutné ruční nasazení.');
        }
        $known[$relative] = true;
    }
    foreach ($files as $path => $entry) {
        if (str_starts_with($path, 'database/migrations/') && !isset($known[$path])) {
            throw new RuntimeException('Balíček přidává databázovou migraci. Je nutné ruční nasazení.');
        }
    }
}

/** Back up every changed original before writing the activation journal. */
function prepare_activation(string $root, string $backup, array $files): array
{
    $journal = [];
    foreach ($files as $path => $entry) {
        $target = safe_path($root, $path, true);
        $old = is_file($target) ? file_digest($target) : null;
        if ($old === $entry['sha256']) {
            continue;
        }
        $mode = $old === null ? 0644 : (fileperms($target) & 0777);
        if (!is_writable(dirname($target))) {
            throw new RuntimeException('PHP nemá právo měnit soubory aplikace. Ověřte oprávnění na hostingu.');
        }
        if ($old !== null) {
            $saved = safe_path($backup, $path, true);
            if (!copy($target, $saved) || !hash_equals($old, file_digest($saved))) {
                throw new RuntimeException('Nelze bezpečně zazálohovat původní soubor.');
            }
        }
        $journal[] = ['path' => $path, 'old' => $old, 'new' => $entry['sha256'], 'mode' => $mode];
    }

    // Keep the gate and entry points stable until dependencies have been published.
    $last = ['app/update_gate.php' => 1, 'app/bootstrap.php' => 2, 'public/index.php' => 3, 'index.php' => 4];
    usort($journal, static fn (array $a, array $b): int =>
        (($last[$a['path']] ?? 0) <=> ($last[$b['path']] ?? 0)) ?: strcmp($a['path'], $b['path']));
    return $journal;
}

/** Publish the verified release under the exclusive gate, recovering on failure. */
function install_release(array $release): int
{
    if (version_compare($release['version'], APP_VERSION, '<')) {
        throw new RuntimeException('Downgrade není povolen.');
    }
    $root = study_project_root();
    $work = work_directory($root);
    $jobPath = $root . '/cache/ppl-update-job.lock';
    if (is_link($jobPath)) {
        throw new RuntimeException('Nebezpečný zámek aktualizace.');
    }
    $job = @fopen($jobPath, 'c+b');
    if ($job === false || !flock($job, LOCK_EX | LOCK_NB)) {
        if (is_resource($job)) {
            fclose($job);
        }
        throw new RuntimeException('Jiný administrátor již připravuje aktualizaci.');
    }
    $exclusive = false;
    try {
        if (file_exists(journal_path($root))) {
            throw new RuntimeException('Nejdříve je nutné dokončit obnovu předchozí aktualizace.');
        }
        remove_workspace($work);
        if (!mkdir($work, 0700) || file_put_contents($work . '/.htaccess', "Require all denied\n") === false
            || !mkdir($work . '/stage', 0700) || !mkdir($work . '/backup', 0700)) {
            throw new RuntimeException('Nelze připravit soukromý pracovní adresář updateru.');
        }
        $archive = $work . '/release.zip';
        download_release($release, $archive);
        $files = stage_archive($archive, $work . '/stage', $release['version']);
        // Network transfer and decompression hold only a shared request gate.
        // Stop other application requests only for backup and live activation.
        acquire_exclusive_gate();
        $exclusive = true;
        verify_migrations_unchanged($root, $files);
        $metadata = json_decode(file_get_contents($root . '/release-metadata.json'), true, 32, JSON_THROW_ON_ERROR);
        if (($metadata['current_version'] ?? null) !== APP_VERSION) {
            throw new RuntimeException('Nainstalovaná verze se mezitím změnila. Obnovte stránku.');
        }
        $journal = prepare_activation($root, $work . '/backup', $files);
        write_journal($root, $journal);
        try {
            foreach ($journal as $entry) {
                $source = safe_path($work . '/stage', $entry['path']);
                $target = safe_path($root, $entry['path']);
                publish_file($source, $target, $entry['new'], $entry['mode']);
            }
            foreach ($journal as $entry) {
                if (!hash_equals($entry['new'], file_digest(safe_path($root, $entry['path'])))) {
                    throw new RuntimeException('Kontrola nasazených souborů selhala.');
                }
            }
            if (!unlink(journal_path($root))) {
                throw new RuntimeException('Nelze potvrdit dokončení aktualizace.');
            }
        } catch (\Throwable $exception) {
            recover_activation($root);
            throw $exception;
        }
        // Retain the last workspace backup for FTP recovery. The next update
        // replaces it only after confirming no unfinished activation exists.
        return count($journal);
    } finally {
        // The request still has loaded the previous PHP generation. Keep its
        // runtime gate until request shutdown rather than racing another update.
        if ($exclusive && is_file(journal_path($root))) {
            // A failed recovery must never serve another mixed runtime request.
            maintenance_response();
        }
        flock($job, LOCK_UN);
        fclose($job);
    }
}
