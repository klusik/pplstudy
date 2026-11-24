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
'title' => 'Voda v atmosféře, rosný bod a adiabatické děje',
'lead' => 'Když pochopíš, jak se vzduch sytí vodní párou a jak se chová při výstupu, pochopíš vznik oblačnosti, mlhy i bouřek.',
'questions' => [
43,
44,
45,
46,
47,
48,
49,
50,
51
],
'sources' => [
'nws-gradient',
'nws-stability',
'nws-fog',
'faa-weather'
],
'memory' => [
[
'label' => 'Suchá adiabata (DALR)',
'value' => '0,98 °C/100 m ≈ 3 °C/1 000 ft',
'note' => 'Zkouškově 1 °C/100 m; jde o nenasycenou částici, nikoli okolní atmosféru.',
'overview' => true
],
[
'label' => 'Nasycená adiabata (SALR)',
'value' => '≈ 0,6 °C/100 m ≈ 1,8 °C/1 000 ft',
'note' => 'Zkoušková hodnota. Skutečný gradient je proměnný; 0,4–0,7 °C/100 m odpovídá asi 1,2–2,1 °C/1 000 ft.',
'overview' => true
],
[
'label' => 'Rosný bod a spread',
'value' => 'T − Td',
'note' => 'Při ochlazování a stejném obsahu páry spread klesá, RH roste; T = Td při nasycení.',
'overview' => false
],
[
'label' => 'Odhad základny termických Cu',
'value' => '≈ 122 m/°C ≈ 400 ft/°C',
'note' => 'Výška nad zdrojovou vrstvou (AGL při přízemní vrstvě), nikoliv AMSL. Pro rychlý metrický odhad 120 m/°C.',
'overview' => true
]
],
'sections' => [
[
'heading' => 'Tři skupenství a změny skupenství',
'figure' => 'water-phase',
'paras' => [
'Voda se v atmosféře vyskytuje jako vodní pára (plyn, neviditelná), kapalné kapičky a pevné ledové částice. Oblak není vodní pára; je tvořen kapičkami, ledem nebo obojím. Přeměny skupenství přenášejí energii: při vypařování a tání se teplo spotřebovává, při kondenzaci a mrznutí uvolňuje. Sublimace znamená přechod z ledu přímo do páry, desublimace opačný přechod.',
'Latentní teplo kondenzace je zásadní pro konvekci. Když se stoupající nasycený vzduch ochlazuje a voda kondenzuje, uvolněné teplo částečně kompenzuje ochlazování expanzí. Proto se nasycený vzduch při výstupu zpravidla ochlazuje pomaleji než nenasycený.'
],
'table' => [
'headers' => [
'Přeměna',
'Směr',
'Energie'
],
'rows' => [
[
'Vypařování',
'kapalina → pára',
'spotřeba'
],
[
'Kondenzace',
'pára → kapalina',
'uvolnění'
],
[
'Tání',
'led → kapalina',
'spotřeba'
],
[
'Mrznutí',
'kapalina → led',
'uvolnění'
],
[
'Sublimace',
'led → pára',
'spotřeba'
],
[
'Desublimace',
'pára → led',
'uvolnění'
]
]
]
],
[
'heading' => 'Relativní vlhkost, rosný bod a deficit',
'paras' => [
'Relativní vlhkost RH (Relative Humidity) je procentní vyjádření poměru skutečného tlaku vodní páry e a tlaku nasycené vodní páry eₛ při dané teplotě. Teplejší vzduch má vyšší tlak nasycené páry. Nejde doslova o schopnost vzduchu něco ‚udržet‘ jako houba, ale o termodynamickou rovnováhu. Při ochlazování bez významné změny obsahu páry roste RH; při ohřívání klesá.',
'Rosný bod Td je teplota, na niž je třeba vzduch ochladit při přibližně konstantním tlaku a obsahu vodní páry, aby dosáhl nasycení. Rozdíl T − Td je deficit rosného bodu (dew-point spread). Malý spread znamená vyšší relativní vlhkost, ale sám neurčuje dohlednost ani vznik mlhy. Rosný bod při změně teploty za jinak stejných podmínek zůstává přibližně stálý; při výstupu nebo přísunu vlhkosti se však může měnit.'
],
'formula' => 'RH = 100 × e / eₛ(T) [%]    |    deficit = T − Td',
'figure' => 'humidity',
'example' => [
'title' => 'METAR 16/14 vs 24/14',
'text' => 'V letištním pozorování METAR (jeho úplné dekódování je v kapitole 13) jsou čísla T/Td uvedena v °C. V obou případech je Td 14 °C. Spread 2 °C v prvním případě znamená blízkost nasycení; spread 10 °C ve druhém výrazně nižší RH. Pokud se v noci první vzduch ochladí na 14 °C a další podmínky dovolí, může vzniknout mlha.'
]
],
[
'heading' => 'Adiabatické ochlazování a ohřívání',
'paras' => [
'Pohybující se vzduchová částice si při přibližně adiabatickém ději nevyměňuje teplo s okolím. Při výstupu do nižšího tlaku expanduje a koná práci, čímž klesá její teplota. Při sestupu je stlačována a ohřívá se. Nenasycená částice se ochlazuje asi o 0,98 °C/100 m (9,8 °C/km), tedy 2,99 °C/1 000 ft. Nasycená částice typicky o 0,4–0,7 °C/100 m (4–7 °C/km; přibližně 1,2–2,1 °C/1 000 ft), přičemž přesná hodnota závisí na tlaku, teplotě a množství kondenzující vody. Pro otázky ÚCL se používá přibližně 1 °C/100 m (3 °C/1 000 ft) a 0,6 °C/100 m (1,8 °C/1 000 ft).',
'Je zásadní nezaměňovat adiabatické gradienty částice s gradientem okolí a s ISA. Částice může mít v určité výšce úplně jinou teplotu než okolní vzduch; právě rozdíl určuje vztlak a stabilitu.'
],
'figure' => 'lapse',
'table' => [
'headers' => [
'Gradient',
'Orientační hodnota',
'Význam'
],
'rows' => [
[
'DALR (suchá adiabata)',
'0,98 °C/100 m ≈ 3 °C/1 000 ft (zkouška 1 °C/100 m)',
'Nenasycená vystupující částice'
],
[
'SALR/MALR (nasycená adiabata)',
'proměnný, pro zkoušku ≈ 0,6 °C/100 m ≈ 1,8 °C/1 000 ft',
'Nasycená vystupující částice'
],
[
'ISA',
'0,65 °C/100 m ≈ 2 °C/1 000 ft',
'Modelový profil okolí'
],
[
'ELR',
'závisí na měření',
'Skutečný profil okolí'
]
]
]
],
[
'heading' => 'Výška kondenzace a odhad základny',
'figure' => 'cloud-base-lcl',
'paras' => [
'Při suchém výstupu teplota klesá přibližně 0,98 °C/100 m (3 °C/1 000 ft) a rosný bod zhruba 0,16 °C/100 m (0,5 °C/1 000 ft); jejich rozdíl se zmenšuje. Hladina, kde se zvedaná částice poprvé nasytí, se označuje LCL (lifting condensation level). Rozdíl gradientů je přibližně 0,98 − 0,16 = 0,82 °C/100 m (3 − 0,5 = 2,5 °C/1 000 ft). Na zmenšení rozdílu T − Td o 1 °C tedy potřebujeme přibližně 100/0,82 = 122 m, respektive 1 000/2,5 = 400 ft výstupu. Proto pro dobře promíchanou přízemní vrstvu odhadujeme základnu termických kumulů pomocí 122 m / 400 ft na každý °C spreadu. Neplatí spolehlivě pro vrstevnaté oblaky, fronty, odlišné zdrojové vrstvy nebo orografii.'
],
'formula' => 'H AGL [m] ≈ (T − Td) × 122 m/°C
H AGL [ft] ≈ (T − Td) × 400 ft/°C',
'example' => [
'title' => 'T = 22 °C, Td = 12 °C',
'text' => 'Spread 10 °C: 10 × 122 ≈ 1 220 m (10 × 400 = 4 000 ft) AGL nad místem původu částice. Při elevaci 366 m / 1 200 ft by to bylo asi 1 585 m / 5 200 ft AMSL. Skutečnou základnu ověř pozorováním a předpovědí.'
],
'note' => 'Vysoká relativní vlhkost v přízemí neříká nic jistého o vlhkosti ve výšce. Vertikální profil je důležitý pro odhad vývoje oblačnosti.'
]
],
'checks' => [
[
'question' => 'Co se stane s RH při ochlazení za stejného obsahu vodní páry?',
'answer' => 'Vzroste, protože klesá tlak nasycené páry.'
],
[
'question' => 'Proč je nasycená adiabata menší než suchá?',
'answer' => 'Uvolňování latentního tepla kondenzace částečně kompenzuje ochlazování.'
],
[
'question' => 'Při T 18 °C a Td 15 °C odhadni základnu termických Cu.',
'answer' => 'Spread 3 °C: 3 × 122 ≈ 366 m AGL nebo 3 × 400 = 1 200 ft AGL; pouze orientační.'
]
]
];
