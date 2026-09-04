<?php

/**
 * Project: PPL Study
 * File: app/views/admin.php
 * Module Type: View Module
 *
 * Purpose:
 *   Renders administrator dashboard, user management, question editor, source
 *   documents, importer, settings, and System Health pages.
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
use function PplStudy\Core\e;
use function PplStudy\Core\study_limit;
use function PplStudy\Core\study_url;
use function PplStudy\Services\app_setting;

/** Render the standalone administrator login page. */
function render_admin_login(string $error = ''): void
{
    ?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Admin login | <?= e(app_setting('site_name')) ?></title><link rel="stylesheet" href="<?= e(study_url('/public/assets/app.css?v=' . APP_VERSION)) ?>"></head>
<body class="admin-login-body"><main class="admin-login-card">
    <a class="brand login-brand" href="<?= e(study_url('/')) ?>"><span class="brand-mark">✈</span><span><strong><?= e(app_setting('site_name')) ?></strong><small>Administrace</small></span></a>
    <h1>Přihlášení</h1>
    <p>Správa otázek, importu a nastavení webu.</p>
    <?php if ($error !== ''): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack-form">
        <?= csrf_field() ?>
        <label>Uživatelské jméno<input name="username" autocomplete="username" required autofocus></label>
        <label>Heslo<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="button primary" type="submit">Přihlásit</button>
    </form>
</main></body></html>
<?php
}

/** Render administrator document header and navigation. */
function render_admin_header(string $title, string $active = ''): void
{
    ?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title><?= e($title) ?> | Admin | <?= e(app_setting('site_name')) ?></title><link rel="stylesheet" href="<?= e(study_url('/public/assets/app.css?v=' . APP_VERSION)) ?>"></head>
<body class="admin-body">
<header class="admin-header"><div class="shell admin-nav-shell"><a class="brand" href="<?= e(study_url('/admin')) ?>"><span class="brand-mark">✈</span><span><strong><?= e(app_setting('site_name')) ?></strong><small>Admin</small></span></a><nav class="admin-nav">
<a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= e(study_url('/admin')) ?>">Dashboard</a>
<a class="<?= $active === 'users' ? 'active' : '' ?>" href="<?= e(study_url('/admin/users')) ?>">Uživatelé</a>
<a class="<?= $active === 'questions' ? 'active' : '' ?>" href="<?= e(study_url('/admin/questions')) ?>">Otázky</a>
<a class="<?= $active === 'sources' ? 'active' : '' ?>" href="<?= e(study_url('/admin/sources')) ?>">Zdroje</a>
<a class="<?= $active === 'import' ? 'active' : '' ?>" href="<?= e(study_url('/admin/import')) ?>">Import</a>
<a class="<?= $active === 'settings' ? 'active' : '' ?>" href="<?= e(study_url('/admin/settings')) ?>">Nastavení</a>
<a class="<?= $active === 'diagnostics' ? 'active' : '' ?>" href="<?= e(study_url('/admin/diagnostics')) ?>">System Health</a>
<a href="<?= e(study_url('/')) ?>">Web</a>
<a href="<?= e(study_url('/account')) ?>">Můj účet</a>
<form method="post" action="<?= e(study_url('/admin/logout')) ?>"><?= csrf_field() ?><button type="submit" class="nav-button">Odhlásit</button></form>
</nav></div></header><main class="shell admin-main">
<?php
}

/** Render administrator page footer. */
function render_admin_footer(): void
{
    ?></main><script src="<?= e(study_url('/public/assets/app.js?v=' . APP_VERSION)) ?>" defer></script></body></html><?php
}

/** Render a success/error flash if supplied. */
function render_flash(string $message, string $type = 'success'): void
{
    if ($message !== '') {
        echo '<div class="flash ' . e($type) . '">' . e($message) . '</div>';
    }
}

/** Return a short Czech label for one curation state. */
function curation_status_label(string $status): string
{
    return match ($status) {
        'verified' => 'ověřeno',
        'explained' => 'vysvětlení hotovo',
        default => 'čeká na zpracování',
    };
}

/** Return a short Czech label for one wording-fidelity state. */
function wording_status_label(string $status): string
{
    return $status === 'source_exact' ? 'zdrojové znění' : 'studijní znění';
}

