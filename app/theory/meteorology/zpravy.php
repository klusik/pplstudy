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
'title' => 'METAR, TAF a letecké meteorologické informace',
'lead' => 'Nauč se zprávy nejen dekódovat, ale také pochopit, k jakému místu, času, výšce a druhu údaje se vztahují.',
'questions' => [
10,
17,
23,
24,
26,
70,
71,
72,
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
126
],
'sources' => [
'metar-guide',
'taf-guide',
'chmi-aviation',
'chmi-swl',
'chmi-swl-legend',
'rlp-units',
'chmi-taf',
'chmi-metar',
'chmi-sigmet',
'rlp-vfr',
'rlp-aim'
],
'memory' => [
[
'label' => 'METAR/TAF: dohlednost',
'value' => '9999 = ≥ 10 000 m (≈ 32 808 ft)',
'note' => '0000 = méně než 50 m (≈ 164 ft). Skupina je v metrech; kód se nemění.',
'overview' => true
],
[
'label' => 'METAR/TAF: základna',
'value' => '100 ft = 30,48 m',
'note' => 'BKN032 = 3 200 ft ≈ 975 m nad letištěm; BKN015 = 1 500 ft ≈ 457 m.',
'overview' => true
],
[
'label' => 'CAVOK',
'value' => '≥ 10 km (≈ 32 808 ft) · žádný oblak pod 1 524 m / 5 000 ft nebo vyšší MSA',
'note' => 'Současně bez CB/TCU a význačného počasí. Je to letištní údaj, nikoliv potvrzení bezpečné trasy.',
'overview' => true
],
[
'label' => 'QNH a teploty',
'value' => 'Q1008 = 1008 hPa · M03/M05 = −3/−5 °C',
'note' => 'hPa a °C jsou primární. inHg patří k doplňkovému výkladu zahraničních zpráv.',
'overview' => false
],
[
'label' => 'Časové skupiny TAF',
'value' => 'FM · BECMG · TEMPO · PROB30/40',
'note' => 'Rozliš začátek, interval přechodu a dočasnou či pravděpodobnostní změnu. Čas UTC.',
'overview' => false
]
],
'sections' => [
[
'heading' => 'Druhy meteorologických informací',
'figure' => 'weather-info-types',
'paras' => [
'Pozorování popisuje naměřené nebo pozorované počasí v daném místě a čase. Analýza skládá více pozorování do mapy. Předpověď vyjadřuje očekávaný budoucí stav, ne jistotu. Výstražné informace upozorňují na konkrétní nebezpečné jevy. V ČR je hlavním poskytovatelem leteckých meteorologických produktů ČHMÚ; ŘLP ČR zajišťuje přístup k leteckým informacím a integrovanému briefingu. Využívej platné AIP a VFR příručku, ne jen jednu aplikaci.',
'Čas meteorologických produktů je běžně UTC (Z). V ČR je místní čas v zimě UTC+1 a v létě UTC+2. Některé webové radarové či družicové produkty mohou uvádět občanský čas. Vždy kontroluj datum a jednotky v legendě. Nezaměňuj datum vydání s časem platnosti.'
],
'table' => [
'headers' => [
'Produkt',
'Typ informace',
'Na co se používá'
],
'rows' => [
[
'METAR / SPECI',
'aktuální letištní pozorování / mimořádná zpráva',
'vítr, dohlednost, jevy, oblačnost, T/Td, QNH'
],
[
'TAF',
'předpověď pro konkrétní letiště',
'odlet, ETA, návrat a alternativy'
],
[
'TREND',
'krátkodobá předpověď připojená k pozorování',
'typicky následující dvě hodiny'
],
[
'SIGMET',
'výstražná informace pro FIR/UIR',
'význačné nebezpečné jevy na trati'
],
[
'AIRMET',
'informace pro lety v nízkých hladinách, kde se vydává',
'např. neočekávané nebezpečné jevy mimo SIGMET'
],
[
'GAMET / oblastní předpověď',
'předpověď pro nízké hladiny oblasti podle regionálního systému',
'podmínky na trati mezi letišti'
],
[
'SWL / LLSWC',
'mapa význačného počasí v nízkých hladinách',
'oblačnost, dohlednost, nulová izoterma, námraza, turbulence, fronty'
],
[
'ATIS',
'letištní informační vysílání',
'počasí i provozní údaje včetně dráhy a případně převodní hladiny'
],
[
'VOLMET',
'vysílání meteorologických informací',
'aktualizace počasí za letu'
]
]
]
],
[
'heading' => 'METAR: anatomie zprávy',
'figure' => 'metar-anatomy',
'paras' => [
'METAR (Meteorological Aerodrome Report) je rutinní letištní zpráva. SPECI je mimořádné pozorování, které se vydává při příslušných změnách podmínek podle provozních pravidel. Každý údaj má přesné jednotky. V českém METAR/TAF je dohlednost v metrech, vítr typicky v uzlech, teplota v °C, QNH v hPa a základna oblačnosti ve stovkách stop (100 ft = 30,48 m). V USA se například používají statute miles a inHg; inHg zde uvádíme jen doplňkově. Vždy čti původní jednotky kódu a neměň je při předávání zprávy.'
],
'code' => 'METAR LKPR 241230Z 24012G22KT 210V280 7000 -SHRA SCT018 BKN032 09/06 Q1008 NOSIG=',
'table' => [
'headers' => [
'Skupina',
'Dekódování příkladu'
],
'rows' => [
[
'LKPR',
'Praha/Ruzyně, ICAO identifikátor'
],
[
'241230Z',
'24. den v měsíci, 12:30 UTC'
],
[
'24012G22KT',
'vítr z 240° 12 kt, nárazy 22 kt'
],
[
'210V280',
'směr kolísal mezi 210° a 280°'
],
[
'7000',
'převládající dohlednost 7 000 m (≈ 22 966 ft)'
],
[
'-SHRA',
'slabé dešťové přeháňky'
],
[
'SCT018 BKN032',
'rozptýlené oblaky v 549 m / 1 800 ft, oblačno v 975 m / 3 200 ft nad letištěm'
],
[
'09/06',
'teplota +9 °C, rosný bod +6 °C'
],
[
'Q1008',
'QNH 1008 hPa'
],
[
'NOSIG',
'neočekává se význačná změna v období trendu, nikoli záruka neměnného počasí'
]
]
],
'note' => 'Všechny zde uvedené zprávy jsou autorské didaktické příklady, nikoli skutečná aktuální data z letišť. Nepoužívej je k letu.'
],
[
'heading' => 'Kódy dohlednosti, jevů a oblačnosti',
'figure' => 'metar-units',
'paras' => [
'Zápis 9999 znamená převládající dohlednost nejméně 10 000 m (≈ 32 808 ft). Hodnota 0000 znamená méně než 50 m (≈ 164 ft). CAVOK (Ceiling And Visibility OK) se používá při dohlednosti nejméně 10 km (≈ 32 808 ft), bez provozně význačné oblačnosti (zejména žádný oblak pod 1 524 m / 5 000 ft nebo nejvyšší minimální sektorovou výškou (MSA, Minimum Sector Altitude), je-li vyšší, a žádný CB/TCU) a bez význačného počasí. CAVOK neznamená bezoblačnou oblohu, nulový vítr ani automaticky VFR na celé trase.',
'Prefix − označuje slabý jev, + silný; nepřítomnost prefixu zpravidla mírnou intenzitu, pokud je intenzita pro jev použitelná. SH je přeháňka, TS bouřka, FZ namrzající, VC v blízkém okolí. Před teplotou M znamená minus; například M03/M05 je −3/−5 °C. Vítr 00000KT znamená bezvětří, VRB03KT proměnlivý směr 3 kt. AUTO upozorňuje na automatické měření a COR na opravenou zprávu. NSC označuje absenci význačné oblačnosti; NCD je u automatického pozorování stav, kdy nebyla detekována oblačnost.'
],
'table' => [
'headers' => [
'Zápis',
'Význam'
],
'rows' => [
[
'+RA',
'silný déšť'
],
[
'SHRA',
'mírné dešťové přeháňky'
],
[
'TSRA',
'bouřka s deštěm'
],
[
'FZRA',
'namrzající déšť'
],
[
'VCTS',
'bouřka v blízkosti letiště'
],
[
'BKN015CB',
'5–7 osmin Cb se základnou 457 m / 1 500 ft nad letištěm'
],
[
'R24/0800U',
'RVR na dráze 24 je 800 m (≈ 2 625 ft), rostoucí tendence'
],
[
'VV002',
'vertikální dohlednost 61 m / 200 ft při zakrytém nebi'
],
[
'WS ALL RWY',
'hlášený střih větru pro všechny dráhy'
]
]
]
],
[
'heading' => 'TAF: vývoj podmínek v čase',
'figure' => 'taf-timeline',
'paras' => [
'TAF (Terminal Aerodrome Forecast) je letištní předpověď, nikoli předpověď celého FIR (Flight Information Region, letové informační oblasti). První časová skupina je vydání, následuje období platnosti den/hodina. Základní skupina předpovídá převládající podmínky od začátku platnosti. FM mění převládající podmínky od určeného času. BECMG určuje postupný přechod v daném časovém okně; nejpozději na jeho konci platí nový stav. TEMPO představuje dočasná kolísání (jednotlivě zpravidla méně než hodinu a celkem méně než polovinu uvedené doby). PROB30/40 udává pravděpodobnost jevu, někdy v kombinaci s TEMPO. TAF AMD nahrazuje předchozí předpověď upravenou verzí; TAF COR opravuje chybu.',
'Při čtení sestav časovou osu pro plánovaný přílet i rezervu. Skupiny nezrušené změnou mohou dále platit podle struktury TAF. NSW znamená, že se neočekává význačné počasí; nemusí znamenat jasnou oblohu. Zápis 2424 označuje konec 24. dne, tj. půlnoc na 25. den.'
],
'code' => 'TAF LKPR 241100Z 2412/2512 24010KT 9999 SCT025
TEMPO 2413/2417 4000 SHRA BKN015
BECMG 2418/2420 18006KT 3000 BR BKN006
PROB30 TEMPO 2422/2505 0800 FG VV002
FM250800 21008KT 9999 SCT020=',
'table' => [
'headers' => [
'Čas UTC',
'Očekávaný vývoj v příkladu'
],
'rows' => [
[
'24. 12:00–13:00',
'převládá 240°/10 kt, ≥10 km (≈ 32 808 ft), SCT 762 m / 2 500 ft'
],
[
'24. 13:00–17:00',
'dočasně 4 000 m (≈ 13 123 ft), přeháňky a BKN 457 m / 1 500 ft'
],
[
'24. 18:00–20:00',
'přechod na 180°/6 kt, 3 000 m (≈ 9 843 ft), kouřmo, BKN 183 m / 600 ft'
],
[
'24. 22:00–25. 05:00',
'30% pravděpodobnost dočasné mlhy, 800 m (≈ 2 625 ft), VV 61 m / 200 ft'
],
[
'25. od 08:00',
'nový převládající stav: 210°/8 kt, ≥10 km (≈ 32 808 ft), SCT 610 m / 2 000 ft'
]
]
],
'example' => [
'title' => 'Přílet 24. dne v 19:00 UTC',
'text' => 'Jsi uvnitř BECMG 18–20, tedy už může panovat zhoršený stav. Pro rozhodnutí nelze předpokládat jen lepší podmínky z počátku TAF.'
]
],
[
'heading' => 'SIGMET, AIRMET, oblastní předpovědi a ATIS',
'figure' => 'sigmet-volume',
'paras' => [
'SIGMET (Significant Meteorological Information, informace o význačných nebezpečných jevech na trati) vymezuje jev, oblast (FIR/UIR, horní letová informační oblast, nebo její část), čas platnosti, vertikální rozsah a případně pohyb a intenzitu. Může se týkat například význačných bouřek, silné turbulence, silné námrazy, horských vln nebo vulkanického popela. Neznamená, že ostatní území je zaručeně bezpečné. Sleduj vydání, změny i rušení informace. AIRMET (výstražná informace pro provoz v nízkých hladinách) a oblastní předpovědi doplňují nízkoletové informace podle regionálních postupů; konkrétní dostupnost a formát ověř v aktuálním AIP.',
'ATIS (Automatic Terminal Information Service, automatická informační služba) kombinuje meteorologické údaje a informace o provozu letiště: například dráhu v používání, druh přiblížení, převodní hladinu nebo důležité provozní podmínky. Má identifikační písmeno. VOLMET (vysílání meteorologických informací pro letadla za letu) nebo příslušné ATS/FIS (Flight Information Service, letová informační služba) poskytují meteorologické informace za letu. Pro FIR Praha používej aktuální zveřejněné kmitočty a služby. Běžný METAR sám o sobě neoznamuje, která dráha je právě v provozu.'
],
'note' => 'Rozsah výstrahy je třeba interpretovat ve třech rozměrech. Pokud SIGMET neplatí v plánované hladině, stejně prověř vertikální proudění, pohyb oblasti a bezpečnou rezervu.'
],
[
'heading' => 'Mapy a dálkový průzkum',
'paras' => [
'Synoptická mapa obsahuje izobary (tlak redukovaný na MSL), středy tlakových útvarů a frontální analýzu. Sleduj rozdíl tlaku, polohu front a jejich předpokládaný pohyb. Výškové mapy ukazují například vítr, teplotu a geopotenciální výšky. ČHMÚ publikuje SWL (Low-Level Significant Weather Chart, mapa význačného počasí) pro nízké hladiny mezi zemí a FL100 (10 000 ft ≈ 3 048 m tlakové výšky); v legendě uvádí základny a vrcholy oblačnosti, nulovou izotermu, nebezpečné jevy, dohlednost, fronty i tlakové útvary. Česká SWL podle dokumentace ČHMÚ používá nadmořskou výšku ve stovkách stop (hft AMSL), vítr v kt a dohlednost v km. To je zásadní rozdíl proti základně v METAR (výška nad letištěm). Například hodnota 030 na SWL znamená 3 000 ft AMSL (≈ 914 m), ne 3 000 ft nad terénem. SFC označuje zemský povrch, XXX výšku nad FL100. Vždy ověř aktuální legendu.',
'Radarový obraz je odvozen z odrazu elektromagnetického signálu od částic. Zobrazuje oblasti srážek, nikoliv všechny oblaky a nikoli přímo námrazu či turbulenci. Stínění intenzivními srážkami, vzdálenost od radaru a výška svazku mohou skrývat nízké nebo vzdálené jevy. Družicové snímky ve viditelném spektru ukazují oblačnost ve dne, infračervené snímky poskytují informace o teplotě vyzařujících povrchů a oblačnosti i v noci. Vysoká studená oblačnost může být konvektivní i neškodný Ci, nízká mlha může být v některých produktech těžko rozpoznatelná.'
],
'figure' => 'products',
'table' => [
'headers' => [
'Chci zjistit',
'Primární nástroj',
'Co doplnit'
],
'rows' => [
[
'Tlakové útvary a fronty',
'synoptická mapa',
'časový vývoj, výškové mapy'
],
[
'Kde právě prší',
'radar',
'animace, radarové pokrytí'
],
[
'Rozsah oblačnosti a front',
'družice',
'METAR, radar, synoptika'
],
[
'Předpokládané podmínky do FL100 (10 000 ft ≈ 3 048 m tlakové výšky)',
'SWL a oblastní předpověď',
'SIGMET, TAF, sondáž'
],
[
'Přízemní vítr a ceiling na letišti',
'METAR / ATIS',
'TAF, místní data, čas'
]
]
]
]
],
'checks' => [
[
'question' => 'Co znamená +RA a SHRA?',
'answer' => 'Silný déšť; mírné dešťové přeháňky.'
],
[
'question' => 'Co zjistíš z ATIS, co normálně neobsahuje METAR?',
'answer' => 'Provozní údaje, například dráhu v používání a převodní hladinu.'
],
[
'question' => 'Jaká mapa ukazuje izobary při MSL a fronty?',
'answer' => 'Synoptická přízemní mapa.'
],
[
'question' => 'Je CAVOK důkazem, že mezi dvěma letišti nejsou nebezpečné jevy?',
'answer' => 'Ne, vztahuje se na konkrétní letištní situaci, nikoliv na celou trať.'
],
[
'question' => 'TAF s TEMPO BKN008 (244 m / 800 ft nad letištěm) v době příletu: lze jej ignorovat, protože jde jen o krátkou změnu?',
'answer' => 'Ne, dočasné zhoršení může zasáhnout přílet. Zohledni jej v plánu, alternativách a rozhodnutí.'
]
]
];
