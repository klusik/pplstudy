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
'title' => 'Atmosféra, teplota a hustota',
'lead' => 'Nejdříve pochopíme, proč se s výškou mění tlak a teplota, co je standardní atmosféra a proč stejné letadlo za horkého dne létá jinak než v zimě.',
'questions' => [
4,
5,
6,
7,
8,
9,
10,
11,
12,
13,
14,
15,
18,
19,
20,
122,
127
],
'sources' => [
'easa-syllabus',
'faa-weather',
'nws-education',
'nws-gradient',
'nist-units'
],
'memory' => [
[
'label' => 'Standardní tlak ISA',
'value' => '1013,25 hPa',
'note' => 'Doplňkově 29,92 inHg; na evropských zprávách a při výkladu používáme přednostně hPa.',
'overview' => true
],
[
'label' => 'Standardní teplota a hustota',
'value' => '+15 °C · 1,225 kg/m³',
'note' => 'Hodnoty v MSL pro suchou ISA.',
'overview' => true
],
[
'label' => 'Pokles teploty v ISA',
'value' => '0,65 °C/100 m ≈ 2 °C/1 000 ft',
'note' => 'Přesný převod 1,9812 °C/1 000 ft; paměťová pomůcka 2. ISA není skutečný profil ani adiabata.',
'overview' => true
],
[
'label' => 'Tropopauza ISA',
'value' => '11 000 m ≈ 36 089 ft',
'note' => 'Zkouškově přibližně 11 km / 36 000 ft; teplota −56,5 °C.',
'overview' => true
],
[
'label' => 'Převod výšek',
'value' => '1 ft = 0,3048 m',
'note' => '1 m ≈ 3,28084 ft; 1 000 ft = 304,8 m, přibližně 305 m. 100 m ≈ 328 ft. Násobení třemi je jen velmi hrubý odhad.',
'overview' => true
]
],
'sections' => [
[
'heading' => 'Složení a vrstvy atmosféry',
'paras' => [
'Suchý vzduch je přibližně 78 % dusíku (N₂), 21 % kyslíku (O₂) a 1 % dalších plynů, zejména argonu a malého podílu CO₂. Vodní pára má proměnlivý podíl. Při stoupání zůstává v běžných letových hladinách podíl kyslíku přibližně stejný, ale klesá jeho parciální tlak, což je podstatné pro hypoxii.',
'Troposféra je spodní vrstva, v níž je nejvíce hmoty a téměř všechna vodní pára atmosféry. Probíhá v ní většina jevů důležitých pro pilota: konvekce, mlha, fronty, srážky a bouřky. Tropopauza je přechod ke stratosféře; její výška se mění se zeměpisnou šířkou, roční dobou a synoptickou situací. Je níže nad póly a výše v tropech. Ve stratosféře se teplota ve vyšších částech zpravidla zvyšuje vlivem absorpce UV záření ozonem. MSL (Mean Sea Level) znamená střední hladinu moře; AMSL je výška nad ní a AGL výška nad místním terénem. Zápis FL (Flight Level) vyjadřuje tlakovou výšku ve stovkách stop při STD 1013,25 hPa, podrobně v kapitole 2.'
],
'figure' => 'atmosphere'
],
[
'heading' => 'ISA: mezinárodní standardní atmosféra',
'figure' => 'isa-reference',
'paras' => [
'ISA (International Standard Atmosphere, mezinárodní standardní atmosféra) je referenční model, nikoliv předpověď. Vzduch se v ní chová podle stanoveného profilu. Teplotní gradient je změna teploty na jednotku výšky; kladnou velikostí poklesu zde rozumíme, o kolik stupňů se ochladí při výstupu. Umožňuje porovnávat výkonnost letadel a kalibrovat barometrické přístroje. Na střední hladině moře (MSL) má definované hodnoty. Do 11 km (11 000 m ≈ 36 089 ft) předpokládá lineární pokles teploty o 0,65 °C/100 m neboli 6,5 °C/km. To je 1,9812 °C/1 000 ft, pro rychlý odhad 2 °C/1 000 ft. Nad touto hranicí je v první části modelové stratosféry teplota konstantní. V reálné atmosféře může být profil úplně jiný.'
],
'table' => [
'headers' => [
'Veličina / hladina',
'Hodnota v ISA'
],
'rows' => [
[
'Tlak v MSL',
'1013,25 hPa (doplňkově 29,92 inHg)'
],
[
'Teplota v MSL',
'+15 °C (288,15 K)'
],
[
'Hustota v MSL',
'1,225 kg/m³'
],
[
'Vertikální teplotní gradient',
'0,65 °C/100 m = 6,5 °C/km ≈ 1,98 °C/1 000 ft'
],
[
'1 524 m / 5 000 ft',
'přibližně +5 °C'
],
[
'3 048 m / 10 000 ft',
'přibližně −5 °C'
],
[
'FL180 (tlaková výška 18 000 ft ≈ 5 486 m)',
'tlak přibližně 500 hPa'
],
[
'10 000 m ≈ 32 808 ft',
'−50 °C'
],
[
'Tropopauza ISA',
'11 000 m ≈ 36 089 ft, −56,5 °C'
]
]
],
'example' => [
'title' => 'Odchylka ISA ve výšce 1 524 m / 5 000 ft',
'text' => 'Metricky: 15 − 0,65 × 15,24 ≈ +5,1 °C. Ve stopách: 15 − 2 × 5 ≈ +5 °C. Změříš-li OAT (Outside Air Temperature, venkovní teplotu) +17 °C, jde o ISA +12 °C. Na této hodnotě závisí zejména hustotní výška a výkon.'
]
],
[
'heading' => 'Tlak, hustota a výkon',
'figure' => 'density-particles',
'paras' => [
'Tlak je síla na jednotku plochy; 1 hPa = 100 Pa. Výškoměr v hPa a výškoměr v metrech či stopách popisují stejné fyzikální podmínky; jednotka stupnice výšky je nezávislá na jednotce nastavení tlaku. S výškou ubývá hmotnost sloupce vzduchu nad místem, takže tlak klesá. Pokles není lineární: u země se tlak na jednotku výšky mění rychleji než vysoko, protože vzduch je hustší. Pro suchý vzduch platí stavová rovnice p = ρ R T, tedy ρ = p/(R T), kde R ≈ 287 J/(kg·K) a T musí být v kelvinech.',
'Při stejném tlaku má teplejší vzduch nižší hustotu; při stejné teplotě ji snižuje nižší tlak. Vlhký vzduch je při stejném tlaku a teplotě také o něco méně hustý než suchý. Pro stejnou IAS (Indicated Airspeed, indikovanou vzdušnou rychlost) je ve vzduchu o menší hustotě vyšší TAS (True Airspeed, skutečná rychlost vůči vzduchu), a při stejné hmotnosti a konfiguraci se obvykle prodlužuje vzlet a zhoršuje stoupání. U nepřeplňovaného pístového motoru klesá dostupný výkon; vrtule pracuje v řidším prostředí. Skutečná čísla se berou z AFM (Aircraft Flight Manual, letová příručka) nebo POH (Pilot’s Operating Handbook, provozní příručka pilota), ne z pouhé teplotní poučky. Výpočty tlakové a hustotní výšky následují v kapitole 2.'
],
'formula' => 'ρ [kg/m³] = (100 × p[hPa]) / (287,05 × T[K])',
'example' => [
'title' => 'Stejný tlak, jiná teplota',
'text' => 'Při 1013,25 hPa (101 325 Pa) a 288,15 K vychází 1,225 kg/m³. Při +35 °C (308,15 K) je to asi 1,146 kg/m³, tedy přibližně o 6,5 % méně. Neznamená to automaticky právě 6,5% prodloužení vzletu.'
]
],
[
'heading' => 'Skutečný teplotní profil a inverze',
'paras' => [
'Teplotní gradient okolí (ELR, Environmental Lapse Rate) určí měření nebo vertikální předpovědní profil. Normální pokles teploty s výškou není totéž co ISA ani adiabatický gradient pohybující se částice (změna teploty při rozpínání nebo stlačování bez výměny tepla s okolím, podrobně v kapitole 3). Při izotermii je teplota s výškou stejná; při inverzi roste. Obě situace tlumí běžnou konvekci.',
'Přízemní radiační inverze vzniká zejména za jasné noci, kdy zemský povrch vyzařuje teplo a ochlazuje vzduch těsně nad ním. Subsidenční inverze se může vytvořit ve výši přibližně 1 500 m (asi 5 000 ft) v tlakové výši: sestupující vzduch se stlačuje a ohřívá nad chladnější spodní vrstvou. Inverze může zadržet kouř, mlhu a nízkou oblačnost a současně omezit výšku kumulů. Hranice inverze bývá místem změny větru; samotná inverze však neznamená automaticky silný střih.'
],
'figure' => 'inversion',
'note' => 'Ve zkoušce se 11 000 m / 36 000 ft používá jako zaokrouhlení modelové tropopauzy. Nememoruj jej jako pevnou výšku skutečné tropopauzy.'
]
],
'checks' => [
[
'question' => 'Jaká je v ISA teplota v 8 000 ft (≈ 2 438 m)?',
'answer' => 'Metricky: 15 − 0,65 × 24,384 ≈ −0,85 °C. Ve stopách: 15 − 2 × 8 ≈ −1 °C.'
],
[
'question' => 'Proč v létě na vysokém letišti nestačí řídit se nadmořskou výškou?',
'answer' => 'Tlak a teplota ovlivňují hustotu, a tím výkonnost; rozhoduje skutečný výpočet podle AFM/POH.'
],
[
'question' => 'Jak se liší izotermie a inverze?',
'answer' => 'V izotermii je T konstantní; při inverzi T s výškou roste.'
]
]
];