/** Render the admin dashboard. */
function render_admin_dashboard(array $summary, array $userSummary, array $subjects, array $logs, array $bankMeta): void
{
    render_admin_header('Dashboard', 'dashboard');
    ?>
<div class="admin-title"><div><span class="eyebrow">Administrace</span><h1>Dashboard</h1></div><span class="version-badge">v<?= e(APP_VERSION) ?></span></div>
<div class="metric-grid">
    <div class="metric"><span>Aktivní karty</span><strong><?= (int) $summary['active_questions'] ?></strong></div>
    <div class="metric"><span>Předměty</span><strong><?= (int) $summary['active_subjects'] ?></strong></div>
    <div class="metric"><span>Přílohové reference</span><strong><?= (int) $summary['attachments'] ?></strong></div>
    <div class="metric"><span>Vysvětlení hotovo</span><strong><?= (int) ($summary['explained_questions'] ?? 0) ?></strong></div>
    <div class="metric"><span>Zdrojově ověřeno</span><strong><?= (int) ($summary['verified_questions'] ?? 0) ?></strong></div>
    <div class="metric"><span>Zdrojové znění</span><strong><?= (int) ($summary['source_exact_questions'] ?? 0) ?></strong></div>
    <div class="metric"><span>Aktivní uživatelé</span><strong><?= (int) ($userSummary['active'] ?? 0) ?></strong></div>
</div>
<div class="admin-grid two">
    <section class="panel"><h2>Question bank</h2><dl class="definition-list">
        <div><dt>Název</dt><dd><?= e((string) ($bankMeta['bank_name'] ?? '')) ?></dd></div>
        <div><dt>Verze kompilátu</dt><dd><?= e((string) ($bankMeta['bank_compilation_version'] ?? '')) ?></dd></div>
        <div><dt>Datum kompilátu</dt><dd><?= e((string) ($bankMeta['bank_compiled_at'] ?? '')) ?></dd></div>
    </dl><p><a class="button secondary" href="<?= e(study_url('/admin/import')) ?>">Importovat bundled bank</a></p></section>
    <section class="panel"><h2>Předměty</h2><div class="mini-subject-list"><?php foreach ($subjects as $subject): ?><div><span><?= (int) $subject['id'] ?>. <?= e((string) $subject['name']) ?></span><strong><?= (int) $subject['expected_cards'] ?></strong></div><?php endforeach; ?></div></section>
</div>
<section class="panel"><h2>Poslední změny</h2><?php if ($logs === []): ?><p class="muted">Auditní log začne první administrátorskou změnou.</p><?php else: ?><div class="log-list"><?php foreach ($logs as $log): ?><div><strong><?= e((string) $log['event_type']) ?></strong><span><?= e((string) $log['message']) ?></span><small><?= e((string) $log['created_at']) ?><?= !empty($log['username']) ? ' · ' . e((string) $log['username']) : '' ?></small></div><?php endforeach; ?></div><?php endif; ?></section>
<?php
    render_admin_footer();
}

/** Render administrator user list. */
function render_admin_users(array $users, string $error = ''): void
{
    render_admin_header('Uživatelé', 'users');
    ?>
<div class="admin-title"><div><span class="eyebrow">Přístupy</span><h1>Uživatelé</h1></div><a class="button primary" href="<?= e(study_url('/admin/users/new')) ?>">Přidat uživatele</a></div>
<?php render_flash($error, 'error'); ?>
<section class="panel">
    <p class="muted">Účty vytváří administrátor. Nový účet používá při prvním přihlášení dočasné heslo a následně si uloží vlastní stálé heslo.</p>
    <?php if ($users === []): ?>
        <p>Po spuštění databázové migrace se zde zobrazí uživatelské profily.</p>
    <?php else: ?>
    <div class="admin-table-wrap"><table class="admin-table user-table"><thead><tr><th>Uživatel</th><th>Role</th><th>Stav</th><th>Heslo</th><th>Poslední přihlášení</th><th></th></tr></thead><tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><strong><?= e((string) ($user['display_name'] ?? $user['username'])) ?></strong><small class="table-subline"><?= e((string) $user['username']) ?></small></td>
            <td><?= (string) $user['role'] === 'admin' ? 'Administrátor' : 'Uživatel' ?></td>
            <td><span class="status-dot <?= !empty($user['active']) ? 'ok' : 'off' ?>"><?= !empty($user['active']) ? 'aktivní' : 'deaktivovaný' ?></span></td>
            <td><span class="status-dot <?= !empty($user['must_change_password']) ? '' : 'ok' ?>"><?= !empty($user['must_change_password']) ? (!empty($user['temporary_password_consumed_at']) ? 'první přihlášení proběhlo' : 'dočasné heslo připraveno') : 'stálé heslo' ?></span></td>
            <td><?= e((string) ($user['last_login_at'] ?? 'zatím bez přihlášení')) ?></td>
            <td><a class="text-link" href="<?= e(study_url('/admin/user/' . (int) $user['id'])) ?>">Spravovat</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</section>
<?php
    render_admin_footer();
}

