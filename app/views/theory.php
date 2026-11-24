<?php

/**
 * Project: PPL Study
 * File: app/views/theory.php
 * Module Type: Theory presentation
 * Purpose: Render accessible, escaped chapter data, original diagrams,
 *          tables, worked examples, source references and recall checks.
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Views;

use const PplStudy\Core\APP_VERSION;
use function PplStudy\Core\e;
use function PplStudy\Core\study_url;
use function PplStudy\Services\theory_figures;
use function PplStudy\Services\theory_sources;

/** Build the canonical link for a registered course and optional chapter. */
function theory_url(string $slug, string $chapter = ''): string
{
    return study_url('/study/theory/' . rawurlencode($slug)
        . ($chapter === '' ? '' : '?chapter=' . rawurlencode($chapter)));
}

/** Render a chapter table with semantic header cells and mobile overflow. */
function render_theory_table(array $table): void
{
    ?>
    <div class="theory-table-wrap" tabindex="0" role="region" aria-label="Studijní tabulka">
        <table class="theory-table">
            <thead><tr><?php foreach ($table['headers'] as $header): ?><th scope="col"><?= e($header) ?></th><?php endforeach; ?></tr></thead>
            <tbody><?php foreach ($table['rows'] as $row): ?><tr><?php foreach ($row as $column => $cell): ?>
                <?php if ($column === 0): ?><th scope="row"><?= e($cell) ?></th><?php else: ?><td><?= e($cell) ?></td><?php endif; ?>
            <?php endforeach; ?></tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php
}

/** Render the original project-owned explanatory illustration. */
function render_theory_figure(string $key): void
{
    $figure = theory_figures()[$key] ?? null;
    if ($figure === null) {
        return;
    }
    ?>
    <figure class="theory-figure">
        <?php $figureUrl = study_url('/public/assets/theory/meteorology/' . $key . '.svg?v=' . APP_VERSION); ?>
        <a class="theory-figure-link" href="<?= e($figureUrl) ?>" target="_blank" rel="noopener" aria-label="<?= e('Otevřít schéma ve větší velikosti: ' . $figure[0]) ?>"><img src="<?= e($figureUrl) ?>" alt="<?= e($figure[1]) ?>" loading="lazy" width="800" height="360"></a>
        <figcaption><?= e($figure[0]) ?>. Autorské schematické znázornění, není v měřítku. <a href="<?= e($figureUrl) ?>" target="_blank" rel="noopener">Zvětšit schéma ↗</a></figcaption>
    </figure>
    <?php
}

