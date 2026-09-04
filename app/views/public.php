<?php

/**
 * Project: PPL Study
 * File: app/views/public.php
 * Module Type: View Module
 *
 * Purpose:
 *   Renders public dashboard, study-card, drill, search, and source-information
 *   pages using data prepared by services and controllers.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 *
 * Notes:
 *   - Public study uses four editable answer choices with automatic scoring.
 *   - The source-derived answer remains the scoring authority.
 *   - Keep comments and docstrings intact when modifying this file.
 */

declare(strict_types=1);

namespace PplStudy\Views;

use function PplStudy\Core\e;
use function PplStudy\Core\is_authenticated;
use function PplStudy\Core\password_change_required;
use function PplStudy\Core\study_url;
use function PplStudy\Services\app_setting;
use function PplStudy\Services\source_document_by_key;

/** Render the public subject dashboard. */
function render_home(array $subjects, array $counts, array $summary): void
{
    $authenticated = is_authenticated();
    $readyForStudy = $authenticated && !password_change_required();
    render_header(app_setting('site_name'), '', 'home');
    ?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">PPL(A) teorie 2026</span>
        <h1>Dynamické studium PPL(A) teorie.</h1>
        <p><?= e(app_setting('home_intro')) ?></p>
        <div class="hero-actions">
            <?php if ($readyForStudy): ?>
                <a class="button primary" href="<?= e(study_url('/study')) ?>">Pokračovat ve studiu</a>
                <a class="button secondary" href="<?= e(study_url('/drill')) ?>">Spustit drill</a>
            <?php elseif ($authenticated): ?>
                <a class="button primary" href="<?= e(study_url('/account/password')) ?>">Nastavit stálé heslo</a>
            <?php else: ?>
                <a class="button primary" href="<?= e(study_url('/login')) ?>">Přihlásit ke studiu</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-stats" aria-label="Souhrn databáze">
        <div><strong><?= (int) $summary['active_questions'] ?></strong><span>studijních karet</span></div>
        <div><strong><?= (int) $summary['active_subjects'] ?></strong><span>předmětů</span></div>
        <div><strong><?= (int) $summary['attachments'] ?></strong><span>karet s přílohou</span></div>
        <div><strong>75 %</strong><span>hranice paperu</span></div>
    </div>
</section>

<section class="section-heading">
    <div><span class="eyebrow">Předměty</span><h2>Přehled studijních oblastí</h2></div>
    <p>Každý předmět má vlastní banku karet, počet zkouškových otázek a čas paperu podle kompilátu.</p>
</section>
<div class="subject-grid">
<?php foreach ($subjects as $subject):
    $id = (int) $subject['id'];
    $count = (int) ($counts[$id] ?? 0);
    ?>
    <article class="subject-card" data-subject-card data-subject-id="<?= $id ?>" data-question-count="<?= $count ?>">
        <div class="subject-number"><?= $id ?></div>
        <div class="subject-body">
            <h3><?= e((string) $subject['name']) ?></h3>
            <p><?= e((string) ($subject['description'] ?? '')) ?></p>
            <div class="subject-meta">
                <span><?= $count ?> karet</span>
                <span><?= (int) $subject['exam_question_count'] ?> otázek</span>
                <span><?= (int) $subject['exam_time_minutes'] ?> min</span>
            </div>
            <?php if ($readyForStudy): ?>
                <div class="subject-progress" aria-live="polite">
                    <div class="progress-track"><span data-progress-bar></span></div>
                    <small data-progress-label>Progress začne první vyhodnocenou kartou.</small>
                </div>
                <div class="card-actions">
                    <a class="button small primary" href="<?= e(study_url('/study?subject=' . $id)) ?>">Náhodná karta</a>
                    <a class="button small secondary" href="<?= e(study_url('/drill?subject=' . $id . '&mode=paper')) ?>">Paper drill</a>
                </div>
            <?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>
</div>

<section class="notice-panel">
    <strong>Studijní přístup</strong>
    <p><?= $readyForStudy
        ? 'Přihlášený účet zpřístupňuje jednotlivé karty, čtyři možnosti odpovědi, drill a vyhledávání.'
        : 'Dashboard ukazuje rozsah studijní banky. Jednotlivé karty, odpovědi, drill a vyhledávání se otevřou po přihlášení.' ?></p>
</section>
<?php
    render_footer();
}

