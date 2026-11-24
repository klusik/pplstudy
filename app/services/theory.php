<?php

/**
 * Project: PPL Study
 * File: app/services/theory.php
 * Module Type: Theory course registry and content service
 *
 * Purpose:
 *   Keep the curriculum, course routes, sources and chapter selection explicit.
 *   Chapter files are trusted project-authored data, never user-supplied paths.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Services;

/** Return the registered theory courses, keyed by stable route slug. */
function theory_registry(): array
{
    return [
        'meteorologie' => [
            'subject_id' => 3,
            'title' => 'Letecká meteorologie',
            'subtitle' => 'Meteorologie pro PPL(A): od fyziky atmosféry k bezpečné předletové přípravě',
            'description' => 'Ucelený český výklad, názorná schémata, výpočty, dekódování meteorologických zpráv, praktické situace a kontrolní otázky.',
            'source_version' => 'ÚCL, Meteorologie – letoun, verze 1.1 (29. 12. 2020)',
            'source_count' => 129,
            'quick_reference' => true,
            'exam_url' => 'https://www.caa.gov.cz/zkusebni-otazky-pro-zkousky-teoretickych-znalosti-ppl-lapl/',
            'chapter_files' => [
                'atmosfera' => 'atmosfera.php',
                'vyskomery' => 'vyskomery.php',
                'vlhkost' => 'vlhkost.php',
                'stabilita' => 'stabilita.php',
                'oblacnost' => 'oblacnost.php',
                'mlha' => 'mlha.php',
                'vitr' => 'vitr.php',
                'fronty' => 'fronty.php',
                'tlakove-utvary' => 'tlakove-utvary.php',
                'hory' => 'hory.php',
                'namraza' => 'namraza.php',
                'bourky' => 'bourky.php',
                'zpravy' => 'zpravy.php',
                'briefing' => 'briefing.php',
            ],
            'directory' => dirname(__DIR__) . '/theory/meteorology',
        ],
    ];
}

/** Find the course route for a subject without loading chapter content. */
function theory_slug_for_subject(int $subjectId): ?string
{
    foreach (theory_registry() as $slug => $course) {
        if ($course['subject_id'] === $subjectId) {
            return $slug;
        }
    }
    return null;
}

/** Load the known chapter files for one registered course. */
function theory_course(string $slug): ?array
{
    $course = theory_registry()[$slug] ?? null;
    if ($course === null) {
        return null;
    }
    $course['slug'] = $slug;
    $course['chapters'] = [];
    foreach ($course['chapter_files'] as $chapterSlug => $filename) {
        $chapter = require $course['directory'] . '/' . $filename;
        $chapter['slug'] = $chapterSlug;
        $course['chapters'][$chapterSlug] = $chapter;
    }
    unset($course['directory'], $course['chapter_files']);
    return $course;
}

