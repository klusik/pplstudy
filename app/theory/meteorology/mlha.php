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
'title' => 'Mlha, dohlednost a nízká oblačnost',
'lead' => 'Pro VFR je dohlednost a výška základny často limitující dříve než vítr. Rozlišuj mechanismus vzniku a možnost, že se jev během letu zhorší.',
'questions' => [
2,
44,
45,
46,
47,
53,
60,
61,
62,
63,
91,
110
],
'sources' => [
'nws-fog',
'nws-fog-rules',
'chmi-briefing',
'wmo-levels'
],
'sections' => [
[
'heading' => 'Dohlednost není totéž co základna',
'paras' => [
'Meteorologická dohlednost popisuje rozpoznatelnost vzdálených objektů v atmosféře; v letištních zprávách se běžně používá převládající dohlednost. Letová dohlednost je to, co pilot vidí z kokpitu ve směru letu. RVR (Runway Visual Range) je dráhová dohlednost odvozená pro podmínky na konkrétní dráze a nelze ji zaměnit s běžnou vodorovnou dohledností.',
'Mlha FG je tvořena vodními kapkami nebo ledovými částicemi u země, které snižují dohlednost typicky pod 1 km. Kouřmo BR je zvlhčená dohlednost v rozsahu zpravidla 1 až 5 km. Zákal HZ je převážně rozptyl světla na suchých jemných částicích; není tak přímo závislý na dosažení rosného bodu jako mlha. Rozlišuj také kouř FU, prach DU a písek SA. Kód MIFG (mělká mlha), BCFG (chuchvalce mlhy), PRFG (částečná mlha) může upozornit na místní významné omezení, i když převládající dohlednost v METAR vypadá přijatelně.'
],
'table' => [
'headers' => [
'Kód',
'Česky',
'Příčina'
],
'rows' => [
[
'FG',
'mlha',
'nasycení, kapičky/led'
],
[
'BR',
'kouřmo',
'vlhký aerosol a kapičky'
],
[
'HZ',
'zákal',
'jemné částice, často suchý vzduch'
],
[
'FZFG',
'namrzající mlha',
'přechlazené kapky'
],
[
'FU',
'kouř',
'produkty hoření'
],
[
'RVR',
'dráhová dohlednost',
'vlastnost konkrétní dráhy'
]
]
]
],
[
'heading' => 'Radiační mlha',
'paras' => [
'Po západu Slunce země ztrácí dlouhovlnným zářením energii. Vzduch v kontaktu se zemí se ochladí, často vzniká přízemní inverze. Vlhkost z mokré půdy a slabý vítr pomáhají vytvářet vrstvu nasyceného vzduchu. Úplné bezvětří může dát jen mělkou vrstvu nebo rosu; silnější promíchávání může zabránit nasycení. Oblačná pokrývka omezuje noční radiační ztrátu, proto často brání tomuto mechanismu.',
'Po východu Slunce se mělká mlha může rozpouštět, ale v zimě při slabém insolování a silné inverzi může přetrvávat nebo přejít v nízký St. V údolích se studený vzduch hromadí. Výše položené letiště může být na slunci, zatímco návrat do údolí nebude bezpečný.'
],
'figure' => 'fog',
'example' => [
'title' => 'Večerní pozorování',
'text' => 'T = 7 °C, Td = 6 °C, jasná obloha, slabý vítr a mokrá půda. Spread 1 °C s očekávaným nočním ochlazením je významný varovný signál. Není to výpočet času vzniku mlhy; k tomu je třeba místní předpověď a trend.'
]
],
[
'heading' => 'Advekční, orografická a další mlhy',
'paras' => [
'Advekční mlha vzniká přesunem vlhkého vzduchu nad chladnější povrch (např. moře nebo studená zasněžená pevnina). Vzduch je ochlazován při horizontálním pohybu; na rozdíl od radiační mlhy může přetrvat i během dne a vyžaduje vhodné proudění. Orografická mlha vzniká, když vlhký vzduch vystupuje po svahu a ochlazuje se expanzí až k nasycení. Mrak zakrývající hřeben je z hlediska pilota mlha na terénu.',
'Frontální/srážková mlha se může vytvořit odpařováním srážek do chladné vrstvy. Vypařovací mlha (steam fog) vzniká například při proudění studeného vzduchu nad relativně teplou vodou; vodní pára z hladiny přispívá k nasycení. Namrzající mlha při teplotách pod bodem mrazu obsahuje přechlazenou kapalinu; na povrchu letadla může vytvářet led.'
],
'table' => [
'headers' => [
'Typ',
'Rozpoznávací situace',
'Typický vývoj'
],
'rows' => [
[
'Radiační',
'jasná vlhká noc, slabý vítr',
'po ohřevu se může rozpustit'
],
[
'Advekční',
'vlhký teplejší vzduch nad studeným povrchem',
'může trvat celý den'
],
[
'Orografická',
'vlhký vítr proti svahu',
'závisí na směru proudění a zdvihu'
],
[
'Frontální',
'srážky do chladné vrstvy',
'souvisí s frontou'
],
[
'Vypařovací',
'studený vzduch nad teplou vodou',
'přísun páry a promíchávání'
]
]
]
],
[
'heading' => 'VFR rozhodnutí při zhoršování dohlednosti',
'paras' => [
'Před letem posuď pozorování i předpovědi v době odletu, návratu a na alternativách. Porovnávej časové tendence METAR/SPECI, TAF, regionální předpověď, webkamery a topografii; letištní měření není reprezentativní pro každé údolí. Podmínky VMC závisejí na prostoru, výšce, denní/noční době a příslušných pravidlech. Vlastní minima mohou být přísnější, zvláště při výcviku a v neznámém terénu.',
'Pokud se základna snižuje a ztrácíš vizuální kontakt s terénem či bezpečnou rezervu, rozhodnutí o návratu nebo odklonu musí přijít dřív, než se uzavře možnost bezpečně pokračovat. Nespoléhej na to, že se dohlednost o pár kilometrů dále zlepší, protože předpověď to říká. Při neúmyslném vstupu do IMC použij nacvičené postupy, přístroje a pomoc ATS; praktický postup patří do výcviku.'
],
'note' => 'Pěkný METAR na jednom letišti není povolení k celému VFR letu. Nejdůležitější je souvislá bezpečná trasa v čase a prostoru.'
]
],
'checks' => [
[
'question' => 'Jaký typ mlhy očekáváš nad studeným povrchem při přílivu teplého vlhkého vzduchu?',
'answer' => 'Advekční.'
],
[
'question' => 'Jaký účinek může mít zataženo během noci na vznik radiační mlhy?',
'answer' => 'Omezuje vyzařování a ochlazení země, může tvorbě zabránit.'
],
[
'question' => 'Je HZ stejně citlivý na teplotu jako mlha FG?',
'answer' => 'Ne, převážně suchý aerosol může existovat i při velkém spreadu.'
]
]
];
