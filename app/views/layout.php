<?php

/**
 * Project: PPL Study
 * File: app/views/layout.php
 * Module Type: View Module
 *
 * Purpose:
 *   Owns the public page chrome, metadata, authentication-aware navigation,
 *   and footer.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Views;

use const PplStudy\Core\APP_VERSION;
use function PplStudy\Core\csrf_field;
use function PplStudy\Core\current_user;
use function PplStudy\Core\e;
use function PplStudy\Core\is_admin;
use function PplStudy\Core\is_authenticated;
use function PplStudy\Core\password_change_required;
use function PplStudy\Core\study_url;
use function PplStudy\Services\app_setting;

/** Render the shared public document header. */
function render_header(string $title, string $description = '', string $active = ''): void
{
    $siteName = app_setting('site_name');
    $fullTitle = $title === '' || $title === $siteName ? $siteName : $title . ' | ' . $siteName;
    $description = $description !== '' ? $description : app_setting('site_subtitle');
    $assetVersion = rawurlencode(APP_VERSION);
    $authenticated = is_authenticated();
    $passwordPending = $authenticated && password_change_required();
    $user = $authenticated ? current_user() : null;
    ?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#10253f">
    <title><?= e($fullTitle) ?></title>
    <link rel="stylesheet" href="<?= e(study_url('/public/assets/app.css?v=' . $assetVersion)) ?>">
</head>
<body>
<header class="site-header">
    <div class="shell nav-shell">
        <a class="brand" href="<?= e(study_url('/')) ?>" aria-label="Domů">
            <span class="brand-mark" aria-hidden="true">✈</span>
            <span><strong><?= e($siteName) ?></strong><small><?= e(app_setting('site_subtitle')) ?></small></span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Otevřít menu">Menu</button>
        <nav class="main-nav" data-nav>
            <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= e(study_url('/')) ?>">Přehled</a>
            <?php if ($authenticated && !$passwordPending): ?>
                <a class="<?= $active === 'study' ? 'active' : '' ?>" href="<?= e(study_url('/study')) ?>">Studium</a>
                <a class="<?= $active === 'drill' ? 'active' : '' ?>" href="<?= e(study_url('/drill')) ?>">Drill</a>
                <a class="<?= $active === 'search' ? 'active' : '' ?>" href="<?= e(study_url('/search')) ?>">Hledat</a>
            <?php endif; ?>
            <a class="<?= $active === 'about' ? 'active' : '' ?>" href="<?= e(study_url('/about')) ?>">O zdroji</a>
            <?php if (!$authenticated): ?>
                <a class="admin-link" href="<?= e(study_url('/login')) ?>">Přihlášení</a>
            <?php else: ?>
                <a class="<?= $active === 'account' ? 'active admin-link' : 'admin-link' ?>" href="<?= e(study_url($passwordPending ? '/account/password' : '/account')) ?>"><?= $passwordPending ? 'Nastavit heslo' : e((string) ($user['display_name'] ?? $user['username'])) ?></a>
                <?php if (is_admin() && !$passwordPending): ?><a href="<?= e(study_url('/admin')) ?>">Admin</a><?php endif; ?>
                <form method="post" action="<?= e(study_url('/logout')) ?>"><?= csrf_field() ?><button type="submit" class="nav-button">Odhlásit</button></form>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="shell page-shell">
<?php
}

/** Render the shared public footer and JavaScript entry point. */
function render_footer(): void
{
    $assetVersion = rawurlencode(APP_VERSION);
    ?>
</main>
<footer class="site-footer">
    <div class="shell footer-grid">
        <div>
            <strong><?= e(app_setting('site_name')) ?></strong>
            <p><?= e(app_setting('source_disclaimer')) ?></p>
        </div>
        <div class="footer-meta">Verze <?= e(APP_VERSION) ?> · Studijní progress se ukládá v tomto prohlížeči.</div>
    </div>
</footer>
<script src="<?= e(study_url('/public/assets/app.js?v=' . $assetVersion)) ?>" defer></script>
</body>
</html>
<?php
}

/** Render a compact page title block. */
function render_page_title(string $eyebrow, string $title, string $text = ''): void
{
    ?>
<section class="page-title">
    <span class="eyebrow"><?= e($eyebrow) ?></span>
    <h1><?= e($title) ?></h1>
    <?php if ($text !== ''): ?><p><?= e($text) ?></p><?php endif; ?>
</section>
<?php
}