/** Return the bibliography used by the theory course; all URLs are editorial constants. */
function theory_sources(): array
{
    return [
        'faa-pa-da' => ['FAA · Safety of Flight: Effects of Density Altitude', 'https://www.faa.gov/air_traffic/publications/atpubs/AIM_html/chap7_section_6.html', 'Rozlišení hustotní, tlakové a skutečné výšky; důsledky pro výkon.'],
        'nws-pa-da' => ['NOAA/NWS · Pressure Altitude & Density Altitude', 'https://www.weather.gov/media/zhu/ZHU_Training_Page/winds/pressure_winds/Temp_Density_Pressure.pdf', 'Definice PA, výpočet z QNH a orientační pravidlo 120 ft/°C.'],
        'rlp-units' => ['AIP ČR · GEN 2.1 Měřicí jednotky', 'https://aim.rlp.cz/eaip/html/eAIP/LK-GEN-2.1-cz-CZ.html', 'Jednotky používané v českých leteckých publikacích a provozu.'],
        'rlp-altimeter' => ['VFR příručka ČR · Postupy pro nastavení výškoměru', 'https://aim.rlp.cz/vfrmanual/', 'Aktuální převodní výška, hladina, výjimky a postupy.'],
        'nist-units' => ['NIST · Guide to the SI, Appendix B: Conversion Factors', 'https://www.nist.gov/pml/special-publication-811/nist-guide-si-appendix-b-conversion-factors', 'Přesný převod mezinárodní stopy na metry (1 ft = 0,3048 m) a pravidla zaokrouhlování.'],
        'easa-syllabus' => ['EASA – Easy Access Rules for Aircrew, AMC1 FCL.210; FCL.215', 'https://www.easa.europa.eu/en/document-library/easy-access-rules/online-publications/easy-access-rules-aircrew-regulation-eu-no?kw=LAPL&page=5', 'Osnova teoretických znalostí PPL(A); evropský rámec.'],
        'faa-weather' => ['FAA – Aviation Weather Handbook (FAA-H-8083-28B, 2026)', 'https://www.faa.gov/regulationspolicies/handbooksmanuals/aviation/faa-h-8083-28b-aviation-weather-handbook', 'Učební příručka fyziky počasí a leteckých rizik; americké provozní postupy zde nejsou přebírány jako evropské.'],
        'faa-handbooks' => ['FAA – Pilot’s Handbook of Aeronautical Knowledge a další příručky', 'https://www.faa.gov/regulations_policies/handbooks_manuals/aviation', 'Referenční výklad atmosféry, výškoměrů a výkonu.'],
        'nws-education' => ['NOAA/NWS – meteorologické vzdělávací materiály', 'https://www.weather.gov/dmx/education', 'Atmosféra, cirkulace, oblaky a meteorologické mapy.'],
        'nws-gradient' => ['NOAA/NWS – adiabatický teplotní gradient', 'https://forecast.weather.gov/glossary.php?word=Adiabatic+Lapse+Rate', 'Suchá a nasycená adiabata.'],
        'nws-stability' => ['NOAA/NWS – Environmental Parameters and Indices', 'https://www.weather.gov/lmk/indices', 'Stabilita atmosféry a porovnání teplotních gradientů.'],
        'nws-cape' => ['NOAA/NWS – Mesoscale Parameters', 'https://www.weather.gov/source/zhu/ZHU_Training_Page/convective_parameters/Sounding_Stuff/MesoscaleParameters.html', 'CAPE, CIN a konvekce.'],
        'nws-fog' => ['NOAA/NWS – Fog Definitions', 'https://www.weather.gov/source/zhu/ZHU_Training_Page/fog_stuff/fog_definitions/Fog_definitions.html', 'Mechanismy tvorby mlhy.'],
        'nws-fog-rules' => ['NOAA/NWS – Fog Ingredients', 'https://www.weather.gov/source/zhu/ZHU_Training_Page/fog_stuff/forecasting_fog/FORECASTING_FOG.htm', 'Podmínky vzniku a rozpouštění mlh.'],
        'nws-pressure' => ['NOAA/NWS – Pressure and Wind', 'https://www.weather.gov/source/zhu/ZHU_Training_Page/winds/pressure_winds/Pressure.htm', 'Tlakový gradient, Coriolisovo zrychlení a tření.'],
        'nws-airmasses' => ['NOAA/NWS – Basic Weather Education', 'https://www.weather.gov/crp/weather_education', 'Vzduchové hmoty a fronty.'],
        'nws-storm' => ['NOAA/NWS – Thunderstorm Life Cycle', 'https://www.weather.gov/spotterguide/life', 'Fáze bouřkové buňky.'],
        'nws-microburst' => ['NOAA/NWS – What is a Microburst?', 'https://www.weather.gov/ama/microbursts', 'Fyzika sestupných proudů a suchých i mokrých microburstů.'],
        'nws-microburst-glossary' => ['NOAA/NWS – Microburst (glosář)', 'https://forecast.weather.gov/glossary.php?word=MICROBURST', 'Definice a riziko střihu.'],
        'wmo-genera' => ['WMO – International Cloud Atlas: genera', 'https://cloudatlas.wmo.int/en/principles-of-cloud-classification-genera.html', 'Deset rodů oblačnosti.'],
        'wmo-levels' => ['WMO – International Cloud Atlas: levels', 'https://cloudatlas.wmo.int/en/some-useful-concepts-levels.html', 'Výškové rozmezí oblačnosti.'],
        'wmo-classification' => ['WMO – International Cloud Atlas: přehled klasifikace', 'https://cloudatlas.wmo.int/en/cloud-classification-summary.html', 'Druhy, odrůdy a další znaky oblaků.'],
        'metoffice-fronts' => ['Met Office – Weather fronts', 'https://weather.metoffice.gov.uk/learn-about/weather/atmosphere/weather-fronts', 'Vznik a symbolika front.'],
        'metoffice-weather' => ['Met Office – Understanding weather', 'https://weather.metoffice.gov.uk/learn-about/met-office-for-schools/other-content/other-resources/understanding-weather', 'Typické změny při přechodu front.'],
        'metoffice-pressure' => ['Met Office – High and low pressure', 'https://weather.metoffice.gov.uk/learn-about/weather/how-weather-works/high-and-low-pressure', 'Výše, níže a blokující situace.'],
        'metoffice-conditions' => ['Met Office – Weather conditions', 'https://weather.metoffice.gov.uk/learn-about/weather/how-weather-works/high-and-low-pressure/weather-conditions', 'Sezónní rozdíly počasí v tlakových útvarech.'],
        'metoffice-foehn' => ['Met Office – Foehn effect', 'https://weather.metoffice.gov.uk/learn-about/weather/types-of-weather/wind/foehn-effect', 'Termodynamické i dynamické mechanismy fénu.'],
        'bora-study' => ['Zecchetto & De Biasio – Sea Surface Winds over the Mediterranean Basin', 'https://doi.org/10.1175/JAM2498.1', 'Orografické proudění, bóra a mistrál.'],
        'caa-mountain' => ['UK CAA – Mountain Flying', 'https://www.caa.co.uk/general-aviation/safety-topics/mountain-flying/', 'Výcvik, terénní a meteorologické riziko v horách.'],
        'caa-winter' => ['UK CAA – Safety Sense 03: Winter Flying', 'https://www.caa.co.uk/data-and-publications/publications/documents/content/safety-sense-leaflet-03/', 'Zimní provoz, námraza a kontaminace.'],
        'easa-engine' => ['EASA – EGAST GA 5: Piston Engine Icing', 'https://www.easa.europa.eu/en/document-library/general-publications/egast-leaflet-ga-5-piston-engine-icing', 'Námraza pístových motorů.'],
        'easa-icing' => ['EASA – Easy Access Rules for Air Operations, Part-NCO', 'https://www.easa.europa.eu/en/document-library/easy-access-rules/online-publications/easy-access-rules-air-operations?erules-id=ERULES-1963177438-18537', 'NCO.OP.165 a 170, kontaminace a námraza.'],
        'easa-ops' => ['EASA – Easy Access Rules for Air Operations, Part-NCO', 'https://www.easa.europa.eu/en/document-library/easy-access-rules/online-publications/easy-access-rules-air-operations?erules-id=ERULES-1963177438-18537', 'NCO.OP.160, meteorologické podmínky a přehodnocování letu.'],
        'metar-guide' => ['Met Office – How to decode a METAR', 'https://docs.mavis.metoffice.gov.uk/guidance/metar-decode/', 'Struktura zprávy a skupiny trendu.'],
        'taf-guide' => ['Met Office – How to decode a TAF', 'https://docs.mavis.metoffice.gov.uk/guidance/taf-decode/', 'Platnost, změnové skupiny a pravděpodobnost.'],
        'chmi-aviation' => ['ČHMÚ – Letecká meteorologie', 'https://www.chmi.cz/letectvi', 'Rozcestník aktuálních českých meteorologických produktů.'],
        'chmi-briefing' => ['ČHMÚ – Jak udělat předletovou přípravu?', 'https://www.chmi.cz/-/jak-udelat-predletovou-pripravu-', 'Postup vyhodnocení pozorování, předpovědí a rizik.'],
        'chmi-swl' => ['ČHMÚ – SWL mapa pro ČR', 'https://www.chmi.cz/letectvi/predpovedi-pro-letani/swl-mapa', 'Předpověď význačného počasí do FL100, legenda a jednotky.'],
        'chmi-taf' => ['ČHMÚ – TAF', 'https://www.chmi.cz/letectvi/textove-predpovedi-pro-letani/predpovedi-taf', 'Letištní předpovědi.'],
        'chmi-metar' => ['ČHMÚ – METAR/SPECI', 'https://en.chmi.cz/web/chmi.cz/letectvi/aktualni-pocasi-pro-letani/zpravy-metar-speci', 'Aktuální letištní pozorování.'],
        'chmi-sigmet' => ['ČHMÚ – SIGMET a výstrahy pro letiště', 'https://www.chmi.cz/letectvi/sigmet-vystrahy-pro-letiste', 'Výstražné informace FIR Praha.'],
        'rlp-vfr' => ['ŘLP ČR – VFR příručka ČR, GEN 7', 'https://aim.rlp.cz/vfrmanual/', 'Aktuální vydání a meteorologické služby v České republice.'],
        'rlp-aim' => ['ŘLP ČR – AIM', 'https://aim.rlp.cz/', 'AIP, VFR příručka, aktuální provozní a meteorologické informace.'],
    ];
}