/** Render the study setup page. */
function render_study_setup(array $subjects): void
{
    render_header('Studium', '', 'study');
    render_page_title('Studijní režimy', 'Otázky a učení', 'Vyber způsob práce a předmět. Režim otázek vyhodnocuje zvolenou možnost. Režim učení odkryje správnou odpověď až na požádání a nechá vlastní sebehodnocení.');
    ?>
<div class="mode-grid">
    <article class="panel mode-card">
        <span class="eyebrow">Režim otázek</span>
        <h2>Automatické vyhodnocení</h2>
        <p>Vybereš jednu ze čtyř možností. Po odpovědi se zobrazí správný výsledek a vysvětlení.</p>
        <a class="button primary" href="<?= e(study_url('/study?subject=all&mode=quiz')) ?>">Náhodná otázka</a>
    </article>
    <article class="panel mode-card">
        <span class="eyebrow">Režim učení</span>
        <h2>Vlastní kontrola</h2>
        <p>Nejdřív si odpověď promyslíš, potom odkryješ řešení a označíš, jestli jsi látku znal.</p>
        <a class="button primary" href="<?= e(study_url('/study?subject=all&mode=learn')) ?>">Náhodná karta k učení</a>
    </article>
</div>
<section class="panel study-subject-picker">
    <h2>Konkrétní předmět</h2>
    <div class="study-subject-list">
        <?php foreach ($subjects as $subject): ?>
        <div>
            <span><strong><?= (int) $subject['id'] ?>.</strong> <?= e((string) $subject['name']) ?></span>
            <span class="study-subject-actions">
                <a class="button small secondary" href="<?= e(study_url('/study?subject=' . (int) $subject['id'] . '&mode=quiz')) ?>">Otázka</a>
                <a class="button small secondary" href="<?= e(study_url('/study?subject=' . (int) $subject['id'] . '&mode=learn')) ?>">Učení</a>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php
    render_footer();
}