/** Render new invitation-only user form. */
function render_admin_user_new(string $error = ''): void
{
    $minimum = study_limit('account_password_min_length');
    render_admin_header('Přidat uživatele', 'users');
    ?>
<div class="admin-title"><div><span class="eyebrow">Přístupy</span><h1>Přidat uživatele</h1></div><a class="button secondary" href="<?= e(study_url('/admin/users')) ?>">Zpět</a></div>
<?php render_flash($error, 'error'); ?>
<form method="post" class="panel settings-form user-edit-form">
    <?= csrf_field() ?>
    <div class="form-grid two">
        <label>Uživatelské jméno<input name="username" value="<?= e((string) ($_POST['username'] ?? '')) ?>" autocomplete="off" required></label>
        <label>Zobrazované jméno<input name="display_name" value="<?= e((string) ($_POST['display_name'] ?? '')) ?>"></label>
    </div>
    <label>Role<select name="role"><option value="user"<?= (string) ($_POST['role'] ?? 'user') === 'user' ? ' selected' : '' ?>>Uživatel</option><option value="admin"<?= (string) ($_POST['role'] ?? '') === 'admin' ? ' selected' : '' ?>>Administrátor</option></select></label>
    <div class="form-grid two">
        <label>Dočasné heslo<input type="password" name="temporary_password" minlength="<?= $minimum ?>" autocomplete="new-password" required><small>Minimálně <?= $minimum ?> znaků. Platí pro první přihlášení.</small></label>
        <label>Dočasné heslo znovu<input type="password" name="temporary_password_again" minlength="<?= $minimum ?>" autocomplete="new-password" required></label>
    </div>
    <button class="button primary" type="submit">Vytvořit uživatele</button>
</form>
<?php
    render_admin_footer();
}

/** Render one managed user profile and password-reset controls. */
function render_admin_user_edit(array $user, int $currentAdminId, string $message = '', string $error = ''): void
{
    $minimum = study_limit('account_password_min_length');
    $self = (int) $user['id'] === $currentAdminId;
    render_admin_header('Uživatel ' . (string) $user['username'], 'users');
    ?>
<div class="admin-title"><div><span class="eyebrow">Uživatel #<?= (int) $user['id'] ?></span><h1><?= e((string) ($user['display_name'] ?? $user['username'])) ?></h1></div><a class="button secondary" href="<?= e(study_url('/admin/users')) ?>">Zpět</a></div>
<?php render_flash($message); render_flash($error, 'error'); ?>
<div class="admin-grid two user-admin-grid">
<section class="panel">
    <h2>Profil a oprávnění</h2>
    <form method="post" class="settings-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="save">
        <label>Uživatelské jméno<input value="<?= e((string) $user['username']) ?>" disabled></label>
        <label>Zobrazované jméno<input name="display_name" value="<?= e((string) ($user['display_name'] ?? '')) ?>"></label>
        <label>Role<select name="role"<?= $self ? ' disabled' : '' ?>><option value="user"<?= (string) $user['role'] === 'user' ? ' selected' : '' ?>>Uživatel</option><option value="admin"<?= (string) $user['role'] === 'admin' ? ' selected' : '' ?>>Administrátor</option></select></label>
        <?php if ($self): ?><input type="hidden" name="role" value="admin"><?php endif; ?>
        <label class="checkbox-row"><input type="checkbox" name="active" value="1"<?= !empty($user['active']) ? ' checked' : '' ?><?= $self ? ' disabled' : '' ?>> Aktivní účet</label>
        <?php if ($self): ?><input type="hidden" name="active" value="1"><?php endif; ?>
        <button class="button primary" type="submit">Uložit profil</button>
    </form>
    <dl class="definition-list account-admin-meta">
        <div><dt>Vytvořen</dt><dd><?= e((string) $user['created_at']) ?></dd></div>
        <div><dt>Vytvořil</dt><dd><?= e((string) ($user['created_by_username'] ?? 'installer / recovery')) ?></dd></div>
        <div><dt>Poslední přihlášení</dt><dd><?= e((string) ($user['last_login_at'] ?? 'zatím bez přihlášení')) ?></dd></div>
        <div><dt>Heslo</dt><dd><?= !empty($user['must_change_password']) ? (!empty($user['temporary_password_consumed_at']) ? 'dočasné heslo bylo použito' : 'dočasné heslo je připravené') : 'stálé' ?></dd></div>
    </dl>
</section>
<section class="panel">
    <h2>Nové dočasné heslo</h2>
    <p>Reset vytvoří nový jednorázový přístup. Po příštím přihlášení si uživatel nastaví vlastní stálé heslo.</p>
    <form method="post" class="stack-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="reset_password">
        <label>Dočasné heslo<input type="password" name="temporary_password" minlength="<?= $minimum ?>" autocomplete="new-password" required></label>
        <label>Dočasné heslo znovu<input type="password" name="temporary_password_again" minlength="<?= $minimum ?>" autocomplete="new-password" required></label>
        <button class="button secondary" type="submit">Nastavit dočasné heslo</button>
    </form>
</section>
</div>
<?php
    render_admin_footer();
}

