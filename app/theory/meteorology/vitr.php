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
'title' => 'Vítr a místní cirkulace',
'lead' => 'Směr a rychlost větru jsou výsledkem tlakových rozdílů, rotace Země, tření a terénu. V letectví rozhodují také o driftu, délce vzletu a turbulenci.',
'questions' => [
26,
27,
28,
29,
30,
31,
32,
33,
36,
37,
38,
90,
92,
123,
124,
125,
126,
129
],
'sources' => [
'nws-pressure',
'metoffice-pressure',
'faa-weather'
],
'sections' => [
[
'heading' => 'Proč vzduch proudí',
'paras' => [
'Rozdílné ohřívání zemského povrchu a rozložení hmotnosti vzduchového sloupce vytváří horizontální rozdíly tlaku. Síla tlakového gradientu působí od vyššího k nižšímu tlaku, kolmo k izobarám (čarám stejného tlaku). Hustě rozmístěné izobary znamenají při stejném měřítku mapy a intervalu větší tlakový gradient, tedy zpravidla silnější vítr; široké rozestupy naznačují slabší proudění. Skutečný vítr ovlivňují i zeměpisná šířka, tření, zakřivení izobar a místní podmínky.',
'Coriolisovo zrychlení způsobené rotací Země působí na severní polokouli vpravo od směru pohybu, na jižní vlevo. Je nulové na rovníku a roste s rychlostí proudění a zeměpisnou šířkou. Nevyvolává vítr z klidu, mění směr již pohybujícího se vzduchu. Daleko od země a při téměř přímých izobarách může nastat rovnováha tlakového gradientu a Coriolisovy síly: geostrofický vítr teče přibližně rovnoběžně s izobarámi. Na severní polokouli má při tomto směru nižší tlak vlevo.'
],
'figure' => 'wind'
],
[
'heading' => 'Gradientový a přízemní vítr',
'paras' => [
'Při zakřiveném proudění kolem tlakových útvarů je třeba zohlednit dostředivé zrychlení; mluvíme o gradientovém větru. V třecí (planetární mezní) vrstvě tření snižuje rychlost větru a tím i Coriolisův účinek. Výsledný vítr má složku k nižšímu tlaku, protíná izobary. Nad hladkou vodou je tření obvykle menší než nad lesem, zástavbou nebo členitým pohořím. Výška třecí vrstvy se mění podle denního cyklu, stability a drsnosti povrchu.',
'Na severní polokouli v přízemní cykloně proudí vzduch proti směru hodinových ručiček a částečně dovnitř; v anticykloně po směru hodinových ručiček a ven. Konvergence znamená sbíhání proudění, divergence rozbíhání. V níži může přízemní konvergence podporovat výstup a vznik mraků; v tlakové výši sestup a divergence oblačnost obvykle potlačují. Ve výšce je vztah k vertikálnímu pohybu odlišný: divergence ve výšce může naopak podporovat výstup z nižších vrstev.'
],
'figure' => 'pressure'
],
[
'heading' => 'Měření směru a letecké použití',
'paras' => [
'Meteorologický směr větru udává směr, odkud vítr vane: 090° je východní, 180° jižní, 270° západní. V METAR/TAF a předpovědích je směr zpravidla vztažen ke skutečnému severu. Hlášení pro bezprostřední provoz na dráze může používat magnetický směr podle místních postupů. Při výpočtu složek větru sjednoť referenci dráhy a větru; runway designátor je navíc zaokrouhlený magnetický směr, ne přesný azimut.',
'Vítr ve výšce se používá pro výpočet korekce kurzu a ground speed, zatímco přízemní vítr rozhoduje o volbě dráhy a složkách větru pro vzlet a přistání. Nárazy a proměnlivost směru mohou být důležitější než samotná střední rychlost. Zápis 225/15 znamená vítr z jihozápadu 15 kt, nikoli směr, kam vane. 1 kt = 1 NM/h ≈ 0,514 m/s.'
],
'formula' => 'Čelní složka = V × cos Δ    |    boční složka = V × sin Δ',
'example' => [
'title' => 'Dráha 270°, vítr 240°/16 kt (stejná reference)',
'text' => 'Úhel 30°. Čelní složka 16 × cos 30° ≈ 13,9 kt, boční 16 × sin 30° = 8 kt zleva. Při nárazech 25 kt by boční složka při stejném směru dosáhla 12,5 kt. Porovnej s limity a osobní rezervou.'
]
],
[
'heading' => 'Místní větrné systémy',
'paras' => [
'Při slabém synoptickém proudění převládnou místní teplotní rozdíly. Za dne se pevnina ohřívá rychleji než voda, vzniká mořská bríza (při pobřeží od moře k pevnině) a ve výšce návratové proudění. V noci se může vytvořit opačná pevninská bríza. Při velkém gradientovém větru se tyto efekty mohou zesílit, zeslabit nebo zaniknout.',
'Na osluněném svahu vzniká anabatický vítr směrem vzhůru. V noci ochlazený hustý vzduch stéká po svahu dolů jako katabatický vítr. V údolí se za dne často rozvine proudění proti směru klesání údolí (údolní vítr), v noci opačný horský vítr; svahový a údolní vítr nejsou přesně stejný mechanismus. Sestupný fén za hřebenem je odlišný od čistě nočního radiačního katabatického větru, ačkoliv otázka 38 užívá širší označení pro proudění ze svahu.'
],
'figure' => 'breeze',
'note' => 'Za slabého synoptického větru se směr na letišti v údolí může během dne obrátit, i když vyšší hladiny mají stále obdobný vítr. Při předletové přípravě zkoumej obě vrstvy.'
]
],
'checks' => [
[
'question' => 'Kterým směrem působí síla tlakového gradientu?',
'answer' => 'Od vysokého k nízkému tlaku, kolmo k izobarám.'
],
[
'question' => 'Proč vítr u země protíná izobary?',
'answer' => 'Tření zpomalí proudění a zeslabí Coriolisův účinek; vítr má složku k nižšímu tlaku.'
],
[
'question' => 'Vítr 225/15: odkud vane?',
'answer' => 'Z jihozápadu, rychlostí 15 kt.'
]
]
];