/** Render one study card in quiz or learning mode. */
function render_question_card(array $question, ?int $previous, ?int $next, string $mode = 'quiz'): void
{
    $sequence = (int) $question['sequence_no'];
    $subjectId = (int) $question['subject_id'];
    $mode = $mode === 'learn' ? 'learn' : 'quiz';
    $displayTitle = trim((string) ($question['title'] ?? '')) !== '' ? (string) $question['title'] : 'Karta ' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    $choices = is_array($question['choices'] ?? null) ? array_values($question['choices']) : [];
    if (count($choices) === 4 && (string) ($question['wording_status'] ?? '') !== 'source_exact') {
        shuffle($choices);
    }
    $sections = is_array($question['explanation_sections'] ?? null) ? $question['explanation_sections'] : [];
    $sourceDocumentKey = trim((string) ($question['source_document_key'] ?? ''));
    $sourceDocument = $sourceDocumentKey !== '' ? source_document_by_key($sourceDocumentKey) : null;
    $querySuffix = '?mode=' . $mode;
    render_header($displayTitle, (string) $question['prompt'], 'study');
    ?>
<section class="question-page" data-question-page data-sequence="<?= $sequence ?>" data-subject-id="<?= $subjectId ?>" data-study-mode="<?= e($mode) ?>">
    <div class="question-toolbar">
        <div>
            <span class="eyebrow"><?= e((string) $question['subject_name']) ?></span>
            <h1><?= e($displayTitle) ?></h1>
            <div class="question-mode-badges">
                <span class="status-dot <?= $mode === 'learn' ? '' : 'ok' ?>"><?= $mode === 'learn' ? 'režim učení' : 'režim otázek' ?></span>
                <?php if ((string) ($question['curation_status'] ?? '') === 'verified'): ?><span class="status-dot ok">zdrojově ověřeno</span><?php elseif ((string) ($question['curation_status'] ?? '') === 'explained'): ?><span class="status-dot">vysvětlení hotovo</span><?php endif; ?>
            </div>
        </div>
        <div class="question-index">#<?= str_pad((string) $sequence, 3, '0', STR_PAD_LEFT) ?></div>
    </div>

    <article class="study-card">
        <div class="study-label"><?= $mode === 'learn' ? 'Studijní otázka' : 'Otázka' ?></div>
        <p class="question-prompt"><?= nl2br(e((string) $question['prompt'])) ?></p>
        <?php if (!empty($question['attachment_code']) && !empty($question['attachment_url'])): ?>
            <?php $attachmentUrl = (string) $question['attachment_url']; $attachmentIsImage = (bool) preg_match('/\.(?:svg|png|jpe?g|webp)(?:\?.*)?$/i', $attachmentUrl); ?>
            <div class="attachment-prompt"><strong>Příloha <?= e((string) $question['attachment_code']) ?></strong><a class="text-link" target="_blank" rel="noopener noreferrer" href="<?= e($attachmentUrl) ?>">Otevřít podklad k otázce</a></div>
            <?php if ($attachmentIsImage): ?>
                <figure class="question-attachment"><img src="<?= e($attachmentUrl) ?>" alt="Příloha <?= e((string) $question['attachment_code']) ?> k otázce" loading="lazy"></figure>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (count($choices) === 4): ?>
            <div class="choice-list" data-choice-list>
                <?php foreach ($choices as $choiceIndex => $choice): ?>
                    <button class="choice-button<?= $mode === 'learn' ? ' learning-choice' : '' ?>" type="button" data-choice data-correct="<?= trim((string) $choice) === trim((string) $question['answer']) ? '1' : '0' ?>"<?= $mode === 'learn' ? ' tabindex="-1" aria-disabled="true"' : '' ?>>
                        <span class="choice-letter"><?= chr(65 + $choiceIndex) ?></span>
                        <span class="choice-text"><?= e((string) $choice) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="choice-feedback" data-choice-feedback hidden></div>
            <?php if ($mode === 'learn'): ?>
                <div class="learning-reveal-actions"><button class="button primary" type="button" data-reveal-answer>Zobrazit správnou odpověď</button></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="notice-panel compact"><strong>Možnosti odpovědi se připravují</strong><p>Tato karta zatím nemá kompletní čtyřčlennou sadu odpovědí.</p></div>
        <?php endif; ?>

        <div class="answer-panel" data-answer-panel hidden>
            <div class="study-label success">Správná odpověď</div>
            <p class="correct-answer"><?= nl2br(e((string) $question['answer'])) ?></p>

            <?php if ($sections !== []): ?>
                <div class="explanation-sections">
                    <?php foreach ($sections as $section): if (!is_array($section)) { continue; } $heading = trim((string) ($section['heading'] ?? '')); $body = trim((string) ($section['body'] ?? '')); if ($heading === '' || $body === '') { continue; } ?>
                    <section class="explanation-block">
                        <h2><?= e($heading) ?></h2>
                        <p><?= nl2br(e($body)) ?></p>
                    </section>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="explanation-block">
                    <h2>Vysvětlení</h2>
                    <p><?= nl2br(e((string) $question['explanation'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (trim((string) ($question['warning'] ?? '')) !== ''): ?>
                <div class="warning-block"><strong>Studijní poznámka</strong><p><?= nl2br(e((string) $question['warning'])) ?></p></div>
            <?php endif; ?>

            <?php if ($mode === 'learn'): ?>
                <div class="self-score learning-self-score">
                    <span>Vlastní kontrola:</span>
                    <button class="button success" type="button" data-self-score="correct">Věděl jsem</button>
                    <button class="button secondary" type="button" data-self-score="wrong">Chci zopakovat</button>
                    <button class="button ghost" type="button" data-star>☆ Označit</button>
                </div>
            <?php else: ?>
                <div class="self-score"><button class="button ghost" type="button" data-star>☆ Označit</button></div>
            <?php endif; ?>
        </div>
    </article>

    <aside class="source-card">
        <div><span>Veřejná stopa ÚCL</span><strong><?= e((string) $question['source_trace']) ?></strong></div>
        <div><span>Zpracování</span><strong><?= (string) ($question['curation_status'] ?? '') === 'verified' ? 'Zdrojově ověřeno' : ((string) ($question['curation_status'] ?? '') === 'explained' ? 'Vysvětlení hotovo' : 'Připraveno ke zpracování') ?></strong></div>
        <div><span>Znění</span><strong><?= (string) ($question['wording_status'] ?? '') === 'source_exact' ? 'Přesné zdrojové znění' : 'Studijní znění' ?></strong></div>
        <?php if (!empty($question['source_document_page'])): ?><div><span>Strana zdroje</span><strong><?= (int) $question['source_document_page'] ?></strong></div><?php endif; ?>
        <?php if ($sourceDocument && !empty($sourceDocument['local_available'])): ?><a class="text-link" target="_blank" href="<?= e(study_url('/source/' . rawurlencode($sourceDocumentKey))) ?>">Lokální zdrojové PDF</a><?php endif; ?>
        <?php if (!empty($question['subject_official_pdf_url'])): ?><a class="text-link" target="_blank" rel="noopener noreferrer" href="<?= e((string) $question['subject_official_pdf_url']) ?>">Oficiální PDF ÚCL</a><?php endif; ?>
        <?php if (!empty($question['attachment_code'])): ?>
            <div><span>Příloha</span><strong><?= e((string) $question['attachment_code']) ?></strong></div>
        <?php endif; ?>
    </aside>

    <nav class="question-nav">
        <div><?php if ($previous !== null): ?><a class="button secondary" href="<?= e(study_url('/question/' . $previous . $querySuffix)) ?>">← Předchozí</a><?php endif; ?></div>
        <a class="button primary" href="<?= e(study_url('/study?subject=' . $subjectId . '&mode=' . $mode)) ?>">Jiná náhodná</a>
        <div><?php if ($next !== null): ?><a class="button secondary" href="<?= e(study_url('/question/' . $next . $querySuffix)) ?>">Další →</a><?php endif; ?></div>
    </nav>
</section>
<?php
    render_footer();
}

/** Render the browser-driven drill shell. */
function render_drill(array $subjects, ?int $selectedSubject, string $mode, int $defaultCount): void
{
    render_header('Drill', '', 'drill');
    render_page_title('Procvičování', 'Drill a paper režim', 'Vyber odpověď z nabídky. Systém ji vyhodnotí, uloží průběžný progress a v paper režimu dodrží počet otázek i čas předmětu.');
    ?>
<section class="drill-shell" data-drill-root data-api-url="<?= e(study_url('/api/quiz')) ?>">
    <form class="drill-config" data-drill-form>
        <label>Předmět
            <select name="subject" data-drill-subject>
                <option value="all"<?= $selectedSubject === null ? ' selected' : '' ?>>Všechny předměty</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= (int) $subject['id'] ?>"<?= $selectedSubject === (int) $subject['id'] ? ' selected' : '' ?> data-paper-count="<?= (int) $subject['exam_question_count'] ?>" data-paper-time="<?= (int) $subject['exam_time_minutes'] ?>"><?= (int) $subject['id'] ?>. <?= e((string) $subject['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Režim
            <select name="mode" data-drill-mode>
                <option value="custom"<?= $mode === 'custom' ? ' selected' : '' ?>>Vlastní náhodný drill</option>
                <option value="paper"<?= $mode === 'paper' ? ' selected' : '' ?>>Paper drill</option>
            </select>
        </label>
        <label data-count-field>Počet otázek
            <input type="number" name="count" min="1" max="120" value="<?= $defaultCount ?>">
        </label>
        <button class="button primary" type="submit">Spustit</button>
        <p class="form-help" data-paper-hint>Paper drill je dostupný pro jeden konkrétní předmět.</p>
    </form>

    <div class="drill-stage" data-drill-stage hidden>
        <div class="drill-status">
            <span data-drill-position>1 / 1</span>
            <strong data-drill-title>Drill</strong>
            <span class="timer" data-drill-timer hidden>00:00</span>
        </div>
        <div class="progress-track large"><span data-drill-progress></span></div>
        <article class="study-card drill-card" data-drill-card></article>
    </div>

    <div class="drill-result" data-drill-result hidden></div>
</section>
<section class="notice-panel compact">
    <strong>Rozsah drillu</strong>
    <p>Drill pracuje s aktuální bankou 887 otázek. Zdrojově ověřené otázky zachovávají přesné čtyři možnosti i jejich pořadí; ostatní předměty se postupně převádějí stejným způsobem.</p>
</section>
<?php
    render_footer();
}

/** Render public search form and results. */
function render_search(array $subjects, string $query, ?int $subjectId, array $results): void
{
    render_header('Hledat', '', 'search');
    render_page_title('Vyhledávání', 'Najdi kartu podle pojmu, čísla nebo přílohy', 'Hledá se v otázce, odpovědi, vysvětlení, varování, stopě ÚCL i kódu přílohy.');
    ?>
<form class="search-form" method="get" action="<?= e(study_url('/search')) ?>">
    <input type="search" name="q" value="<?= e($query) ?>" maxlength="160" placeholder="např. QNH, wake turbulence, 7600, PFP-013">
    <select name="subject">
        <option value="">Všechny předměty</option>
        <?php foreach ($subjects as $subject): ?><option value="<?= (int) $subject['id'] ?>"<?= $subjectId === (int) $subject['id'] ? ' selected' : '' ?>><?= e((string) $subject['name']) ?></option><?php endforeach; ?>
    </select>
    <button class="button primary" type="submit">Hledat</button>
</form>
<?php if ($query !== '' || $subjectId !== null): ?>
    <div class="search-summary">Nalezeno <?= count($results) ?> výsledků v aktuálním limitu.</div>
    <div class="search-results">
        <?php foreach ($results as $row): ?>
            <a class="search-result" href="<?= e(study_url('/question/' . (int) $row['sequence_no'])) ?>">
                <span class="result-number">#<?= str_pad((string) $row['sequence_no'], 3, '0', STR_PAD_LEFT) ?></span>
                <div><strong><?= e(trim((string) ($row['title'] ?? '')) !== '' ? (string) $row['title'] : (string) $row['prompt']) ?></strong><small><?= e((string) $row['subject_name']) ?> · <?= e((string) $row['source_trace']) ?></small></div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php
    render_footer();
}

/** Render source provenance, exam structure, and methodology information. */
function render_about(array $subjects): void
{
    render_header('O zdroji', '', 'about');
    render_page_title('Zdroj a metodika', 'Obsah a struktura databáze', 'Web kombinuje přesné zdrojové otázky s doplněnými vysvětleními pro studium, vyhledávání a procvičování.');
    ?>
<div class="content-grid">
    <article class="prose panel">
        <h2>Rozsah banky</h2>
        <p>Bundled databáze aktuálně obsahuje 887 otázek v 9 předmětech. První dva předměty obsahují všech 97 a 58 jednotlivých otázek z dodaných oficiálních PDF ÚCL. Zbývající předměty zatím vycházejí z kompilátu a budou stejným zdrojovým postupem postupně rozděleny a ověřeny.</p>
        <p>Každá otázka obsahuje čtyři možnosti, správnou odpověď a vysvětlení. U zdrojově ověřených otázek je wording i pořadí možností převzato přesně z uloženého zdrojového PDF.</p>
        <h2>Aktuálnost</h2>
        <p><?= e(app_setting('source_disclaimer')) ?></p>
        <p><a class="text-link" href="https://www.caa.gov.cz/zkusebni-otazky-pro-zkousky-teoretickych-znalosti-ppl-lapl/" target="_blank" rel="noopener noreferrer">Aktuální stránka ÚCL se zkušebními otázkami PPL/LAPL</a></p>
    </article>
    <article class="panel">
        <h2>Struktura paperů</h2>
        <div class="exam-table-wrap">
            <table class="exam-table">
                <thead><tr><th>Předmět</th><th>Otázek</th><th>Čas</th><th>Karet</th></tr></thead>
                <tbody>
                <?php $totalQ = $totalTime = $totalCards = 0; foreach ($subjects as $subject):
                    $totalQ += (int) $subject['exam_question_count'];
                    $totalTime += (int) $subject['exam_time_minutes'];
                    $totalCards += (int) $subject['expected_cards'];
                    ?>
                    <tr><td><?= e((string) $subject['name']) ?></td><td><?= (int) $subject['exam_question_count'] ?></td><td><?= (int) $subject['exam_time_minutes'] ?> min</td><td><?= (int) $subject['expected_cards'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th>Celkem</th><th><?= $totalQ ?></th><th><?= $totalTime ?> min</th><th><?= $totalCards ?></th></tr></tfoot>
            </table>
        </div>
        <p class="small-note">Hranice úspěchu uvedená v kompilátu je nejméně 75 % v každém examination paper.</p>
    </article>
</div>
<?php
    render_footer();
}

/** Render a plain public 404 page. */
function render_not_found(): void
{
    http_response_code(404);
    render_header('Přehled studia');
    render_page_title('404', 'Pokračuj přes přehled studia', 'Hlavní navigace tě dovede ke kartám, drillu a vyhledávání.');
    echo '<p><a class="button primary" href="' . e(study_url('/')) . '">Zpět na přehled</a></p>';
    render_footer();
}