/** Render paginated administrator question list. */
function render_admin_questions(array $subjects, array $pageData, string $query, ?int $subjectId): void
{
    render_admin_header('Otázky', 'questions');
    ?>
<div class="admin-title"><div><span class="eyebrow">Question bank</span><h1>Otázky</h1></div><strong><?= (int) $pageData['total'] ?> karet</strong></div>
<form class="admin-filter" method="get" action="<?= e(study_url('/admin/questions')) ?>"><input type="search" name="q" value="<?= e($query) ?>" placeholder="Číslo, text nebo příloha"><select name="subject"><option value="">Všechny předměty</option><?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>"<?= $subjectId === (int) $subject['id'] ? ' selected' : '' ?>><?= e((string) $subject['name']) ?></option><?php endforeach; ?></select><button class="button secondary" type="submit">Filtrovat</button></form>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>#</th><th>Předmět</th><th>Otázka</th><th>Zpracování</th><th>Znění</th><th>Příloha</th><th></th></tr></thead><tbody>
<?php foreach ($pageData['rows'] as $row): ?>
<tr>
    <td><?= (int) $row['sequence_no'] ?></td>
    <td><?= e((string) $row['subject_name']) ?></td>
    <td><?= e(mb_strimwidth((string) $row['prompt'], 0, 130, '…', 'UTF-8')) ?></td>
    <td><span class="status-dot <?= (string) ($row['curation_status'] ?? '') === 'verified' ? 'ok' : ((string) ($row['curation_status'] ?? '') === 'explained' ? '' : 'off') ?>"><?= e(curation_status_label((string) ($row['curation_status'] ?? 'draft'))) ?></span></td>
    <td><span class="status-dot <?= (string) ($row['wording_status'] ?? '') === 'source_exact' ? 'ok' : 'off' ?>"><?= e(wording_status_label((string) ($row['wording_status'] ?? 'study_wording'))) ?></span></td>
    <td><?= e((string) ($row['attachment_code'] ?? '')) ?></td>
    <td><a class="text-link" href="<?= e(study_url('/admin/question/' . (int) $row['sequence_no'])) ?>">Upravit</a></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<nav class="pagination"><?php if ((int) $pageData['page'] > 1): ?><a href="<?= e(study_url('/admin/questions?q=' . rawurlencode($query) . '&subject=' . ($subjectId ?? '') . '&p=' . ((int) $pageData['page'] - 1))) ?>">← Předchozí</a><?php endif; ?><span>Strana <?= (int) $pageData['page'] ?> / <?= (int) $pageData['pages'] ?></span><?php if ((int) $pageData['page'] < (int) $pageData['pages']): ?><a href="<?= e(study_url('/admin/questions?q=' . rawurlencode($query) . '&subject=' . ($subjectId ?? '') . '&p=' . ((int) $pageData['page'] + 1))) ?>">Další →</a><?php endif; ?></nav>
<?php
    render_admin_footer();
}

