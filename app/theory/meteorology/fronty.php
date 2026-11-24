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
'title' => 'Vzduchové hmoty a frontální systémy',
'lead' => 'Fronty nejsou jen čáry v mapě: jsou prostorové přechody mezi rozdílnými vzduchovými hmotami. Naučíš se předvídat typický sled počasí před frontou i za ní.',
'questions' => [
34,
35,
68,
69,
70,
71,
72,
73,
74,
75,
76,
77,
78,
79,
80,
81,
88
],
'sources' => [
'nws-airmasses',
'metoffice-fronts',
'metoffice-weather',
'faa-weather'
],
'sections' => [
[
'heading' => 'Odkud přichází vzduch',
'paras' => [
'Vzduchová hmota získává vlastnosti během pobytu nad rozsáhlou oblastí s podobným povrchem. Geografický původ charakterizuje především teplotu a vlhkost; při pohybu se hmota modifikuje výměnou tepla a vody s povrchem. Kontinentální polární vzduch přitékající v zimě z východu může být velmi chladný a relativně suchý. Po průchodu nad teplejší nebo vlhčí oblastí se jeho charakter mění.',
'Střední Evropu významně ovlivňuje střídání polárního a teplejšího subtropického vzduchu a západní cirkulace nad severním Atlantikem. Polární fronta je rozsáhlá přechodová oblast mezi teplejším a studenějším vzduchem ve středních šířkách, v níž často vznikají frontální níže. Fronta není ostrá svislá stěna; je to šikmá trojrozměrná přechodová zóna.'
],
'table' => [
'headers' => [
'Hmota',
'Původ',
'Typické vlastnosti v místě původu'
],
'rows' => [
[
'cP',
'kontinentální polární',
'chladná, relativně suchá'
],
[
'mP',
'maritimní polární',
'chladná, vlhčí'
],
[
'cT',
'kontinentální tropická',
'teplá, často suchá'
],
[
'mT',
'maritimní tropická',
'teplá, vlhká'
],
[
'A',
'arktická',
'velmi studená, později se může měnit při transportu'
]
]
]
],
[
'heading' => 'Teplá fronta',
'paras' => [
'Teplejší vzduch postupuje do oblasti chladnějšího vzduchu a po pozvolna skloněném rozhraní vystupuje nad něj. Před příchodem samotné přízemní fronty může pilot nejdříve pozorovat vysokou oblačnost Ci a Cs, poté přibývá As a následně mohutný Ns. Základna oblačnosti často klesá a nastupují souvislé srážky, může se vytvořit mlha. Během přechodu často roste teplota a rosný bod a mění se vítr. Tlak před frontou většinou klesá, později se pokles zmírňuje.',
'Pro VFR je zrádné, že vysoká oblačnost před frontou může působit neškodně, zatímco zhoršování oblačnosti a dohlednosti probíhá postupně na velkém území. Při přechlazené srážkové vrstvě může vznikat namrzající déšť. Skutečný sled závisí na aktivitě fronty, vlhkosti, terénu a roční době.'
],
'figure' => 'warm-front'
],
[
'heading' => 'Studená fronta',
'paras' => [
'Studený vzduch postupuje pod teplý a nutí jej stoupat. Rozhraní má často strmější sklon než teplá fronta. V nestabilním vlhkém teplém sektoru může vzniknout řada TCU/Cb, přeháněk a bouřek, prudké změny větru a turbulence. Ne každá studená fronta má bouřky: stabilnější situace může přinést vrstevnaté srážky nebo jen změnu větru.',
'Po přechodu fronty často klesne teplota, tlak začne růst, viditelnost se mimo srážky zlepší. Studený vzduch proudící nad teplejší povrch může být konvektivní; za frontou proto očekávej Cu, přeháňky a nárazy větru, ne nutně okamžitě klidné počasí.'
],
'figure' => 'cold-front'
],
[
'heading' => 'Okluze a stacionární fronta',
'paras' => [
'Ve vyvíjející se frontální níži může studená fronta dohnat teplou a zvednout teplý sektor od země. Vzniká okluzní fronta. Podle relativní teploty vzduchu před a za okluzí se odlišuje teplý a studený typ. Počasí bývá složité, od dlouhotrvajících srážek po vloženou konvekci. Stacionární fronta se vůči zemskému povrchu významně neposouvá; může dlouhodobě přinášet oblačnost a srážky.'
],
'figure' => 'front-symbols',
'table' => [
'headers' => [
'Fronta',
'Symbol na synoptické mapě',
'Poloha symbolů'
],
'rows' => [
[
'Studená',
'modrá čára s trojúhelníky',
've směru postupu'
],
[
'Teplá',
'červená čára s půlkruhy',
've směru postupu'
],
[
'Okluzní',
'fialová čára s trojúhelníky a půlkruhy',
'na stejné straně'
],
[
'Stacionární',
'střídavě červené a modré symboly',
'na opačných stranách'
]
]
]
],
[
'heading' => 'Přechod typické polární frontální níže',
'paras' => [
'Modelová níže středních šířek postupuje přibližně s výškovým řídicím prouděním, v testové poučce ve směru izobar teplého sektoru. Při obvyklém průchodu jižně od středu níže na severní polokouli (typická situace pro střední Evropu) vítr při teplé i studené frontě často stáčí po směru hodinových ručiček (veering). Nejde o univerzální zákon: záleží na dráze středu níže a lokální topografii.'
],
'table' => [
'headers' => [
'Fáze',
'Tlak a vítr',
'Typické počasí'
],
'rows' => [
[
'Před teplou frontou',
'tlak klesá, často JV až J',
'Ci/Cs → As → Ns, zhoršování'
],
[
'Teplá fronta',
'pokles tlaku se zmírní, vítr se může stočit',
'souvislé srážky, stoupající T'
],
[
'Teplý sektor',
'tlak často přibližně stálý nebo pomalu klesá',
'vlhčí vzduch, Sc/St; v létě může být dobrá dohlednost'
],
[
'Studená fronta',
'minimum či krátký pokles, poté růst, často Z až SZ',
'přeháňky, případně bouřky, změna větru'
],
[
'Za studenou frontou',
'tlak roste',
'lepší dohlednost mimo přeháňky, Cu'
]
]
],
'example' => [
'title' => 'Plán letu s návratem za čtyři hodiny',
'text' => 'Při letu před teplou frontou může být odlet za VMC, ale návrat již při nízké základně a dešti. Nestačí počasí při odletu: vyhodnoť trend a čas postupu, záložní letiště i možnost změnit nebo zrušit let.'
]
]
],
'checks' => [
[
'question' => 'Jak poznáš na mapě studenou frontu?',
'answer' => 'Modrá čára s trojúhelníky ve směru postupu.'
],
[
'question' => 'Proč se před teplou frontou základna oblačnosti obvykle snižuje?',
'answer' => 'Pozvolný výstup vlhkého teplého vzduchu nad chladnou hmotu vede k postupnému houstnutí a snižování vrstev oblaků.'
],
[
'question' => 'Co znamená okluze?',
'answer' => 'Studená fronta dostihla teplou a teplý sektor byl v okludované části vytlačen vzhůru.'
]
]
];
