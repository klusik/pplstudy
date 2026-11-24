<?php

/**
 * Project: PPL Study
 * File: app/views/admin_updates.php
 * Module Type: Admin View
 *
 * Purpose:
 *   Show deployment readiness, the installed version and a verified release;
 *   render only explicit administrator POST actions with CSRF protection.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Views;

use const PplStudy\Core\APP_VERSION;
use function PplStudy\Core\csrf_field;
use function PplStudy\Core\e;
use function PplStudy\Core\study_url;

/** Render the administrator update page. */
function render_admin_updates(array $problems, ?array $release, string $message, string $error): void
{
    render_admin_header('Aktualizace', 'updates');
    ?>
<div class="admin-title"><div><span class="eyebrow">Administrace</span><h1>Aktualizace aplikace</h1></div><span class="version-badge">v<?= e(APP_VERSION) ?></span></div>
<?php render_flash($message); render_flash($error, 'error'); ?>
<section class="panel">
    <h2>Stav aktualizací</h2>
    <dl class="definition-list">
        <div><dt>Nainstalovaná verze</dt><dd><?= e(APP_VERSION) ?></dd></div>
        <div><dt>Zdroj</dt><dd>klusik/pplstudy · veřejné stabilní GitHub Releases</dd></div>
        <?php if ($release !== null): ?>
        <div><dt>Dostupná verze</dt><dd><?= e($release['version']) ?></dd></div>
        <div><dt>Velikost balíčku</dt><dd><?= e(number_format($release['size'] / 1048576, 2, ',', ' ')) ?> MiB</dd></div>
        <div><dt>SHA-256</dt><dd><code style="overflow-wrap:anywhere"><?= e($release['sha256']) ?></code></dd></div>
        <?php endif; ?>
    </dl>
    <?php if ($problems !== []): ?>
        <div class="flash error"><strong>Updater zatím není připraven.</strong><ul><?php foreach ($problems as $problem): ?><li><?= e($problem) ?></li><?php endforeach; ?></ul></div>
        <p>Pro veřejný repozitář není potřeba žádný GitHub token. Ověřte dostupnost PHP rozšíření a oprávnění souborů.</p>
    <?php else: ?>
        <form method="post" action="<?= e(study_url('/admin/updates')) ?>">
            <?= csrf_field() ?>
            <button class="button secondary" name="action" value="check" type="submit">Zkontrolovat aktualizace</button>
        </form>
    <?php endif; ?>
</section>
<?php if ($release !== null && $problems === []): ?>
<section class="panel">
    <h2>Ruční aktualizace</h2>
    <?php if (version_compare($release['version'], APP_VERSION, '<')): ?>
        <p>Nejnovější publikovaný release je starší než instalace. Downgrade není povolen.</p>
    <?php else: ?>
        <p><?= version_compare($release['version'], APP_VERSION, '>') ? 'Je dostupná nová verze.' : 'Aplikace má aktuální verzi. Vynucená aktualizace ověří a opraví soubory této verze.' ?></p>
        <p>Před aktualizací zálohujte databázi a soubory. Během nasazení bude web krátce nedostupný. Updater nemění lokální konfiguraci, účty, databázi otázek ani uložené studijní PDF. Změna schématu vyžaduje ruční nasazení.</p>
        <form method="post" class="stack-form" action="<?= e(study_url('/admin/updates')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="expected_version" value="<?= e($release['version']) ?>">
            <input type="hidden" name="expected_digest" value="<?= e($release['sha256']) ?>">
            <input type="hidden" name="expected_asset" value="<?= (int) $release['asset_id'] ?>">
            <?php if (version_compare($release['version'], APP_VERSION, '>')): ?>
            <button class="button primary" name="action" value="install" type="submit">Nainstalovat novou verzi</button>
            <?php endif; ?>
            <button class="button secondary" name="action" value="force" type="submit">Vynutit aktualizaci / opravit soubory</button>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>
<section class="panel"><h2>Obnova</h2><p>Při přerušení aktivace se příští požadavek pokusí automaticky obnovit původní soubory ze zálohy. Pokud se web neobnoví, použijte FTP a postup v README.md. Nikdy nemažte aktivační žurnál před kontrolou obnovy.</p></section>
<?php
    render_admin_footer();
}