/** Render one administrator question edit form. */
function render_admin_question_edit(array $question, array $subjects, array $sourceDocuments, string $message = '', string $error = ''): void
{
    $sectionBodies = [];
    foreach (is_array($question['explanation_sections'] ?? null) ? $question['explanation_sections'] : [] as $section) {
        if (is_array($section)) {
            $sectionBodies[(string) ($section['heading'] ?? '')] = (string) ($section['body'] ?? '');
        }
    }
    render_admin_header('Karta #' . (int) $question['sequence_no'], 'questions');
    ?>
<div class="admin-title"><div><span class="eyebrow">Karta #<?= (int) $question['sequence_no'] ?></span><h1><?= e(trim((string) ($question['title'] ?? '')) !== '' ? (string) $question['title'] : 'Upravit studijní kartu') ?></h1></div><a class="button secondary" href="<?= e(study_url('/question/' . (int) $question['sequence_no'])) ?>" target="_blank">Náhled karty</a></div>
<?php render_flash($message); render_flash($error, 'error'); ?>
<form method="post" class="question-edit-form panel"><?= csrf_field() ?>
<div class="form-grid two">
<label>Předmět<select name="subject_id"><?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>"<?= (int) $question['subject_id'] === (int) $subject['id'] ? ' selected' : '' ?>><?= e((string) $subject['name']) ?></option><?php endforeach; ?></select></label>
<label>Volitelný název<input name="title" value="<?= e((string) ($question['title'] ?? '')) ?>"></label>
</div>

<div class="curation-state-grid">
<label>Zpracování
<select name="curation_status">
<option value="draft"<?= (string) ($question['curation_status'] ?? 'draft') === 'draft' ? ' selected' : '' ?>>Čeká na zpracování</option>
<option value="explained"<?= (string) ($question['curation_status'] ?? '') === 'explained' ? ' selected' : '' ?>>Vysvětlení hotovo</option>
<option value="verified"<?= (string) ($question['curation_status'] ?? '') === 'verified' ? ' selected' : '' ?>>Zdrojově ověřeno</option>
</select>
</label>
<label>Znění
<select name="wording_status">
<option value="study_wording"<?= (string) ($question['wording_status'] ?? 'study_wording') === 'study_wording' ? ' selected' : '' ?>>Studijní znění</option>
<option value="source_exact"<?= (string) ($question['wording_status'] ?? '') === 'source_exact' ? ' selected' : '' ?>>Přesné zdrojové znění</option>
</select>
</label>
</div>

<label>Veřejná stopa ÚCL<input name="source_trace" value="<?= e((string) $question['source_trace']) ?>" required></label>
<div class="form-grid two">
<label>Zdrojový dokument
<select name="source_document_key"><option value="">Bez přiřazeného dokumentu</option><?php foreach ($sourceDocuments as $document): $key = (string) ($document['source_key'] ?? ''); ?><option value="<?= e($key) ?>"<?= (string) ($question['source_document_key'] ?? '') === $key ? ' selected' : '' ?>><?= e((string) ($document['title'] ?? $key)) ?><?= !empty($document['local_available']) ? ' · lokálně uložen' : '' ?></option><?php endforeach; ?></select>
</label>
<label>Strana ve zdrojovém PDF<input type="number" min="1" max="65535" name="source_document_page" value="<?= e((string) ($question['source_document_page'] ?? '')) ?>"></label>
</div>

<label>Otázka<textarea name="prompt" rows="5" required><?= e((string) $question['prompt']) ?></textarea></label>
<label>Správná odpověď<textarea name="answer" rows="4" required><?= e((string) $question['answer']) ?></textarea></label>
<?php $choices = is_array($question['choices'] ?? null) ? array_values($question['choices']) : []; while (count($choices) < 4) { $choices[] = ''; } ?>
<fieldset class="choice-edit-fieldset">
<legend>Možnosti A-D <?= !empty($question['choices_generated']) ? '<span class="status-dot">generované</span>' : '<span class="status-dot ok">upravené</span>' ?></legend>
<p class="form-help">Správná odpověď musí být přesně jednou z těchto čtyř možností. Uložením se sada označí jako ručně upravená.</p>
<?php foreach (array_slice($choices, 0, 4) as $choiceIndex => $choice): ?>
<label>Možnost <?= chr(65 + $choiceIndex) ?><textarea name="choice_<?= $choiceIndex + 1 ?>" rows="3" required><?= e((string) $choice) ?></textarea></label>
<?php endforeach; ?>
</fieldset>

<label>Základní vysvětlení<textarea name="explanation" rows="6" required><?= e((string) $question['explanation']) ?></textarea></label>
<fieldset class="explanation-section-editor">
<legend>Rozšířené vysvětlení</legend>
<label>Princip<textarea name="section_principle" rows="5"><?= e((string) ($sectionBodies['Princip'] ?? '')) ?></textarea></label>
<label>Jak si to zapamatovat<textarea name="section_memory" rows="4"><?= e((string) ($sectionBodies['Jak si to zapamatovat'] ?? '')) ?></textarea></label>
<label>Provozní souvislost<textarea name="section_practical" rows="5"><?= e((string) ($sectionBodies['Provozní souvislost'] ?? '')) ?></textarea></label>
<label>Doplňující vysvětlení<textarea name="section_extra" rows="4"><?= e((string) ($sectionBodies['Doplňující vysvětlení'] ?? '')) ?></textarea></label>
</fieldset>

<label>Pozor / studijní upozornění<textarea name="warning" rows="4"><?= e((string) ($question['warning'] ?? '')) ?></textarea></label>
<div class="form-grid two"><label>Kód přílohy<input name="attachment_code" value="<?= e((string) ($question['attachment_code'] ?? '')) ?>"></label><label>URL příloh<input name="attachment_url" value="<?= e((string) ($question['attachment_url'] ?? '')) ?>"></label></div>
<label class="checkbox-row"><input type="checkbox" name="active" value="1"<?= !empty($question['active']) ? ' checked' : '' ?>> Karta je aktivní pro studium</label>
<div class="form-actions"><a class="button secondary" href="<?= e(study_url('/admin/questions')) ?>">Zpět</a><button class="button primary" type="submit">Uložit změny</button></div>
</form>
<?php
    render_admin_footer();
}

