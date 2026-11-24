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
'title' => 'Předletový briefing a praktické VFR scénáře',
'lead' => 'Závěrečná kapitola propojuje teorii s přípravou reálného letu. Cílem není najít jeden příznivý údaj, ale rozhodnout, zda je celá cesta bezpečně proveditelná.',
'questions' => [
18,
25,
26,
41,
60,
74,
76,
91,
92,
96,
100,
106,
108,
109,
111,
112,
113,
114,
115,
116,
117,
118,
119,
120,
121,
123,
126,
128
],
'sources' => [
'chmi-briefing',
'chmi-aviation',
'chmi-swl',
'rlp-vfr',
'easa-ops',
'caa-mountain'
],
'sections' => [
[
'heading' => 'Postup od velkého měřítka k letišti',
'paras' => [
'Začni požadavky letu: čas odletu, plánovaný návrat nebo ETA, délka letu a rezerva, plánovaná výška, nejvyšší terén, možnosti odklonu, typ letadla, vybavení a zkušenosti pilota. Poté si vytvoř meteorologický obraz od rozsáhlých systémů k lokálním podmínkám. Aktuální pozorování a předpověď se vzájemně doplňují; nespoléhej na jediný produkt.'
],
'figure' => 'briefing',
'table' => [
'headers' => [
'Krok',
'Co zkontrolovat',
'Rozhodovací otázka'
],
'rows' => [
[
'1. Synoptika',
'fronty, tlakové útvary, pohyb a výšková situace',
'Co se bude na trase měnit během letu?'
],
[
'2. Výstrahy',
'platné SIGMET, AIRMET a místní výstrahy',
'Existuje jev, který letadlo nebo pilot nezvládne?'
],
[
'3. Pozorování',
'METAR/SPECI, radarová animace, družice, webkamery',
'Odpovídá skutečnost předpovědi?'
],
[
'4. Předpovědi',
'TAF, SWL, regionální předpověď a sondáže',
'Jaké bude počasí při ETA a návratu?'
],
[
'5. Vítr a teplota',
'vítr ve výšce, přízemní složky, nárazy, DA, izoterma 0 °C',
'Dovolí to výkon letadla a rezervu?'
],
[
'6. Terén a únik',
'AGL vs AMSL, VMC, alternativy, úniková trasa, palivo',
'Existuje průběžně bezpečná cesta a plán B?'
],
[
'7. Rozhodnutí',
'go / změna / no-go, časové hranice a osobní minima',
'Kdy se otočím, odkloním nebo nepole­tím?'
]
]
]
],
[
'heading' => 'Scénář A: zimní tlaková výše',
'paras' => [
'Modelová situace: malé letiště v údolí má ráno teplotu +1 °C a rosný bod 0 °C, vítr slabý. V regionu se vyskytuje nízká inverze, výše na kopcích je slunečno. V předpovědi pro dobu návratu je přetrvávající nízká oblačnost a lokální mlha. Právě vysoký tlak a bezvětří jsou v tomto případě příčinou problému, nikoli argumentem pro odlet.',
'Zhodnoť spread, noční vývoj, rozpad inverze v regionální předpovědi, skutečnou základnu a dohlednost na cestě. Pokud není bezpečný odlet i návrat, vyčkej nebo zruš let. Slunce na kopci ani pohled nad inverzi není důkaz, že v údolí zůstane vizuální přístup.'
],
'example' => [
'title' => 'Otázka pro samostatné rozhodnutí',
'text' => 'Je odlet po krátkém rozpuštění mlhy bezpečný, pokud večer očekáváš její návrat? Pouze pokud let včetně alternativy a dostatečných rezerv vychází bezpečně v reálných podmínkách; jinak uprav plán nebo nelétej.'
]
],
[
'heading' => 'Scénář B: letní odpolední konvekce',
'paras' => [
'Modelová situace: ráno CAVOK a 25 °C, během odpoledne předpověď TCU/CB a přeháněk, vysoká vlhkost a výšková brázda. Radar v době plánování ještě nic výrazného neukazuje. Dopoledne je možné stabilnější okno, ale bouřky se mohou vytvořit rychle přímo na trase nebo u cílového letiště.',
'Posuď čas, pravděpodobnost a pohyb konvekce, sleduj trend a výstrahy. Radar bez odrazů znamená pouze absenci detekovaných srážek v daném čase, ne absenci budoucí bouřky. Nastav předem konzervativní hranice návratu. Není-li jasná cesta mimo konvekci, let odlož; v okolí přeháňky na letišti zvaž také střih a výtokový vítr.'
]
],
[
'heading' => 'Scénář C: základna oblačnosti a terén',
'paras' => [
'Modelové letiště v elevaci 1 300 ft AMSL hlásí BKN020. Základna je tedy přibližně 3 300 ft AMSL. Na trase leží hřeben 2 700 ft AMSL. Mezi vrcholem a letištní odhadovanou základnou zbývá jen 600 ft, navíc skutečný terén a oblačnost se mohou měnit. To není automaticky použitelná rezerva pro VFR, zejména při požadovaných vzdálenostech od oblačnosti, stoupání terénu, turbulenci nebo nepřesnosti výšky.',
'Pro bezpečný plán musí existovat trasa se skutečnou rezervou, případně nižší alternativní terén, při zachování pravidel pro VMC a minimální výšky. Neodvozuj lokální AGL nad celou krajinou pouze z BKN na odletovém letišti.'
],
'formula' => 'Základna AMSL ≈ elevace referenčního letiště + hlášená základna [ft AGL]'
],
[
'heading' => 'Scénář D: teplý den a krátká dráha',
'paras' => [
'Modelová situace: travnaté letiště v 1 500 ft AMSL, QNH 1003, OAT +30 °C. Orientační DA z kapitoly o výškoměru je kolem 4 000 ft. Boční vítr v nárazech, mokrá tráva nebo stoupající terén za dráhou dále komplikují výkon. Nejde jen o délku rozjezdu, ale o celou potřebnou vzdálenost a schopnost překonat překážky.',
'Použij příslušné výkonové tabulky konkrétního AFM/POH, skutečnou hmotnost, stav dráhy, vítr, teplotu a publikovanou délku. Nepoužívej odhad DA jako náhradu výkonového výpočtu. Pokud výsledky nemají přijatelnou rezervu, sniž hmotnost v rámci limitů, zvol jiné letiště/čas nebo nelétej.'
]
],
[
'heading' => 'Rozhodnutí a další studium',
'paras' => [
'Při rozhodování je důležité odolat tlaku na pokračování: cestující, časový plán nebo už investované palivo nejsou meteorologické argumenty. Dobrá praxe je určit dopředu čas a místo pro další kontrolu a podmínky, při nichž plán změníš. Za letu ověřuj, že předpověď stále odpovídá pozorování; použij aktuální informace dostupné přes ATS/FIS a neodkládej bezpečné rozhodnutí.',
'Uč se nejprve principy a poté banku otázek. Zkoušej si bez nápovědy vysvětlit, proč je odpověď správná, a proč ostatní možnosti neplatí. Učebnice mapuje 129 zveřejněných meteorologických otázek v lokální bance, ale ÚCL uvádí, že zveřejněná banka představuje jen část skutečných zkouškových otázek. Samotná znalost zveřejněné sady není záruka úspěchu.'
],
'note' => 'Tento studijní materiál není letový briefing, oficiální schválená učebnice ani náhrada výcviku. Pro let mají přednost aktuální předpisy, AIP, NOTAM, schválená dokumentace letadla, výcvik a skutečné meteorologické informace.'
]
],
'checks' => [
[
'question' => 'Jaký je rozdíl mezi radarovým snímkem a SWL mapou?',
'answer' => 'Radar především zobrazuje detekované srážky v určitém čase; SWL je předpověď význačného počasí v nízkých hladinách.'
],
[
'question' => 'Letiště hlásí SCT008, bez BKN/OVC. Je nízká oblačnost irelevantní?',
'answer' => 'Ne. Nízké jednotlivé oblaky mohou omezit VFR trasu, rozhled a dodržení vzdáleností.'
],
[
'question' => 'Co když je předpověď příznivá, ale skutečná pozorování se zhoršují rychleji?',
'answer' => 'Přehodnoť let podle aktuálního vývoje; nepokračuj jen na základě původní předpovědi.'
]
]
];
