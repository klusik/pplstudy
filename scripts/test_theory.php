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

if (PHP_SAPI !== 'cli' || $argc !== 2) {
    exit(1);
}
require dirname(__DIR__) . '/app/services/theory.php';

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
foreach ($course['chapters'] as $slug => $chapter) {
    check_theory($zip->locateName('app/theory/meteorology/' . $slug . '.php') !== false, 'Missing chapter in ZIP: ' . $slug);
    check_theory(trim($chapter['title']) !== '' && trim($chapter['lead']) !== '', 'Missing chapter heading');
    check_theory(count($chapter['sections']) >= 3 && count($chapter['checks']) >= 3, 'Incomplete chapter: ' . $slug);
    foreach ($chapter['questions'] as $id) {
        check_theory(is_int($id) && in_array($id, $expected, true), 'Invalid source question ID');
        $covered[$id] = true;
    }
    foreach ($chapter['sources'] as $id) {
        check_theory(isset($sources[$id]) && str_starts_with($sources[$id][1], 'https://'), 'Unknown source: ' . $id);
        $usedSources[$id] = true;
    }
    foreach ($chapter['sections'] as $section) {
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
        if (isset($section['figure'])) {
            $id = $section['figure'];
            check_theory(isset($figures[$id]) && $figures[$id][1] !== '', 'Missing figure metadata');
            $svg = $zip->getFromName('public/assets/theory/meteorology/' . $id . '.svg');
            check_theory(is_string($svg) && str_contains($svg, '<svg') && !preg_match('/<(?:script|foreignObject)\b/i', $svg), 'Unsafe or missing illustration');
            $usedFigures[$id] = true;
        }
        $sections++;
    }
    foreach ($chapter['checks'] as $check) {
        check_theory(trim($check['question']) !== '' && trim($check['answer']) !== '', 'Empty recall exercise');
        $checks++;
    }
}
$actual = array_keys($covered);
sort($actual);
check_theory($actual === $expected && count($actual) === $course['source_count'], 'Meteorology bank coverage incomplete');
check_theory(count($usedFigures) === count($figures) && count($figures) === 23, 'Figure coverage incomplete');
printf("Theory: %d chapters, %d sections, %d/%d source IDs, %d diagrams, %d recall checks, %d sources, %d prose words.\n",
    count($course['chapters']), $sections, count($actual), count($expected), count($usedFigures), $checks, count($usedSources), $words);
$zip->close();
