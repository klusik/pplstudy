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
'title' => 'Stabilita atmosféry a konvekce',
'lead' => 'Samotný oblak neprozradí všechno. Rozhodující je, zda se vychýlená částice vzduchu vrátí, nebo bude pokračovat ve výstupu.',
'questions' => [
1,
3,
11,
12,
14,
15,
41,
48,
49,
50,
51,
52,
59,
64,
65,
85,
87,
101,
102,
122,
129
],
'sources' => [
'nws-stability',
'nws-gradient',
'nws-cape',
'faa-weather'
],
'sections' => [
[
'heading' => 'Archimédův princip v atmosféře',
'paras' => [
'Představ si vzduchovou částici, kterou zdvihne termika, fronta nebo horský svah. Tlak v ní se přizpůsobuje okolí a její teplota se mění přibližně adiabaticky. Je-li ve stejné výšce teplejší než okolí (přesněji má-li menší hustotu, s ohledem i na vodní páru), působí na ni kladný vztlak. Je-li chladnější, má tendenci k návratu dolů. Stabilita popisuje reakci atmosféry na takové vychýlení, nikoli to, zda právě svítí slunce.',
'Označme Γ jako kladnou velikost poklesu teploty s výškou. Skutečný gradient okolí Γe porovnávej s gradientem nenasycené částice Γd ≈ 9,8 °C/km a nasycené Γm, pro zjednodušení 6 °C/km. Rovnost znamená neutrální stav pro příslušný typ částice.'
],
'figure' => 'stability',
'table' => [
'headers' => [
'Skutečný gradient okolí',
'Charakter',
'Typický důsledek'
],
'rows' => [
[
'Γe < Γm',
'absolutně stabilní',
'vychýlené částice chladnější, výstup tlumen'
],
[
'Γm < Γe < Γd',
'podmíněně nestabilní',
'nenasycený vzduch stabilní, nasycený může růst'
],
[
'Γe > Γd',
'absolutně nestabilní',
'výstup podporován i před nasycením'
],
[
'Γe = 0 nebo záporný',
'izotermie / inverze',
'výrazná stabilita'
]
]
]
],
[
'heading' => 'Konvekce, termika a vznik Cu',
'paras' => [
'Zemský povrch přijímá sluneční záření nerovnoměrně; suché pole, asfalt nebo osluněný svah se může ohřát více než les či voda. Vzduch nad ním se ohřívá, stává se lehčím a vytváří stoupavé termické proudy. Po dosažení LCL se objeví Cu. Náhradou za stoupající vzduch vznikají okolní klesavé proudy. Za sucha může být termika bezoblačná, tedy i za zdánlivě klidné modré oblohy.',
'Slabé Cu humilis s plochými základnami typicky ukazují omezený vertikální rozvoj. Cu mediocris a congestus (v meteorologických zprávách TCU) představují rostoucí konvekci. Jestliže částice získá dostatečnou energii a nepřekonatelnou inverzi nepotká, může se vyvinout Cb. Přerůstání konvektivní oblačnosti v testu označuje právě tento vertikální vývoj až k přeháňkám. Přes den se základna i aktivita mění, večer často slábnou.'
],
'example' => [
'title' => 'Dva teplotní profily',
'text' => 'Přízemí 20 °C. Po výstupu o 1 km má nenasycená částice přibližně 10 °C. Je-li okolí 15 °C, je částice chladnější a výstup tlumen. Pokud má okolí 8 °C, částice je teplejší a má kladný vztlak. Nasycená částice s gradientem 6 °C/km by měla 14 °C.'
]
],
[
'heading' => 'Podmíněná nestabilita, CAPE a CIN',
'paras' => [
'Při podmíněné nestabilitě se nenasycená částice sama neudrží ve stoupání, ale po dostatečném nuceném zdvihu a kondenzaci může být teplejší než okolí. LFC je hladina volné konvekce, EL hladina rovnováhy. CAPE (Convective Available Potential Energy, J/kg) představuje dostupnou energii vztlaku; CIN je energetická bariéra, kterou musí částice překonat. Velká CAPE bez spouštěče neznamená jistou bouřku. Nízká CAPE také nevylučuje nebezpečné organizované bouřky s dalšími mechanismy zdvihu.',
'Spouštěčem mohou být oslunění, konvergence větru, fronta, výšková brázda nebo překonávání pohoří. Přísun chladnějšího vzduchu ve vyšších hladinách zvyšuje teplotní rozdíl a často konvekci podporuje. Vlhká nestabilní hmota vystupující po návětrném svahu může vytvářet Cb a intenzivní srážky, případně skryté Cb v jiné oblačnosti. Stabilní vlhká hmota při stejném zdvihu spíše tvoří vrstevnatou oblačnost.'
],
'note' => 'Cu nebo dokonce bezoblačná termika může být turbulentní. Cb ani TCU není bezpečné prolétávat. Sleduj změnu tvaru a rozsahu oblačnosti, radar a výstrahy; růst může být rychlý.'
],
[
'heading' => 'Čtení vertikálního profilu',
'paras' => [
'Aerologická sondáž měří tlak, teplotu, vlhkost a vítr s výškou. ČHMÚ publikuje i modelové pseudosondáže. Srovnej křivky T a Td (blízkost = pravděpodobnější vlhká vrstva), změnu gradientu (inverzní víko) a profil větru. Teplota 0 °C vyznačuje izotermu, ale oblačnost a přechlazená voda určují skutečné riziko námrazy. Sondáž je prostorový a časový vzorek, ne záruka stejného profilu po celé trati.'
],
'example' => [
'title' => 'Ranní inverze',
'text' => 'Ráno 6 °C na letišti, 10 °C v 1 000 ft AGL, pak pokles. Inverze může držet mlhu v údolí, i když nad vrstvou svítí slunce. Očekávaný odpolední rozpad inverze může zároveň přinést růst Cu a turbulentnější vzduch.'
]
]
],
'checks' => [
[
'question' => 'Je okolní gradient 8 °C/km při nasycené adiabatě 6 °C/km absolutně nestabilní?',
'answer' => 'Ne. Je mezi nasyceným a suchým gradientem, tedy podmíněně nestabilní.'
],
[
'question' => 'Proč mohou za stabilního počasí vznikat mraky na návětrné straně hor?',
'answer' => 'Vynucený výstup může i stabilní vzduch nasytit; obvykle vznikne vrstevnatá oblačnost.'
],
[
'question' => 'Co může zastavit vertikální růst Cu?',
'answer' => 'Silná stabilní vrstva nebo inverze, vyčerpání přívodu teplého vlhkého vzduchu.'
]
]
];