/** Render source-document manifest and upload controls. */
function render_admin_sources(array $documents, string $message = '', string $error = ''): void
{
    render_admin_header('Zdroje', 'sources');
    ?>
<div class="admin-title"><div><span class="eyebrow">Zdrojové dokumenty</span><h1>Zdroje</h1></div></div>
<?php render_flash($message); render_flash($error, 'error'); ?>
<section class="panel">
<p class="muted">Manifest drží oficiální URL a očekávanou verzi dokumentu. Lokální PDF nahraje administrátor a systém ho uloží mimo veřejný document root. Přihlášeným uživatelům se potom otevírá přes chráněnou route.</p>
<div class="source-admin-list">
<?php foreach ($documents as $document): ?>
<article class="source-admin-row">
    <div class="source-admin-meta">
        <strong><?= e((string) ($document['title'] ?? $document['source_key'])) ?></strong>
        <small><?= e((string) ($document['version_label'] ?? '')) ?> · <?= e((string) ($document['source_key'] ?? '')) ?></small>
        <?php if (!empty($document['local_available'])): ?>
            <span class="status-dot ok">lokální PDF připraveno</span>
            <small><?= number_format(((int) ($document['file_size'] ?? 0)) / 1024 / 1024, 2, ',', ' ') ?> MB · SHA-256 <?= e(substr((string) ($document['sha256'] ?? ''), 0, 16)) ?>…</small>
        <?php else: ?>
            <span class="status-dot off">čeká na lokální PDF</span>
        <?php endif; ?>
    </div>
    <div class="source-admin-actions">
        <?php if (!empty($document['official_url'])): ?><a class="button small secondary" target="_blank" rel="noopener noreferrer" href="<?= e((string) $document['official_url']) ?>">Oficiální zdroj</a><?php endif; ?>
        <?php if (!empty($document['local_available'])): ?><a class="button small secondary" target="_blank" href="<?= e(study_url('/source/' . rawurlencode((string) $document['source_key']))) ?>">Lokální PDF</a><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="source-upload-form">
            <?= csrf_field() ?>
            <input type="hidden" name="source_key" value="<?= e((string) $document['source_key']) ?>">
            <input type="file" name="source_pdf" accept="application/pdf,.pdf" required>
            <button class="button small primary" type="submit"><?= !empty($document['local_available']) ? 'Nahradit PDF' : 'Nahrát PDF' ?></button>
        </form>
    </div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php
    render_admin_footer();
}

