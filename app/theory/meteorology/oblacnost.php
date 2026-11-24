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
'title' => 'Oblačnost, srážky a jejich rozpoznání',
'lead' => 'Druh oblačnosti je vizuální stopa k procesům v atmosféře. Nauč se rozlišit deset rodů, výšku jejich základen a meteorologické jevy, které mohou doprovázet.',
'questions' => [
1,
3,
54,
55,
56,
57,
58,
59,
64,
65,
66,
67,
73,
74,
83,
89,
93
],
'sources' => [
'wmo-genera',
'wmo-levels',
'wmo-classification',
'faa-weather'
],
'sections' => [
[
'heading' => 'Deset rodů oblaků',
'paras' => [
'Mezinárodní klasifikace WMO rozlišuje deset rodů. Názvy napovídají: cirro- označuje převážně vysokou oblačnost, alto- střední, stratus vrstvy, cumulus kupy a nimbus srážkový charakter. V mírném pásu se nízká hladina orientačně pohybuje od země do 2 km, střední 2–7 km, vysoká 5–13 km. Rozsahy se překrývají a mění se se zeměpisnou šířkou; nejde o pevné hranice. Cu a Cb často začínají nízko, ale rostou do vyšších hladin; Ns může zasahovat do více hladin.'
],
'figure' => 'clouds',
'table' => [
'headers' => [
'Rod (zkratka)',
'Vzhled a hladina',
'Co sledovat'
],
'rows' => [
[
'Cirrus (Ci)',
'tenká vláknitá vysoká oblačnost',
'ledové krystalky, možná předzvěst fronty'
],
[
'Cirrocumulus (Cc)',
'drobné vysoké chomáčky',
'malé pravidelné vlnky, bez výrazných stínů'
],
[
'Cirrostratus (Cs)',
'jemný vysoký závoj',
'halo kolem Slunce/Měsíce'
],
[
'Altocumulus (Ac)',
'střední kupy, pásy nebo čočky',
'Ac lenticularis značí vlnu, castellanus nestabilitu'
],
[
'Altostratus (As)',
'šedá souvislá střední vrstva',
'Slunce jako za matným sklem, může houstnout'
],
[
'Nimbostratus (Ns)',
'mohutná tmavá vrstevnatá oblačnost',
'dlouhotrvající déšť/sněžení, nízké cáry'
],
[
'Stratocumulus (Sc)',
'nízké valy nebo velké kupy ve vrstvě',
'může zakrývat obzor, místy mrholení'
],
[
'Stratus (St)',
'nízká jednotvárná vrstva',
'mlha nad terénem, mrholení'
],
[
'Cumulus (Cu)',
'oddělené kupy s plochou základnou',
'termika, při růstu přeháňky'
],
[
'Cumulonimbus (Cb)',
'mohutná věž, často kovadlina',
'bouřky, srážky, kroupy, silné proudy'
]
]
]
],
[
'heading' => 'Jak rozpoznat oblak za letu',
'paras' => [
'Ci je obvykle vláknitý a má průsvitné okraje. Cu má ostré oblé okraje v teplejší kapalné části a přibližně vodorovnou základnu. Ac lenticularis je hladká čočka, která může téměř stát na místě i při silném větru. Pozorování čočky na závětří svědčí o horské vlně, ne o bezpečném místě pro let. Pokud je oblak zakryt další vrstvou, jeho přesný druh může být ze země nebo z kokpitu nerozpoznatelný.',
'U Cb se horní část zpravidla vlákní a rozprostírá do kovadliny. TCU je výrazný věžovitý Cu, který může přerůst v Cb; již v této fázi očekávej nepříznivé proudění. Nezaměňuj hladkou lentikulární oblačnost s klidným vzduchem v jejím okolí.'
],
'note' => 'Ke zkouškovým obrazovým otázkám MET-001, MET-002, MET-004 a MET-005 patří existující studijní překresby v bance. Atlas WMO obsahuje fotografie a detailní klasifikaci; zdejší schémata jsou názorná, nikoliv meteorologická pozorování.'
],
[
'heading' => 'Pokrytí, základna a vrchol oblačnosti',
'paras' => [
'Pokrytí se udává v osminách (oktas). FEW a SCT mohou mít nízkou základnu, i když netvoří ceiling. Ceiling je nejnižší relevantní vrstva BKN nebo OVC, případně vertikální dohlednost při zakrytém nebi. Při plánování VFR však rozhodují i jednotlivé oblaky, požadovaná vzdálenost od nich, skutečná dohlednost za letu a odstup od terénu, ne jen ceiling. METAR uvádí základny v stovkách stop nad letištěm (AGL vzhledem k referenci letiště). Mapy mohou používat AMSL, FL nebo jiné označení, proto čti legendu.'
],
'table' => [
'headers' => [
'Kód',
'Pokrytí',
'Příklad'
],
'rows' => [
[
'FEW',
'1–2/8',
'FEW020 = málo oblaků, základna 2 000 ft'
],
[
'SCT',
'3–4/8',
'SCT025 = rozptýlená ve 2 500 ft'
],
[
'BKN',
'5–7/8',
'BKN015 = ceiling 1 500 ft, pokud není nižší BKN/OVC/VV'
],
[
'OVC',
'8/8',
'OVC008 = zataženo v 800 ft'
],
[
'VV',
'vertikální dohlednost',
'VV003 = 300 ft vertikální dohlednosti'
]
]
]
],
[
'heading' => 'Mechanismy srážek',
'paras' => [
'Kondenzace vytváří drobné kapky; aby z nich byly srážky, musí růst. V teplém oblaku se větší kapičky srážejí s menšími a slučují (koalescence). Ve smíšeném oblaku mohou ledové krystalky růst na úkor přechlazených kapek (Bergeronův–Findeisenův proces), spojovat se do vloček nebo namrzat při zachytávání kapek. Částice padají, pokud jejich pádová rychlost překoná stoupání vzduchu, a při průchodu různými teplotními vrstvami mohou tát či znovu mrznout.',
'Silné stoupavé proudy pomáhají růstu velkých kapek a krup; v otázkách 64 a 65 je tento mechanismus zdůrazněn. V přírodě ale vznikají i mrholení, déšť nebo sněžení ve vrstevnaté oblačnosti s poměrně slabým zdvihem. Ns typicky dává souvislé srážky; Cu congestus nebo Cb spíše krátké, proměnlivě intenzivní přeháňky. Virga jsou srážky, které se před dopadem odpaří nebo vysublimují; ochlazení vzduchu pod nimi může podporovat downdraft.'
],
'table' => [
'headers' => [
'Jev',
'METAR',
'Podstata'
],
'rows' => [
[
'Déšť',
'RA',
'kapalné kapky'
],
[
'Mrholení',
'DZ',
'velmi drobné kapky'
],
[
'Sníh',
'SN',
'ledové krystalky/vločky'
],
[
'Ledové krupky',
'PL',
'zmrzlé kapky nebo znovuzmrzlé částice'
],
[
'Kroupy',
'GR',
'větší ledové částice z konvekce'
],
[
'Malé kroupy / sněhové krupky',
'GS',
'menší ledové částice'
],
[
'Přeháňky deště',
'SHRA',
'konvektivní srážky'
],
[
'Namrzající déšť',
'FZRA',
'přechlazená kapalina, po dopadu mrzne'
]
]
]
]
],
'checks' => [
[
'question' => 'Který rod typicky doprovází dlouhotrvající souvislý déšť?',
'answer' => 'Nimbostratus (Ns).'
],
[
'question' => 'BKN012 při elevaci 1 500 ft: kolik přibližně AMSL činí základna?',
'answer' => '1 200 ft nad letištěm + 1 500 ft = 2 700 ft AMSL.'
],
[
'question' => 'Co může signalizovat virga pod vysokou konvektivní oblačností?',
'answer' => 'Odpařování srážek, ochlazení a možnost nebezpečných klesavých proudů.'
]
]
];