/** Render prominent chapter memory aids, preserving unit pairs and caveats. */
function render_theory_memory(array $items): void
{
    if ($items === []) {
        return;
    }
    ?>
    <section class="theory-memory" id="pomucky" aria-labelledby="theory-memory-title">
        <div class="theory-memory-heading"><span class="eyebrow">Rychlé opakování</span><h2 id="theory-memory-title">Zapamatuj si</h2><p>Hodnoty a vztahy, které je dobré znát zpaměti. Znak ≈ označuje zaokrouhlení nebo odhad, nikoliv provozní limit.</p></div>
        <div class="theory-memory-grid">
            <?php foreach ($items as $item): ?>
                <div class="theory-memory-card"><h3><?= e($item['label']) ?></h3><strong><?= e($item['value']) ?></strong><p><?= e($item['note']) ?></p></div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/** Render a compact cross-chapter reference from the same editorial memory aids. */
function render_theory_quick_reference(string $slug, array $chapters): void
{
    $selected = [];
    foreach ($chapters as $chapterKey => $chapter) {
        foreach ($chapter['memory'] ?? [] as $item) {
            if (!empty($item['overview'])) {
                $selected[] = [$chapterKey, $item];
            }
        }
    }
    if ($selected === []) {
        return;
    }
    ?>
    <section class="panel theory-quick-reference" id="tahak" aria-labelledby="theory-quick-title">
        <span class="eyebrow">Opakování před zkouškou</span><h2 id="theory-quick-title">Meteorologické pomůcky a převody</h2>
        <p>Výšky a vertikální vzdálenosti uvádíme v metrech i stopách (ft), tlak přednostně v hPa. V českém provozu se dohlednost a RVR hlásí v metrech, METAR/TAF uvádí základny ve stovkách stop a FL zůstává v původní podobě. Podle AIP ČR GEN 2.1 se výška letadla za letu hlásí ve stopách a vertikální rychlost ve ft/min; metrické přístroje na některých letadlech nemění toto pravidlo. Doplňkové převody slouží k pochopení, ne ke změně hlášených hodnot. Při praktickém letu respektuj platná pravidla, jednotky přístrojů a AFM/POH.</p>
        <div class="theory-reference-grid">
            <?php foreach ($selected as [$chapterKey, $item]): ?>
                <a class="theory-reference-item" href="<?= e(theory_url($slug, $chapterKey)) ?>#pomucky"><span><?= e($item['label']) ?></span><strong><?= e($item['value']) ?></strong><small><?= e($item['note']) ?></small></a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/** Render a structured explanation section without accepting arbitrary HTML. */
function render_theory_section(array $section, int $index): void
{
    ?>
    <section class="theory-section" id="sekce-<?= $index ?>">
        <h2><?= e($section['heading']) ?></h2>
        <?php foreach ($section['paras'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?>
        <?php if (isset($section['formula'])): ?><div class="theory-formula"><span>Vztah / pomůcka</span><strong><?= e($section['formula']) ?></strong></div><?php endif; ?>
        <?php if (isset($section['code'])): ?><pre class="theory-weather-code"><code><?= e($section['code']) ?></code></pre><?php endif; ?>
        <?php if (isset($section['figure'])): render_theory_figure($section['figure']); endif; ?>
        <?php if (isset($section['table'])): render_theory_table($section['table']); endif; ?>
        <?php if (isset($section['example'])): ?><aside class="theory-callout theory-example"><span>Praktický příklad</span><h3><?= e($section['example']['title']) ?></h3><p><?= e($section['example']['text']) ?></p><?php if (!empty($section['example']['steps'])): ?><ol class="theory-example-steps"><?php foreach ($section['example']['steps'] as $step): ?><li><strong><?= e($step['label']) ?></strong><p><?= e($step['detail']) ?></p></li><?php endforeach; ?></ol><?php endif; ?></aside><?php endif; ?>
        <?php if (isset($section['note'])): ?><aside class="theory-callout theory-note"><span>Pozor / provozní souvislost</span><p><?= e($section['note']) ?></p></aside><?php endif; ?>
    </section>
    <?php
}

/** Render source notes, linking only to known editorial bibliography entries. */
function render_theory_sources(array $ids): void
{
    $sources = theory_sources();
    ?>
    <ul class="theory-sources">
        <?php foreach (array_unique($ids) as $id): if (!isset($sources[$id])) { continue; } $source = $sources[$id]; ?>
            <li><a href="<?= e($source[1]) ?>" target="_blank" rel="noopener noreferrer"><?= e($source[0]) ?></a><span><?= e($source[2]) ?></span></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/** Render chapter navigation and overview, retaining the existing study modes. */
function render_theory_course(array $course, string $chapterSlug = ''): void
{
    $slug = $course['slug'];
    $chapters = $course['chapters'];
    $keys = array_keys($chapters);
    $chapter = $chapters[$chapterSlug] ?? null;
    $subjectId = (int) $course['subject_id'];
    $sourceQuestions = [];
    $checkCount = 0;
    $figureCount = [];
    foreach ($chapters as $item) {
        $sourceQuestions = array_merge($sourceQuestions, $item['questions']);
        $checkCount += count($item['checks']);
        foreach ($item['sections'] as $section) {
            if (isset($section['figure'])) {
                $figureCount[$section['figure']] = true;
            }
        }
    }
    $sourceQuestions = array_unique($sourceQuestions);
    render_header($chapter ? $chapter['title'] : $course['title'], $course['description'], 'study');
    ?>
    <nav class="theory-breadcrumb" aria-label="Drobečková navigace">
        <a href="<?= e(study_url('/study')) ?>">Studium</a><span>/</span>
        <?php if ($chapter): ?><a href="<?= e(theory_url($slug)) ?>"><?= e($course['title']) ?></a><span>/</span><span aria-current="page"><?= e($chapter['title']) ?></span>
        <?php else: ?><span aria-current="page"><?= e($course['title']) ?></span><?php endif; ?>
    </nav>
    <?php if ($chapter === null): ?>
        <section class="theory-hero">
            <div><span class="eyebrow">PPL(A) · Teoretická učebnice · Předmět <?= $subjectId ?></span>
                <h1><?= e($course['title']) ?></h1><p><?= e($course['subtitle']) ?>.</p>
                <p><?= e($course['description']) ?></p>
                <div class="hero-actions"><a class="button primary" href="<?= e(theory_url($slug, $keys[0])) ?>">Začít od první kapitoly</a><a class="button secondary" href="<?= e(study_url('/drill?subject=' . $subjectId . '&mode=paper')) ?>">Zkouškový test</a><?php if (!empty($course['quick_reference'])): ?><a class="button secondary" href="#tahak">Tahák a převody</a><?php endif; ?></div>
            </div>
            <div class="theory-hero-stats">
                <div><strong><?= count($chapters) ?></strong><span>kapitol</span></div>
                <div><strong><?= count($sourceQuestions) ?>/<?= (int) $course['source_count'] ?></strong><span>témata zveřejněných otázek pokryta</span></div>
                <div><strong><?= count($figureCount) ?></strong><span>originálních schémat</span></div>
                <div><strong><?= $checkCount ?></strong><span>kontrolních otázek</span></div>
            </div>
        </section>
        <section class="theory-intro panel"><h2>Jak studovat</h2><p>Postupuj od základních fyzikálních pojmů přes vznik počasí až po praktický briefing. Po každé kapitole zkus odpovědět na kontrolní otázky bez nápovědy a procvič si příslušná témata v původním režimu otázek. Nezaměňuj orientační výpočty s provozními limity. Učebnice má vlastní text a schémata; odkazy na otázky pouze určují rozsah výuky.</p><p><strong>Podklad:</strong> <?= e($course['source_version']) ?>. ÚCL zveřejňuje jen část celkové zkouškové banky, proto nejde o garanci úplného pokrytí neveřejných otázek.</p></section>
        <h2 class="theory-heading">Obsah učebnice</h2>
        <div class="theory-chapter-grid">
            <?php foreach ($chapters as $chapterKey => $item): $position = array_search($chapterKey, $keys, true) + 1; ?>
                <article class="theory-chapter-card">
                    <span class="theory-chapter-index"><?= str_pad((string) $position, 2, '0', STR_PAD_LEFT) ?></span>
                    <div><h3><a href="<?= e(theory_url($slug, $chapterKey)) ?>"><?= e($item['title']) ?></a></h3>
                    <p><?= e($item['lead']) ?></p><small><?= count($item['sections']) ?> oddílů · <?= count($item['checks']) ?> kontrolních otázek</small></div>
                    <a class="button secondary small" href="<?= e(theory_url($slug, $chapterKey)) ?>">Otevřít kapitolu</a>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($course['quick_reference'])): render_theory_quick_reference($slug, $chapters); endif; ?>
        <?php if (!empty($course['glossary'])): ?>
            <section class="panel theory-glossary" id="slovnik"><span class="eyebrow">Terminologie</span><h2>Slovník pojmů a zkratek</h2><p>Používej jej při čtení kapitol; odkazy vedou na podrobný výklad příslušného pojmu.</p>
                <dl><?php foreach ($course['glossary'] as $entry): ?><div><dt><a href="<?= e(theory_url($slug, $entry['chapter'])) ?>"><?= e($entry['term']) ?></a></dt><dd><?= e($entry['definition']) ?></dd></div><?php endforeach; ?></dl>
            </section>
        <?php endif; ?>
        <section class="panel theory-bibliography"><h2>Zdroje a metodika</h2><p>Autorský výklad využívá níže uvedené odborné zdroje, osnovu EASA a rozsah témat v lokální bance. Studijní texty nejsou oficiální učebnicí ani aktuálním letovým briefingem. Pro let platí aktuální předpisy, AIP, NOTAM, AFM/POH a pokyny instruktora a ATS. Zdroje z USA a Spojeného království slouží k výkladu meteorologie, ne k přenosu jejich místních provozních pravidel do ČR.</p>
        <p><a href="<?= e($course['exam_url']) ?>" target="_blank" rel="noopener noreferrer">ÚCL – zveřejněné zkušební otázky PPL/LAPL</a> (včetně podmínek použití).</p>
        <?php render_theory_sources(array_merge(...array_column($chapters, 'sources'))); ?></section>
    <?php else: $position = array_search($chapterSlug, $keys, true); ?>
        <section class="theory-reading-heading"><span class="eyebrow">Kapitola <?= $position + 1 ?> / <?= count($chapters) ?></span><h1><?= e($chapter['title']) ?></h1><p><?= e($chapter['lead']) ?></p></section>
        <div class="theory-reading-layout">
            <aside class="theory-sidebar" aria-label="Obsah učebnice">
                <nav><a class="theory-back" href="<?= e(theory_url($slug)) ?>">← Přehled učebnice</a><ol><?php foreach ($chapters as $chapterKey => $item): ?><li><a <?= $chapterKey === $chapterSlug ? 'aria-current="page"' : '' ?> href="<?= e(theory_url($slug, $chapterKey)) ?>"><?= e($item['title']) ?></a></li><?php endforeach; ?></ol></nav>
            </aside>
            <article class="theory-article">
                <nav class="theory-section-nav" aria-label="Obsah kapitoly"><h2>V této kapitole</h2><ol><?php if (!empty($chapter['memory'])): ?><li><a href="#pomucky">Zapamatuj si</a></li><?php endif; ?><?php foreach ($chapter['sections'] as $i => $section): ?><li><a href="#sekce-<?= $i + 1 ?>"><?= e($section['heading']) ?></a></li><?php endforeach; ?><li><a href="#kontrola">Ověř si znalosti</a></li></ol></nav>
                <?php $prerequisites = $course['prerequisites'][$chapterSlug] ?? []; ?>
                <?php if ($prerequisites !== [] || !empty($course['glossary'])): ?><aside class="theory-prerequisites"><h2>Co je dobré znát předem</h2>
                    <?php if ($prerequisites !== []): ?><p>Výklad navazuje na tyto kapitoly:</p><ul><?php foreach ($prerequisites as $previousSlug): ?><li><a href="<?= e(theory_url($slug, $previousSlug)) ?>"><?= e($chapters[$previousSlug]['title']) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
                    <?php if (!empty($course['glossary'])): ?><a href="<?= e(theory_url($slug)) ?>#slovnik">Slovník pojmů a zkratek</a><?php endif; ?>
                </aside><?php endif; ?>
                <?php render_theory_memory($chapter['memory'] ?? []); ?>
                <?php foreach ($chapter['sections'] as $i => $section): render_theory_section($section, $i + 1); endforeach; ?>
                <section id="kontrola" class="theory-section theory-checks"><span class="eyebrow">Aktivní vybavování znalostí</span><h2>Ověř si znalosti</h2><p>Odpověď si nejdříve zkus vysvětlit vlastními slovy. Potom ji odkryj a porovnej.</p>
                    <?php foreach ($chapter['checks'] as $i => $check): ?><details><summary><?= $i + 1 ?>. <?= e($check['question']) ?></summary><p><?= e($check['answer']) ?></p></details><?php endforeach; ?>
                </section>
                <section class="theory-section"><h2>Vazba na zkouškovou banku</h2><p>Čísla otázek v předmětu Meteorologie (zdroj ÚCL, nikoli globální čísla karet): <?= e(implode(', ', $chapter['questions'])) ?>. Stejné téma se může záměrně objevit ve více kapitolách.</p>
                    <div class="hero-actions"><a class="button primary" href="<?= e(study_url('/study?subject=' . $subjectId . '&mode=quiz')) ?>">Procvičit meteorologii</a><a class="button secondary" href="<?= e(study_url('/study?subject=' . $subjectId . '&mode=learn')) ?>">Režim učení</a></div>
                </section>
                <section class="theory-section"><h2>Doporučené zdroje kapitoly</h2><?php render_theory_sources($chapter['sources']); ?></section>
                <nav class="theory-pagination" aria-label="Předchozí a další kapitola">
                    <?php if ($position > 0): ?><a class="button secondary" rel="prev" href="<?= e(theory_url($slug, $keys[$position - 1])) ?>">← Předchozí kapitola</a><?php else: ?><a class="button secondary" href="<?= e(theory_url($slug)) ?>">← Obsah učebnice</a><?php endif; ?>
                    <?php if ($position + 1 < count($keys)): ?><a class="button primary" rel="next" href="<?= e(theory_url($slug, $keys[$position + 1])) ?>">Další kapitola →</a><?php else: ?><a class="button primary" href="<?= e(study_url('/drill?subject=' . $subjectId . '&mode=paper')) ?>">Spustit zkouškový test</a><?php endif; ?>
                </nav>
            </article>
        </div>
    <?php endif; ?>
    <?php
    render_footer();
}
