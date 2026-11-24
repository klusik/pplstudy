<?php

/**
 * Project: PPL Study
 * Module Type: Meteorology textbook chapter
 * Purpose: Original Czech instructional text, worked examples and recall exercises.
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

return [
'title' => 'Tlakové útvary, výškové počasí a klimatologie',
'lead' => 'Vysoký tlak není automaticky VMC. Pro pochopení změn počasí je nutné rozlišovat tlak u země, geopotenciální výšku tlakových hladin a globální cirkulaci.',
'questions' => [
26,
30,
31,
32,
33,
34,
35,
78,
82,
83,
84,
85,
86,
87,
88,
89,
90,
91,
92,
123
],
'sources' => [
'metoffice-pressure',
'metoffice-conditions',
'nws-education',
'nws-cape',
'faa-weather'
],
'memory' => [
[
'label' => 'Níže / výše',
'value' => 'L: konvergence a výstup · H: divergence a sestup',
'note' => 'Přízemní idealizace. Výše v zimě nevylučuje mlhu ani nízkou oblačnost.',
'overview' => false
],
[
'label' => 'Tlakové hladiny',
'value' => '850 hPa / 500 hPa',
'note' => 'Tlaková plocha nemá pevnou geometrickou výšku. Ve studeném sloupci leží plochy blíže u sebe.',
'overview' => false
],
[
'label' => 'Tloušťka vrstvy',
'value' => 'Δz [ft] = Δz [m] / 0,3048',
'note' => 'Hypsometrická rovnice při R a g v SI dává metry; následně lze výsledek převést na stopy.',
'overview' => false
]
],
'sections' => [
[
'heading' => 'Cyklona, anticyklona, brázda a hřeben',
'paras' => [
'Tlaková níže (cyklona, L) je oblast nižšího tlaku než okolí. Přízemní sbíhání vzduchu často podporuje výstup, oblačnost a srážky; níže nemusí mít fronty, existují například tepelné níže. Tlaková výše (anticyklona, H) často podporuje sestup a oteplování vzduchu, pokles relativní vlhkosti a rozpouštění oblačnosti. Při zemi se ale může udržovat chladný vzduch pod subsidenční inverzí.',
'Brázda je protáhlá oblast nižšího tlaku či nižších výšek tlakové plochy. Hřeben je protáhlá oblast vyššího tlaku či vyšších geopotenciálních výšek. Sedlo je oblast mezi dvěma výšemi a dvěma nížemi, kde bývá slabší gradient a proměnlivé počasí. O síle větru rozhoduje spíše velikost gradientu než samotná absolutní hodnota tlaku.'
],
'figure' => 'pressure'
],
[
'heading' => 'Jak se liší tlaková výše v létě a zimě',
'figure' => 'high-season',
'paras' => [
'V létě může převládat slunečné počasí, slabý přízemní vítr, místní brízy a roztroušené Cu. Výška základen závisí na vlhkosti; při silném oslunění může být výrazná termika. Přetrvávající vysoké teploty zvyšují hustotní výšku a snižují výkonnost malého letadla. Stagnující vzduch může také hromadit zákal. Na okraji výše nebo pod výškovou níží se konvekce může rozvíjet i při relativně vysokém tlaku u země.',
'V zimě dlouhá noc a nízké sluneční záření napomáhají udržování inverzí. Slabé proudění zadržuje vlhkost a znečištění v přízemní vrstvě; rozsáhlé oblasti mlhy nebo nízkého St mohou přetrvávat mnoho hodin či dní. Nad inverzí může být teplo a jasno, ale pro VFR odlet a návrat rozhoduje počasí pod ní.'
],
'table' => [
'headers' => [
'Situace',
'Výhody',
'Typické hrozby'
],
'rows' => [
[
'Letní výše',
'málo front, často dlouhé období stabilního počasí',
'horko, DA, termika, zákal, místní bouřky'
],
[
'Zimní výše',
'slabý gradient, nad inverzí někdy jasno',
'mlhy, nízký St, mráz, námraza'
],
[
'Frontální níže',
'často lepší dohlednost za frontou mimo srážky',
'vítr, frontální srážky, nízká oblačnost, turbulence'
]
]
]
],
[
'heading' => 'Tlakové plochy ve výšce',
'paras' => [
'Přízemní synoptická mapa ukazuje tlak redukovaný na střední hladinu moře. Výškové mapy (např. 850 nebo 500 hPa) zobrazují obvykle geopotenciální výšku, tedy kde se nachází určitá tlaková plocha. Neříkají, že na této ploše je různý tlak: tlak je stejný z definice. V chladnějším vzduchu je menší geometrická tloušťka vrstvy mezi dvěma tlakovými hladinami. Proto studený příliv ve vyšší troposféře snižuje výšku tlakových ploch a může souviset s brázdou nebo výškovou níží.',
'Přísun chladného vzduchu nad teplejší a vlhčí přízemní vrstvu často zvětšuje skutečný teplotní gradient. Pokud je k dispozici vlhkost a zdvih, vznikají přeháňky nebo bouřky i bez dobře vyvinuté přízemní fronty. Výšková brázda může podporovat vertikální proudění a organizovat konvekci. Samotný pokles geopotenciální výšky neurčuje přesně přízemní tlak; sleduj mapy obou vrstev.'
],
'figure' => 'thickness',
'formula' => 'Δz [m] ≈ (R × T̄ᵥ / g) × ln(p₁/p₂)
Δz [ft] = Δz [m] / 0,3048 (R a g v SI; oba tlaky ve stejných jednotkách, např. hPa)',
'example' => [
'title' => 'Tlakové hladiny 1000 a 500 hPa',
'text' => 'Pro střední virtuální teplotu vrstvy 270 K vychází tloušťka přibližně 5 480 m (17 980 ft); při 250 K asi 5 070 m (16 630 ft). Chladnější sloupec je přibližně o 410 m (1 350 ft) tenčí. Jde o zjednodušený hypsometrický výpočet.'
]
],
[
'heading' => 'Globální cirkulace a klima',
'paras' => [
'Rovníkové oblasti přijímají více sluneční energie než polární. V idealizovaném modelu vzniká Hadleyova buňka s výstupem poblíž rovníku a sestupem kolem 30° severní a jižní šířky; nad subtropickými oceány se proto často nacházejí rozsáhlé tlakové výše. Ve středních šířkách působí Ferrelova buňka, západní větry a frontální cyklony; v polárních oblastech polární buňka. Skutečnou cirkulaci modifikují kontinenty, oceány, sezónní vývoj a rotace Země.',
'Jet stream se tvoří ve vyšších vrstvách troposféry poblíž velkých horizontálních teplotních gradientů, často v okolí tropopauzy. Střídání výškových hřebenů a brázd ovlivňuje pohyb front. Klima je dlouhodobý statistický popis, nikoliv aktuální předpověď. Pro PPL je užitečné rozlišit vliv mořského a kontinentálního klimatu, zimní inverze, letní bouřkovou sezónu, horské mikroklima a sezónní posun tlakových pásů.'
],
'figure' => 'circulation',
'note' => 'Tlaková výše na mapě neříká, zda je trasa VFR průchodná. V zimě může být rozsáhlá nízká oblačnost právě pod anticyklonou.'
]
],
'checks' => [
[
'question' => 'Proč může být v tlakové výši v zimě hustá mlha?',
'answer' => 'Slabý vítr a inverze drží chladný vlhký vzduch u země.'
],
[
'question' => 'Co se stane s tloušťkou mezi dvěma tlakovými hladinami v chladnějším vzduchu?',
'answer' => 'Zmenší se; tlakové plochy leží blíže u sebe.'
],
[
'question' => 'Co zpravidla předpovídá silnější vítr: nízké číslo tlaku nebo husté izobary?',
'answer' => 'Husté izobary značí větší gradient; absolutní hodnota sama rychlost neurčí.'
]
]
];