/** Return original diagram captions and alternative text for registered assets. */
function theory_figures(): array
{
    return [
        'atmosphere' => ['Teplotní profil ISA v troposféře a spodní stratosféře', 'Svislá osa výšky, vodorovná teploty, pokles z 15 na −56,5 °C do 11 km (36 089 ft) a poté izotermie; stupnice v km a ft.'],
        'inversion' => ['Normální pokles, izotermie a inverze', 'Tři teplotní profily: pokles, stálá teplota a růst teploty s výškou.'],
        'altimeter' => ['Tři referenční tlakové hladiny', 'Schéma letadla nad letištěm s referencemi QNH, QFE a STD.'],
        'humidity' => ['Ochlazování vzduchu při stálém rosném bodu', 'Teplota se přibližuje rosnému bodu a relativní vlhkost roste k nasycení.'],
        'lapse' => ['Porovnání teplotních gradientů', 'Suchá adiabata přibližně 0,98 °C/100 m (3 °C/1 000 ft), ISA 0,65 (2) a modelová nasycená adiabata 0,6 (1,8); výšková osa v km a ft.'],
        'stability' => ['Vztlak vzduchové částice', 'Ve stabilní vrstvě je vychýlená částice chladnější než okolí, v nestabilní teplejší.'],
        'clouds' => ['Deset rodů oblaků a jejich obvyklé hladiny', 'Vysoké Ci, Cs, Cc; střední Ac, As, Ns; nízké St, Sc, Cu a Cb s vertikálním rozvojem.'],
        'fog' => ['Tři základní mechanismy mlhy', 'Radiační ochlazování země, vlhký vzduch nad chladným povrchem a výstup po svahu.'],
        'wind' => ['Geostrofický a přízemní vítr', 'Proudění ve výšce podél izobar a u země šikmo k nižšímu tlaku.'],
        'pressure' => ['Výše a níže na severní polokouli', 'V níži proudění proti směru hodinových ručiček dovnitř, ve výši po směru ven.'],
        'breeze' => ['Denní a noční místní proudění', 'Za dne bríza z vody na pevninu a vzduch stoupá po svahu, v noci opačný směr.'],
        'warm-front' => ['Řez teplou frontou', 'Teplý vzduch pomalu vystupuje nad chladný; postup Ci, Cs, As a Ns před frontou.'],
        'cold-front' => ['Řez studenou frontou', 'Studený vzduch podsouvá klín pod teplý, v místě výstupu vzniká konvekce.'],
        'front-symbols' => ['Symboly front na synoptické mapě', 'Studená modrá trojúhelníky, teplá červená půlkruhy, okluze fialová obojí, stacionární střídavě na opačných stranách.'],
        'thickness' => ['Tloušťka tlakových vrstev', 'Stejný interval tlaků zabírá ve studeném sloupci menší geometrickou výšku.'],
        'circulation' => ['Idealizované cirkulační buňky', 'Výstup v rovníkové oblasti, sestup okolo 30 stupňů, výstup okolo 60 stupňů a sestup u pólu.'],
        'mountains' => ['Orografický výstup a fén', 'Na návětří ochlazování a srážky, na závětří stlačování a oteplování.'],
        'mountain-wave' => ['Horská vlna a rotor', 'Proudění za hřebenem osciluje, lentikulární oblaky na vrcholech, rotory pod vlnou.'],
        'icing' => ['Zrnitá a čirá námraza na náběžné hraně', 'Malé kapky vytvářejí drsnou bělavou vrstvu, velké kapky tvoří led zasahující za náběžnou hranu.'],
        'thunderstorm' => ['Životní cyklus bouřky', 'Vývoj s výstupem, zralá buňka s výstupem a sestupem, rozpad s převahou sestupu.'],
        'microburst' => ['Microburst a nebezpečný střih', 'Sestupný vzduch se při zemi rozbíhá, letadlo může ztratit protivítr a dostat zadní vítr.'],
        'products' => ['Rozdíly mezi meteorologickými mapami', 'Synoptika, radar, družice a SWL odpovídají na odlišné otázky.'],
        'briefing' => ['Postup předletové meteorologické přípravy', 'Od synoptiky přes výstrahy, pozorování a předpovědi k posouzení letadla, trasy a rozhodnutí.'],
        'isa-reference' => ['ISA: referenční hodnoty', 'Standardní tlak, teplota a hustota na hladině moře; teplota se s výškou snižuje o 0,65 °C na 100 m, přibližně 2 °C na 1 000 ft.'],
        'density-particles' => ['Teplota a hustota při stejném tlaku', 'Ve stejném objemu teplejšího vzduchu je při stejném tlaku méně hmoty. Schéma částic je kvalitativní.'],
        'pressure-altitude' => ['Tlaková výška při standardním nastavení', 'Na stejném letišti při QNH 1003 hPa ukáže výškoměr elevaci 457 m / 1 500 ft. Při STD 1013,25 hPa ukáže vyšší tlakovou výšku asi 539 m / 1 770 ft; letadlo zůstává na místě.'],
        'pressure-error' => ['Tlaková chyba výškoměru', 'Modelový přechod z QNH 1013 na 993 hPa se stejnou indikací; tlaková plocha klesá a odhad rozdílu výšky je 165 m nebo 540 ft, nikoli univerzální skutečná korekce.'],
        'cold-error' => ['Teplotní chyba výškoměru', 'V chladném vzduchu jsou stejné tlakové hladiny níže; letadlo při stejné indikaci letí geometricky níže.'],
        'density-altitude' => ['Hustotní výška v horkém dni', 'Letiště 457 m / 1500 ft, QNH 1003 hPa a OAT 30 °C; tlaková výška 539 m / 1770 ft a orientační DA 1216 m / 3990 ft.'],
        'water-phase' => ['Skupenství vody a latentní teplo', 'Tání, vypařování a sublimace spotřebovávají energii; mrznutí, kondenzace a desublimace ji uvolňují.'],
        'cloud-base-lcl' => ['Odhad základny termických kumulů', 'Teplota stoupající částice klesá rychleji než její rosný bod, až do nasycení. Při spreadu 10 °C je odhad 1220 m nebo 4000 ft AGL.'],
        'thermal' => ['Termický výstup a vznik Cu', 'Sluneční ohřev vytváří termiku, při dosažení nasycení vznikne Cu. Okolní sestupné proudy mohou být bezoblačné.'],
        'cape-cin' => ['Konvekční energie a její bariéra', 'Schematické porovnání teploty částice a okolí: CIN brzdí počáteční výstup; nad LFC má teplejší částice kladný vztlak až k EL. Nejde o skutečný aerologický diagram.'],
        'sounding' => ['Jak číst modelový vertikální profil', 'Názorný profil teploty T a rosného bodu Td, inverzní vrstva a nulová izoterma. Výšková osa uvádí km a ft; nejde o skutečnou sondáž.'],
        'cloud-recognition' => ['Rozpoznávací znaky čtyř oblaků', 'Vláknitý cirrus, kupovitý cumulus, hladký altocumulus lenticularis a mohutný cumulonimbus s kovadlinou.'],
        'ceiling' => ['Základna oblaků versus ceiling', 'Nízké jednotlivé oblaky FEW008 nejsou ceiling; nejnižší BKN015 udává 457 m / 1500 ft nad letištěm.'],
        'precipitation-growth' => ['Jak rostou srážkové částice', 'V teplém oblaku se kapky slučují; ve smíšeném oblaku roste led i na úkor přechlazených kapek.'],
        'visibility' => ['Dohlednost, RVR a ceiling', 'Schéma rozlišuje převládající dohlednost, výhled z kokpitu, RVR podél dráhy a výšku základny oblačnosti.'],
        'fog-advection-slope' => ['Dvě odlišné cesty k nasycení', 'Vlhký vzduch je ochlazen nad studeným povrchem nebo při nuceném výstupu po svahu.'],
        'visibility-decision' => ['Zhoršující se dohlednost na trase', 'Modelová cesta k terénu zakrytému oblačností a mlhou. Bezpečné rozhodnutí o odklonu musí nastat před ztrátou rezervy.'],
        'wind-components' => ['Rozklad protivětru a boční složky', 'Pro úhel 30° mezi osou dráhy a směrem větru 16 kt vychází čelní složka 13,9 kt (7,2 m/s) a boční 8 kt (4,1 m/s).'],
        'airmass-matrix' => ['Vzduchové hmoty podle místa původu', 'Maritimní a kontinentální polární či tropická hmota se liší zejména teplotou a vlhkostí. Nejde o zeměpisnou mapu.'],
        'frontal-timeline' => ['Průběh tlaku a počasí při přechodu níže', 'Modelové pořadí: pokles tlaku před teplou frontou, přibližně ustálený tlak v teplém sektoru, růst za studenou frontou.'],
        'high-season' => ['Tlaková výše v létě a v zimě', 'Letní výše může přinášet termiku a vysokou hustotní výšku; zimní výše může uzavřít vlhkost pod inverzí.'],
        'foehn-temperature' => ['Fén a teplotní výpočet', 'Vzduch stoupá o 1000 m nenasycený a dalších 1000 m nasycený, po ztrátě kondenzátu sestupuje o 2000 m. Přibližné teploty 15, 5, −1 a 19 °C.'],
        'regional-winds' => ['Místní zesílení mistrálu a bóry', 'Mistrál je usměrňován údolím Rhôny směrem k moři; bóra může prudce sestupovat z pevniny a hor k Jadranu.'],
        'ice-types' => ['Tři typy ledu na profilu křídla', 'Schematické porovnání drsné zrnité, čiré a nepravidelné smíšené námrazy na náběžné hraně.'],
        'iced-airfoil' => ['Vliv ledu na proudění a vztlak', 'Led mění tvar náběžné hrany, podporuje odtržení proudění, zvyšuje odpor a obvykle snižuje maximální vztlak. Nákres je kvalitativní.'],
        'carb-icing' => ['Námraza ve Venturiho trubici', 'Při poklesu tlaku a odpařování paliva se směs ochlazuje; led v nejužším místě omezuje průtok. Jde o schematický princip, nikoli schéma konkrétního karburátoru.'],
        'storm-hazards' => ['Nebezpečí uvnitř i mimo Cb', 'Bouřkový oblak může mít silné vertikální proudy, kroupy, blesky a výtok sahající mimo viditelný oblak.'],
        'shear-turbulence' => ['Střih větru a mechanická turbulence', 'Rychlejší proudění ve vyšší vrstvě a vířivé proudy za překážkami; střih a turbulence jsou příbuzné, ale odlišné jevy.'],
    ];
}
