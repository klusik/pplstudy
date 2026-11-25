<?php

/**
 * Project: PPL Study
 * File: app/services/theory_labs.php
 * Module Type: Theory service
 * Purpose: Register section-local meteorology laboratory IDs by chapter and figure.
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Services;

/** Return the editorial placement of the browser laboratories. */
function theory_labs(): array
{
    return [
        'atmosfera' => [
            'atmosphere' => ['isa-column'],
            'isa-reference' => ['isa-deviation'],
            'density-particles' => ['air-density'],
            'inversion' => ['inversion-profile'],
        ],
        'vyskomery' => [
            'altimeter' => ['altimeter-settings'],
            'pressure-altitude' => ['pressure-altitude'],
            'pressure-error' => ['pressure-travel'],
            'cold-error' => ['cold-altimetry'],
            'density-altitude' => ['density-altitude'],
        ],
        'vlhkost' => [
            'water-phase' => ['water-state'],
            'humidity' => ['humidity-dew'],
            'lapse' => ['parcel-lapse'],
            'cloud-base-lcl' => ['cloud-base'],
        ],
        'stabilita' => [
            'stability' => ['buoyancy'],
            'thermal' => ['thermal-trigger'],
            'cape-cin' => ['cape-cin'],
            'sounding' => ['sounding'],
        ],
        'oblacnost' => [
            'clouds' => ['cloud-genera'],
            'ceiling' => ['oktas', 'ceiling-layers'],
            'precipitation-growth' => ['rain-growth'],
        ],
        'tlakove-utvary' => [
            'pressure' => ['pressure-systems'],
            'high-season' => ['high-season'],
            'thickness' => ['thickness'],
        ],
        'vitr' => [
            'wind' => ['pressure-gradient'],
            'pressure' => ['surface-wind'],
            'wind-components' => ['wind-components'],
            'breeze' => ['breeze-cycle'],
        ],
        'fronty' => [
            'airmass-matrix' => ['airmass'],
            'warm-front' => ['front-section'],
            'front-symbols' => ['front-symbols'],
            'frontal-timeline' => ['front-timeline'],
        ],
        'mlha' => [
            'visibility' => ['visibility-ceiling'],
            'fog' => ['radiation-fog'],
            'fog-advection-slope' => ['advection-fog'],
            'visibility-decision' => ['visibility-trend'],
        ],
        'namraza' => [
            'icing' => ['icing-conditions'],
            'ice-types' => ['ice-shapes'],
            'iced-airfoil' => ['wing-contamination'],
            'carb-icing' => ['carb-cooling'],
        ],
        'bourky' => [
            'thunderstorm' => ['storm-life'],
            'microburst' => ['microburst'],
            'shear-turbulence' => ['wind-shear'],
        ],
        'hory' => [
            'mountains' => ['orographic-lift'],
            'mountain-wave' => ['mountain-wave'],
            'foehn-temperature' => ['foehn'],
            'regional-winds' => ['regional-wind'],
        ],
        'zpravy' => [
            'weather-info-types' => ['weather-products'],
            'metar-anatomy' => ['metar-build'],
            'taf-timeline' => ['taf-window'],
            'sigmet-volume' => ['sigmet-volume'],
        ],
        'briefing' => [
            'briefing' => ['briefing-sequence'],
            'winter-valley' => ['winter-valley'],
            'summer-convection' => ['convection-day'],
            'terrain-ceiling' => ['terrain-ceiling'],
            'hot-runway' => ['hot-runway'],
            'go-no-go' => ['briefing-options'],
        ],
    ];
}

/** Resolve a laboratory only from the registered chapter and existing figure ID. */
function theory_labs_for_section(string $chapter, string $figure): array
{
    return theory_labs()[$chapter][$figure] ?? [];
}

/** Count all laboratory placements in one registered chapter. */
function theory_lab_count(string $chapter): int
{
    return array_sum(array_map('count', theory_labs()[$chapter] ?? []));
}
