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
'title' => 'Bouřky, střih větru a nebezpečné jevy',
'lead' => 'Bouřka není pouze blesk a déšť. Pro pilota je nejnebezpečnější kombinace silných vertikálních pohybů, náhlých změn větru, krup a rychlého zhoršení podmínek.',
'questions' => [
3,
40,
41,
42,
52,
67,
85,
87,
100,
101,
102,
103,
104,
105,
106,
107,
108,
109
],
'sources' => [
'nws-storm',
'nws-microburst',
'nws-microburst-glossary',
'caa-mountain',
'faa-weather'
],
'memory' => [
[
'label' => 'Životní cyklus',
'value' => 'Vývoj: ↑ · zralá buňka: ↑↓ · rozpad: ↓',
'note' => 'Stoupavé a sestupné proudy jsou současně typické pro rozvinuté stadium.',
'overview' => false
],
[
'label' => 'Microburst',
'value' => 'Horizontální rozměr < 4 km (≈ 13 123 ft; 2,16 NM)',
'note' => 'Měřítko je definice, nikoli bezpečná vzdálenost od bouřky.',
'overview' => false
],
[
'label' => 'Střih větru',
'value' => 'Změna vektoru větru na krátké vzdálenosti',
'note' => 'Pozor zejména při vzletu a přistání. Přechod z protivětru na zadní vítr může prudce snížit IAS.',
'overview' => false
],
[
'label' => 'Vertikální proudění',
'value' => '10 m/s ≈ 1 969 ft/min',
'note' => 'Pouhý převod jednotek; proudy v bouřce mohou přesáhnout možnosti letadla.',
'overview' => false
]
],
'sections' => [
[
'heading' => 'Vznik bouřky a životní cyklus',
'paras' => [
'K rozvoji bouřky jsou potřeba dostatečná vlhkost, nestabilita a zdvih. Tepelná bouřka může vzniknout při intenzivním ohřevu vlhkého přízemního vzduchu; fronta nebo pohoří mohou působit jako spouštěč i bez výrazného lokálního ohřevu. Vertikální střih může jednotlivé buňky organizovat do multicel, linií nebo supercel; takový systém může přetrvávat déle než jednoduchá buňka.',
'Ve vývojovém (kumulovém) stadiu převažuje stoupání. V rozvinutém stadiu existují současně silné stoupavé i klesavé proudy, srážky, často elektrická aktivita a výtok studeného vzduchu u země. V rozpadu postupně převládne sestupné proudění a přívod energie se omezuje. I rozpadající se buňka může mít nebezpečné výtokové proudění a zbytkovou elektrickou aktivitu.'
],
'figure' => 'thunderstorm',
'table' => [
'headers' => [
'Stadium',
'Vertikální pohyb',
'Pozorovatelný projev'
],
'rows' => [
[
'Vývoj',
'hlavně stoupavý',
'rychle rostoucí Cu/TCU'
],
[
'Rozvinuté',
'stoupavý i klesavý',
'Cb, srážky, nárazy, blesky'
],
[
'Rozpad',
'převážně sestupný',
'slábnoucí srážky, kovadlina, přetrvávající výtok'
]
]
]
],
[
'heading' => 'Rizika v bouřce i mimo oblak',
'figure' => 'storm-hazards',
'paras' => [
'Silná turbulence může způsobit velké změny zatížení a rychlosti. Kroupy mohou poškodit náběžné hrany, vrtuli, čelní sklo a další části, mohou se dostat i mimo viditelnou základnu oblaku. Blesk může tepelně či mechanicky poškodit konstrukci, elektrické systémy nebo elektroniku; může způsobit i dočasné oslnění. Námraza, srážky, ztráta dohlednosti, silné vertikální proudy a tlakové odchylky se mohou kombinovat.',
'Cb (kumulonimbus) může být ukryt v vrstevnaté oblačnosti (embedded); v noci jej nemusíš vůbec vidět. Ani let pod kovadlinou nebo pod dešťovou clonou není bezpečný způsob obcházení. Nepokoušej se prolétat bouřkovou linií nebo úzkou mezerou jen podle zobrazení na displeji. Družice, radar a výstrahy ukazují jen část situace a mají zpoždění. Při významném riziku vol trasu s velkým odstupem nebo let odlož.'
],
'note' => 'Pro běžný výcvikový VFR let nepřijímej průlet bouřkovou oblastí jako plán. Pokud bezpečná alternativa není jednoznačně dostupná, nelétej.'
],
[
'heading' => 'Húlava, downburst a microburst',
'paras' => [
'Srážky a odpařování mohou ochladit vzduch uvnitř a pod bouřkou. Hustší vzduch sestupuje, po dosažení země se rozlévá do stran. Čelo tohoto výtoku je gust front (nárazová fronta), v českém učebním kontextu húlava. Může se šířit před deštěm a přinášet skokovou změnu větru, tlakový skok a pokles teploty. Downburst je intenzivní sestupný proud s výrazným rozbíhavým výtokem u země; microburst je jeho lokální forma s horizontálním měřítkem menším než asi 4 km (≈ 13 123 ft, 2,16 NM); tato hodnota není bezpečným odstupem od bouřky. Může být mokrý nebo suchý (virga, prach).',
'Při průletu přes výtok se zpočátku může zvýšit IAS při narůstajícím protivětru, poté působí silný downdraft (sestupný proud) a na druhé straně změna na zadní vítr způsobí ztrátu IAS a výkonové rezervy. Zvlášť u země nemusí být výška ani motorový výkon dostatečný pro vyrovnání situace. Rozhodující je včasná prevence: vyčkat, odklonit se nebo přerušit přiblížení podle výcviku a publikovaných postupů.'
],
'figure' => 'microburst'
],
[
'heading' => 'Střih větru a druhy turbulence',
'figure' => 'shear-turbulence',
'paras' => [
'Wind shear je prostorová změna vektoru větru (směru nebo rychlosti) na krátké vzdálenosti, horizontální nebo vertikální. Náraz je změna v čase na místě měření. Turbulence znamená nepravidelné vířivé proudění. Je-li vítr různý ve dvou sousedních vrstvách, mohou na jejich rozhraní vznikat turbulentní víry. Střih nemusí být viditelný ani doprovázen mraky.',
'Při vzletu přes přízemní inverzi se může letadlo dostat z klidné vrstvy do výrazně odlišného větru. Náhlý přechod z protivětru na menší protivítr či zadní vítr může snížit IAS a stoupavost; opačná změna může IAS zvýšit. U letiště v údolí za silného příčného větru se mohou vyskytovat rotory a prudké změny směru, někdy téměř o 180°. Pozoruj kouř, pohyb oblaků, virgu a okolní přeháňky, ale jejich absence střih nevylučuje.'
],
'table' => [
'headers' => [
'Typ',
'Původ',
'Kde čekat'
],
'rows' => [
[
'Mechanická',
'tření a překážky',
'za budovami, stromy, hřebeny'
],
[
'Termická',
'nerovnoměrné ohřívání a konvekce',
'nad osluněnou krajinou, pod Cu'
],
[
'Frontální',
'gradienty a střih v přechodu hmot',
'u aktivních front'
],
[
'Rotorová',
'horská vlna a závětrné víry',
'závětrná strana pohoří'
],
[
'CAT',
'střih v čistém vzduchu',
'zejména vyšší hladiny, jet stream, též jiné střihové vrstvy'
],
[
'Wake turbulence',
'víry vytvářené vztlakem letadla, nikoli počasím',
'za letadlem; vítr ovlivňuje jejich pohyb'
]
]
],
'example' => [
'title' => 'Přeháňka vedle dráhy',
'text' => 'I když je nad prahem čistá obloha, přeháňka poblíž může vysílat výtok k letišti. Náhlá změna větru, hlášený shear nebo výstraha jsou důvodem neuspěchat vzlet či přistání.'
]
]
],
'checks' => [
[
'question' => 'Ve kterém stadiu jednoduché bouřkové buňky současně převládají silné stoupavé i sestupné proudy?',
'answer' => 'V rozvinutém stadiu.'
],
[
'question' => 'Musí být microburst doprovázen deštěm na zemi?',
'answer' => 'Ne, existují i suché microbursty.'
],
[
'question' => 'Proč je střih nebezpečný při vzletu?',
'answer' => 'Rychlá změna relativního proudění mění IAS a výkonovou rezervu v malé výšce.'
]
]
];
