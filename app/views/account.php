<?php

/**
 * Project: PPL Study
 * File: app/views/account.php
 * Module Type: View Module
 *
 * Purpose:
 *   Renders shared login, learner profile, and permanent-password setup pages.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Account pages never expose administrator-only user management controls.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Views;

use const PplStudy\Core\APP_VERSION;
use function PplStudy\Core\csrf_field;
use function PplStudy\Core\e;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\study_url;
use function PplStudy\Services\app_setting;

/** Render the shared learner and administrator login page. */
function render_login(string $error = ''): void
{
    ?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Přihlášení | <?= e(app_setting('site_name')) ?></title><link rel="stylesheet" href="<?= e(study_url('/public/assets/app.css?v=' . APP_VERSION)) ?>"></head>
<body class="admin-login-body"><main class="admin-login-card">
    <a class="brand login-brand" href="<?= e(study_url('/')) ?>"><span class="brand-mark">✈</span><span><strong><?= e(app_setting('site_name')) ?></strong><small>Studijní účet</small></span></a>
    <h1>Přihlášení</h1>
    <p>Přístup ke studijním kartám, drillu a vyhledávání.</p>
    <?php if ($error !== ''): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack-form">
        <?= csrf_field() ?>
        <label>Uživatelské jméno<input name="username" autocomplete="username" required autofocus></label>
        <label>Heslo<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="button primary" type="submit">Přihlásit</button>
    </form>
    <p class="form-help account-login-help">Účty vytváří administrátor studijního webu.</p>
</main></body></html>
<?php
}

/** Render one authenticated learner profile. */
function render_account(array $user, string $notice = ''): void
{
    render_header('Můj účet', '', 'account');
    render_page_title('Uživatelský profil', 'Můj účet', 'Přehled přístupu ke studijnímu systému.');
    if ($notice !== '') {
        echo '<div class="flash">' . e($notice) . '</div>';
    }
    ?>
<div class="content-grid account-grid">
    <section class="panel">
        <h2>Profil</h2>
        <dl class="definition-list">
            <div><dt>Jméno</dt><dd><?= e((string) ($user['display_name'] ?? $user['username'])) ?></dd></div>
            <div><dt>Uživatelské jméno</dt><dd><?= e((string) $user['username']) ?></dd></div>
            <div><dt>Role</dt><dd><?= (string) $user['role'] === 'admin' ? 'Administrátor' : 'Uživatel' ?></dd></div>
            <div><dt>Poslední přihlášení</dt><dd><?= e((string) ($user['last_login_at'] ?? 'první přihlášení')) ?></dd></div>
        </dl>
        <div class="form-actions">
            <a class="button secondary" href="<?= e(study_url('/account/password')) ?>">Změnit heslo</a>
            <?php if ((string) $user['role'] === 'admin'): ?><a class="button secondary" href="<?= e(study_url('/admin')) ?>">Administrace</a><?php endif; ?>
        </div>
    </section>
    <section class="panel">
        <h2>Studium</h2>
        <p>Studijní karty, drill a vyhledávání jsou dostupné v rámci přihlášeného účtu.</p>
        <div class="form-actions">
            <a class="button primary" href="<?= e(study_url('/study')) ?>">Otevřít studium</a>
            <a class="button secondary" href="<?= e(study_url('/drill')) ?>">Spustit drill</a>
        </div>
    </section>
</div>
<?php
    render_footer();
}

/** Render mandatory first-login setup or a voluntary password change form. */
function render_password_setup(array $user, bool $forced, string $message = '', string $error = ''): void
{
    $minimum = study_limit('account_password_min_length');
    render_header($forced ? 'Nastavení hesla' : 'Změna hesla', '', 'account');
    render_page_title(
        'Zabezpečení účtu',
        $forced ? 'Vytvoř si stálé heslo' : 'Změna hesla',
        $forced
            ? 'První přihlášení proběhlo pomocí dočasného hesla. Teď si nastav vlastní stálé heslo pro další přístupy.'
            : 'Nastav nové stálé heslo pro svůj účet.'
    );
    if ($message !== '') {
        echo '<div class="flash">' . e($message) . '</div>';
    }
    if ($error !== '') {
        echo '<div class="flash error">' . e($error) . '</div>';
    }
    ?>
<section class="panel password-panel">
    <div class="account-identity"><strong><?= e((string) ($user['display_name'] ?? $user['username'])) ?></strong><span><?= e((string) $user['username']) ?></span></div>
    <form method="post" class="stack-form">
        <?= csrf_field() ?>
        <?php if (!$forced): ?><label>Aktuální heslo<input type="password" name="current_password" autocomplete="current-password" required></label><?php endif; ?>
        <label>Nové heslo<input type="password" name="new_password" minlength="<?= $minimum ?>" autocomplete="new-password" required><small>Minimálně <?= $minimum ?> znaků.</small></label>
        <label>Nové heslo znovu<input type="password" name="new_password_again" minlength="<?= $minimum ?>" autocomplete="new-password" required></label>
        <button class="button primary" type="submit"><?= $forced ? 'Uložit stálé heslo' : 'Změnit heslo' ?></button>
    </form>
</section>
<?php
    render_footer();
}
