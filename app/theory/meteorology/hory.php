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
'title' => 'Horské počasí, fén a turbulence',
'lead' => 'Hory mění proudění v horizontálním i vertikálním směru. Krásná obloha v závětří může být kombinována s proudem silnějším, než dokáže malé letadlo vystoupat.',
'questions' => [
1,
2,
36,
37,
38,
39,
40,
41,
42,
56,
93,
94,
95,
109,
124,
125,
128,
129
],
'sources' => [
'metoffice-foehn',
'bora-study',
'caa-mountain',
'nws-pressure',
'faa-weather'
],
'memory' => [
[
'label' => 'Fén: suchá a nasycená větev',
'value' => '≈ 1 / 0,6 °C na 100 m · ≈ 3 / 1,8 °C na 1 000 ft',
'note' => 'Při nasyceném výstupu se vzduch obvykle ochlazuje pomaleji než při suchém sestupu ohřívá.',
'overview' => false
],
[
'label' => 'Lenticularis a rotory',
'value' => 'Ac lenticularis = varování před horskou vlnou',
'note' => 'Hladký vzhled oblaku není důkazem bezpečných podmínek; závětří může obsahovat silnou turbulenci a sestupné proudy.',
'overview' => false
],
[
'label' => 'Stoupavost a klesavé proudy',
'value' => '5 m/s ≈ 984 ft/min',
'note' => 'Posuzuj, zda schopnost stoupání a úniková trasa stačí. Číslo je příklad převodu, nikoli hranice bezpečnosti.',
'overview' => false
]
],
'sections' => [
[
'heading' => 'Návětří, závětří a vynucený výstup',
'paras' => [
'Pokud vítr s dostatečnou složkou kolmo na hřeben narazí na terén, část proudění je nucena stoupat, část může být odkloněna kolem překážky nebo blokována. Na návětrné straně se stoupající vzduch ochlazuje; při nasycení vzniká oblačnost. Ve stabilní vrstvě se často rozvíjí St, As nebo Ns, případně orografická mlha a déšť. V nestabilní vlhké hmotě je možný výrazný vertikální růst a Cb. Oslunění svahu může zesílit stoupavé proudy, zastínění je naopak utlumit.',
'Za hřebenem může sestupující vzduch vytvářet značné klesání a mechanické víry. V údolích se vítr usměrňuje, zrychluje v zúženích a v různých výškách může mít velmi odlišný směr. Přízemní vítr z letiště v údolí nepopisuje vítr na hřebeni.'
],
'figure' => 'mountains'
],
[
'heading' => 'Horská vlna a rotor',
'paras' => [
'Vhodně stabilní vrstvený vzduch s dostatečným prouděním napříč hřebenem může po vychýlení oscilovat ve vertikálním směru. Vlny mohou pokračovat daleko za hřebenem. Na vrcholu výstupné části vlny se při dostatku vlhkosti tvoří čočkovité Ac lenticularis, které vůči terénu vypadají téměř stacionárně. V sestupné části se kapky mohou opět odpařovat. V suchém vzduchu může být vlna zcela bezoblačná.',
'Pod vlnou v nižších hladinách může vzniknout rotor, horizontálně orientované vířivé proudění s prudkými změnami směru a rychlosti. Rotorový oblak bývá nepravidelný, někdy se rozpadá a znovu vzniká. Turbulence v rotoru může být silná až nebezpečná, zatímco v některých výstupných částech vlny může být proudění hladké. Výstup a sestup ale mohou překonat schopnosti letadla.'
],
'figure' => 'mountain-wave',
'note' => 'Lentikulární oblak je upozornění na podmínky vhodné pro vlnu, nikoli bezpečná cesta. Nepoužívej pevnou vzdálenost od hřebene ani odhad z přízemního větru jako univerzální bezpečnostní kritérium.'
],
[
'heading' => 'Fén: proč je za horami tepleji',
'paras' => [
'Klasický učební příklad fénu začíná vlhkým vzduchem vystupujícím na návětří, který se nejprve ochlazuje suchou adiabatou, po nasycení pomaleji. Srážkami se část vody odstraní. Při sestupu na závětří se nenasycený vzduch ohřívá přibližně suchou adiabatou. Výsledkem může být teplejší a sušší vzduch v závětrném údolí. Fén může vzniknout i jinými mechanismy bez srážek, například sestupem vzduchu z vyšší vrstvy a turbulentním promícháním.',
'Při jižním fénu v Alpách může být na jižní straně oblačnost a srážky, na severní straně v Bavorsku teplo, nižší relativní vlhkost a výrazná turbulence. Na hřebeni se může tvořit oblačná fénová zeď, v závětří čočky a rotorové oblaky. Změna typu proudění může způsobit prudké nárazy nebo střih u údolních letišť.'
],
'example' => [
'title' => 'Zjednodušený termodynamický fén',
'text' => 'Vzduch při 15 °C stoupá prvních 1 000 m (≈ 3 281 ft) nenasycený při zaokrouhleném gradientu 1 °C/100 m (≈ 3 °C/1 000 ft) → asi 5 °C. Dalších 1 000 m (≈ 3 281 ft) nasycený při 0,6 °C/100 m (≈ 1,8 °C/1 000 ft) → −1 °C. Po ztrátě části kondenzátu sestupuje 2 000 m (≈ 6 562 ft) suchou adiabatou → asi +19 °C ve stejné výšce jako na návětří. Výsledek je ilustrační, skutečný fén závisí i na dynamice.'
]
],
[
'heading' => 'Mistrál, bora a místní nebezpečí',
'paras' => [
'Mistrál je studený, často suchý silný severní až severozápadní vítr typický pro jih Francie a údolí Rhôny; topografie jej může významně usměrnit a zesílit. Bora je studený silný nárazový vítr stékající z pevniny k Jadranu, typicky ze severovýchodního až východního sektoru, lokálně odlišný. Otázka ÚCL č. 95 používá severozápadní směr a očekává odpověď bora; pro praktickou geografii je typický severovýchodní původ důležitější než tato nepřesnost.',
'Před letem v horské oblasti ověř vítr v hladině hřebene i výše, stabilitu, oblačnost, dohlednost, polohu únikových tras, výkonnost a zkušenost. Pro rizikový let do údolí nestačí splnit minimum VMC. Výcvik létání v horách je samostatná praktická disciplína. Měj rezervu na nárazy a klesavé proudy; nespěchej s přeletem, jestliže předpověď, terén a schopnosti letadla nedávají bezpečnou variantu.'
],
'table' => [
'headers' => [
'Projev',
'Možný mechanismus',
'Co jej nevyvrací'
],
'rows' => [
[
'Nepravidelný rotorový oblak',
'vír na závětří',
'dobrá dohlednost'
],
[
'Hladká stacionární čočka',
'horská vlna',
'malý přízemní vítr'
],
[
'Náhlé klesání za hřebenem',
'sestupná větev nebo mechanická turbulence',
'silný motor na rovině'
],
[
'Odlišný směr větru v údolí',
'kanalizace proudění a střih',
'předpověď pro jinou hladinu'
]
]
]
]
],
'checks' => [
[
'question' => 'Je pro horskou vlnu typičtější stabilní, nebo absolutně nestabilní vzduch?',
'answer' => 'Stabilní vrstvení s vhodným příčným větrem umožňuje oscilaci.'
],
[
'question' => 'Jaký oblak indikuje vrchol horské vlny?',
'answer' => 'Ac lenticularis, pokud je vzduch dostatečně vlhký.'
],
[
'question' => 'Proč není při fénu bezoblačné závětří automaticky bezpečné?',
'answer' => 'Mohou zde být silné sestupné proudy, vlny, rotory a střih větru.'
]
]
];
