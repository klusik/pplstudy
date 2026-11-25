<?php

/**
 * Project: PPL Study
 * File: scripts/test_theory.php
 * Module Type: CLI regression test
 * Purpose: Validate course coverage, content structure and packaged assets.
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */
declare(strict_types=1);

use function PplStudy\Services\theory_course;
use function PplStudy\Services\theory_figures;
use function PplStudy\Services\theory_slug_for_subject;
use function PplStudy\Services\theory_sources;
use function PplStudy\Services\theory_labs;
use function PplStudy\Services\theory_labs_for_section;

if (PHP_SAPI !== 'cli' || $argc !== 2) {
    exit(1);
}
require dirname(__DIR__) . '/app/services/theory.php';
require dirname(__DIR__) . '/app/services/theory_labs.php';

/** Assert a course invariant independently of the PHP assertion configuration. */
function check_theory(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$zip = new ZipArchive();
check_theory($zip->open($argv[1]) === true, 'Missing distribution package');
$course = theory_course('meteorologie');
check_theory(is_array($course), 'Missing meteorology course');
check_theory(count($course['chapters']) === 14, 'Unexpected chapter count');
check_theory(theory_course('../meteorology') === null, 'Unregistered course accepted');
foreach (range(1, 9) as $id) {
    check_theory(theory_slug_for_subject($id) === ($id === 3 ? 'meteorologie' : null), 'Unexpected subject mapping');
}
$source = json_decode($zip->getFromName('data/source-exact/03-meteorologie.json'), true, 32, JSON_THROW_ON_ERROR);
$expected = array_map(static fn (array $item): int => (int) $item['source_question_id'], $source['questions']);
sort($expected);
check_theory($expected === range(1, 129), 'Unexpected source-bank IDs');
$sources = theory_sources();
$figures = theory_figures();
$usedSources = [];
$usedFigures = [];
$covered = [];
$checks = 0;
$sections = 0;
$words = 0;
$memoryCount = 0;
$overviewCount = 0;
$stepCount = 0;
$labIds = [];
$labRegistry = theory_labs();
$labSource = '';
foreach (['lab-engine.js', 'lab-physics.js', 'lab-weather.js', 'labs.css'] as $asset) {
    $bytes = $zip->getFromName('public/assets/theory/meteorology/' . $asset);
    check_theory(is_string($bytes) && strlen($bytes) > 100, 'Missing laboratory asset: ' . $asset);
    if (str_ends_with($asset, '.js')) { $labSource .= $bytes; }
}
check_theory($zip->locateName('app/services/theory_labs.php') !== false, 'Missing laboratory registry in ZIP');
$chapterOrder = array_keys($course['chapters']);
check_theory(count($course['glossary']) === 25, 'Glossary incomplete');
foreach ($course['glossary'] as $entry) {
    check_theory(trim($entry['term']) !== '' && trim($entry['definition']) !== '' && isset($course['chapters'][$entry['chapter']]), 'Invalid glossary entry');
}
foreach ($course['chapters'] as $slug => $chapter) {
    check_theory($zip->locateName('app/theory/meteorology/' . $slug . '.php') !== false, 'Missing chapter in ZIP: ' . $slug);
    check_theory(trim($chapter['title']) !== '' && trim($chapter['lead']) !== '', 'Missing chapter heading');
    check_theory(count($chapter['sections']) >= 3 && count($chapter['checks']) >= 3, 'Incomplete chapter: ' . $slug);
    check_theory(count($chapter['memory'] ?? []) >= 3, 'Missing chapter memory aids: ' . $slug);
    foreach ($chapter['memory'] as $aid) {
        check_theory(trim($aid['label']) !== '' && trim($aid['value']) !== '' && trim($aid['note']) !== '' && is_bool($aid['overview']), 'Incomplete memory aid');
        $memoryCount++;
        $overviewCount += (int) $aid['overview'];
    }
    foreach ($course['prerequisites'][$slug] as $prerequisite) {
        check_theory(isset($course['chapters'][$prerequisite]) && array_search($prerequisite, $chapterOrder, true) < array_search($slug, $chapterOrder, true), 'Invalid prerequisite order');
    }
    foreach ($chapter['questions'] as $id) {
        check_theory(is_int($id) && in_array($id, $expected, true), 'Invalid source question ID');
        $covered[$id] = true;
    }
    foreach ($chapter['sources'] as $id) {
        check_theory(isset($sources[$id]) && str_starts_with($sources[$id][1], 'https://'), 'Unknown source: ' . $id);
        $usedSources[$id] = true;
    }
    $chapterLabCount = 0;
    $chapterFigures = [];
    foreach ($chapter['sections'] as $section) {
        $figure = $section['figure'] ?? '';
        $chapterFigures[$figure] = true;
        foreach (theory_labs_for_section($slug, $figure) as $lab) {
            check_theory(is_string($lab) && preg_match('/^[a-z][a-z0-9-]+$/D', $lab) === 1 && !isset($labIds[$lab]), 'Invalid or duplicate laboratory ID');
            check_theory(str_contains($labSource, "add('" . $lab . "',"), 'Unregistered JavaScript laboratory: ' . $lab);
            $labIds[$lab] = true;
            $chapterLabCount++;
        }
        check_theory(trim($section['heading']) !== '' && count($section['paras']) > 0, 'Empty section');
        foreach ($section['paras'] as $paragraph) {
            check_theory(is_string($paragraph) && trim($paragraph) !== '', 'Empty paragraph');
            $words += count(preg_split('/\s+/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY));
        }
        if (isset($section['table'])) {
            $table = $section['table'];
            foreach ($table['rows'] as $row) {
                check_theory(count($row) === count($table['headers']), 'Invalid table in ' . $slug);
            }
        }
        if (isset($section['example']['steps'])) {
            check_theory(count($section['example']['steps']) >= 2, 'Incomplete worked example');
            foreach ($section['example']['steps'] as $step) {
                check_theory(trim($step['label']) !== '' && trim($step['detail']) !== '', 'Empty worked-example step');
                $stepCount++;
            }
        }
        check_theory(isset($section['figure']), 'Unillustrated section: ' . $slug . ' / ' . $section['heading']);
        if (isset($section['figure'])) {
            $id = $section['figure'];
            check_theory(isset($figures[$id]) && $figures[$id][1] !== '', 'Missing figure metadata');
            $svg = $zip->getFromName('public/assets/theory/meteorology/' . $id . '.svg');
            check_theory(is_string($svg) && str_contains($svg, '<svg') && !preg_match('/<(?:script|foreignObject)\b/i', $svg), 'Unsafe or missing illustration');
            $usedFigures[$id] = true;
        }
        $sections++;
    }
    check_theory($chapterLabCount >= 3, 'Insufficient laboratories in ' . $slug);
    foreach (array_keys($labRegistry[$slug] ?? []) as $figure) {
        check_theory(isset($chapterFigures[$figure]), 'Orphan laboratory placement: ' . $slug . '/' . $figure);
    }
    foreach ($chapter['checks'] as $check) {
        check_theory(trim($check['question']) !== '' && trim($check['answer']) !== '', 'Empty recall exercise');
        $checks++;
    }
}
check_theory(count($labIds) === 57 && count($labRegistry) === count($course['chapters']), 'Laboratory coverage mismatch');
printf("Laboratories: %d unique applets in all chapters.\n", count($labIds));
$actual = array_keys($covered);
sort($actual);
check_theory($actual === $expected && count($actual) === $course['source_count'], 'Meteorology bank coverage incomplete');
check_theory(count($usedFigures) === count($figures) && count($figures) === 61 && $sections === 62, 'Figure coverage incomplete');
check_theory($memoryCount === 58 && $overviewCount === 26 && $stepCount >= 12, 'Revision content incomplete');
$altimetry = $course['chapters']['vyskomery']['sections'];
check_theory($altimetry[1]['heading'] === 'Tlaková výška (pressure altitude, PA)' && $altimetry[4]['heading'] === 'Hustotní výška (density altitude, DA)', 'Pressure altitude must be taught before density altitude');
check_theory(str_contains($altimetry[1]['formula'], '8,23 m/hPa') && str_contains($altimetry[1]['formula'], '27 ft/hPa'), 'PA unit pair missing');
check_theory(str_contains($altimetry[4]['formula'], '36,6 m/°C') && str_contains($altimetry[4]['formula'], '120 ft/°C') && str_contains($altimetry[4]['formula'], 'TISA'), 'DA unit pair or temperature calculation missing');
check_theory(str_contains($altimetry[4]['example']['steps'][0]['detail'], '539,5 m / 1 770 ft') && str_contains($altimetry[4]['example']['steps'][3]['detail'], '3 990 ft'), 'Connected PA/DA example mismatch');
check_theory(abs(27 * 0.3048 - 8.23) < 0.01 && abs(120 * 0.3048 - 36.6) < 0.03, 'Rounded conversion constants mismatch');
check_theory(abs(0.65 * 304.8 / 100 - 1.9812) < 0.00001, 'ISA gradient conversion mismatch');
check_theory(abs(0.98 * 304.8 / 100 - 3.0) < 0.02, 'DALR conversion mismatch');
check_theory(abs(0.6 * 304.8 / 100 - 1.8) < 0.04, 'SALR conversion mismatch');
check_theory(str_contains($course['chapters']['zpravy']['sections'][5]['paras'][0], 'hft AMSL'), 'SWL reference height not explained');
printf("Revision: %d memory aids (%d overview), %d glossary entries, %d worked-example steps; every section illustrated.\n", $memoryCount, $overviewCount, count($course['glossary']), $stepCount);
printf("Theory: %d chapters, %d sections, %d/%d source IDs, %d diagrams, %d recall checks, %d sources, %d prose words.\n",
    count($course['chapters']), $sections, count($actual), count($expected), count($usedFigures), $checks, count($usedSources), $words);
$zip->close();