/** Render JSON question-bank import controls. */
function render_admin_import(?array $bundledSummary, string $message = '', string $error = ''): void
{
    render_admin_header('Import', 'import');
    ?>
<div class="admin-title"><div><span class="eyebrow">Question bank</span><h1>Import</h1></div></div>
<?php render_flash($message); render_flash($error, 'error'); ?>
<div class="admin-grid two">
<section class="panel"><h2>Bundled bank</h2><?php if ($bundledSummary): ?><dl class="definition-list"><div><dt>Předměty</dt><dd><?= (int) $bundledSummary['subjects'] ?></dd></div><div><dt>Karty</dt><dd><?= (int) $bundledSummary['questions'] ?></dd></div><div><dt>Reference na přílohy</dt><dd><?= (int) $bundledSummary['attachments'] ?></dd></div></dl><?php endif; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="bundled"><label class="checkbox-row"><input type="checkbox" name="prune" value="1" checked> Synchronizovat databázi přesně podle importu</label><button class="button primary" type="submit">Importovat bundled bank</button></form></section>
<section class="panel"><h2>Nahrát JSON</h2><p>Pro aktualizaci z nového PDF nejprve offline spusť <code>scripts/build_question_bank.py</code>, potom výsledný JSON nahraj sem.</p><form method="post" enctype="multipart/form-data" class="stack-form"><?= csrf_field() ?><input type="hidden" name="action" value="upload"><label>Question bank JSON<input type="file" name="bank_file" accept="application/json,.json" required></label><label class="checkbox-row"><input type="checkbox" name="prune" value="1" checked> Synchronizovat přesně s importem</label><button class="button primary" type="submit">Validovat a importovat</button></form></section>
</div>
<section class="notice-panel"><strong>Import je transakční</strong><p>Nejprve se validuje celý JSON včetně počtu karet v jednotlivých předmětech a kontinuity sequence_no. Teprve potom začne databázový upsert.</p></section>
<?php
    render_admin_footer();
}

/** Render editable site settings. */
function render_admin_settings(array $settings, string $message = ''): void
{
    render_admin_header('Nastavení', 'settings');
    ?>
<div class="admin-title"><div><span class="eyebrow">Web</span><h1>Nastavení</h1></div></div><?php render_flash($message); ?>
<form method="post" class="panel settings-form"><?= csrf_field() ?>
<label>Název webu<input name="site_name" value="<?= e((string) $settings['site_name']) ?>" required></label>
<label>Podtitulek<input name="site_subtitle" value="<?= e((string) $settings['site_subtitle']) ?>"></label>
<label>Úvodní text<textarea name="home_intro" rows="4"><?= e((string) $settings['home_intro']) ?></textarea></label>
<label>Zdrojový disclaimer<textarea name="source_disclaimer" rows="4"><?= e((string) $settings['source_disclaimer']) ?></textarea></label>
<label>Výchozí počet otázek vlastního drillu<input type="number" min="1" max="120" name="default_drill_count" value="<?= e((string) $settings['default_drill_count']) ?>"></label>
<button class="button primary" type="submit">Uložit nastavení</button></form>
<?php
    render_admin_footer();
}

/** Render read-only system diagnostics. */
function render_admin_diagnostics(array $checks, string $message = '', string $error = ''): void
{
    render_admin_header('System Health', 'diagnostics');
    $failed = count(array_filter($checks, static fn (array $check): bool => !$check['ok']));
    ?>
<div class="admin-title"><div><span class="eyebrow">Diagnostika</span><h1>System Health</h1></div><span class="status-dot <?= $failed === 0 ? 'ok' : 'bad' ?>"><?= $failed === 0 ? 'v pořádku' : $failed . ' problémů' ?></span></div>
<?php render_flash($message); render_flash($error, 'error'); ?>
<div class="health-list"><?php foreach ($checks as $check): ?><div class="health-row"><span class="health-icon <?= $check['ok'] ? 'ok' : 'bad' ?>"><?= $check['ok'] ? '✓' : '!' ?></span><div><strong><?= e((string) $check['name']) ?></strong><small><?= e((string) $check['detail']) ?></small></div></div><?php endforeach; ?></div>
<section class="panel"><h2>Migrace</h2><p>Po nahrání nové verze lze zde bezpečně spustit všechny čekající migrační soubory. Definice se nejprve načtou a validují.</p><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="migrate"><button class="button secondary" type="submit">Spustit čekající migrace</button></form></section>
<?php
    render_admin_footer();
}
