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
'sections' => [
[
'heading' => 'Tři skupenství a změny skupenství',
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
'Relativní vlhkost RH je poměr skutečného tlaku vodní páry e a tlaku nasycené vodní páry eₛ při dané teplotě. Teplejší vzduch má vyšší tlak nasycené páry. Nejde doslova o schopnost vzduchu něco ‚udržet‘ jako houba, ale o termodynamickou rovnováhu. Při ochlazování bez významné změny obsahu páry roste RH; při ohřívání klesá.',
'Rosný bod Td je teplota, na niž je třeba vzduch ochladit při přibližně konstantním tlaku a obsahu vodní páry, aby dosáhl nasycení. Rozdíl T − Td je deficit rosného bodu (dew-point spread). Malý spread znamená vyšší relativní vlhkost, ale sám neurčuje dohlednost ani vznik mlhy. Rosný bod při změně teploty za jinak stejných podmínek zůstává přibližně stálý; při výstupu nebo přísunu vlhkosti se však může měnit.'
],
'formula' => 'RH = 100 × e / eₛ(T) [%]    |    deficit = T − Td',
'figure' => 'humidity',
'example' => [
'title' => 'METAR 16/14 vs 24/14',
'text' => 'V obou případech je Td 14 °C. Spread 2 °C v prvním případě znamená blízkost nasycení; spread 10 °C ve druhém výrazně nižší RH. Pokud se v noci první vzduch ochladí na 14 °C a další podmínky dovolí, může vzniknout mlha.'
]
],
[
'heading' => 'Adiabatické ochlazování a ohřívání',
'paras' => [
'Pohybující se vzduchová částice si při přibližně adiabatickém ději nevyměňuje teplo s okolím. Při výstupu do nižšího tlaku expanduje a koná práci, čímž klesá její teplota. Při sestupu je stlačována a ohřívá se. Nenasycená částice se ochlazuje asi o 9,8 °C/km. Nasycená částice typicky o 4–7 °C/km, přičemž přesná hodnota závisí na tlaku, teplotě a množství kondenzující vody. Pro otázky ÚCL se používá přibližně 1 °C/100 m a 0,6 °C/100 m.',
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
'9,8 °C/km ≈ 1 °C/100 m',
'Nenasycená vystupující částice'
],
[
'SALR/MALR (nasycená adiabata)',
'proměnný, pro zkoušku ≈ 6 °C/km',
'Nasycená vystupující částice'
],
[
'ISA',
'6,5 °C/km',
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
'paras' => [
'Při suchém výstupu teplota klesá přibližně 3 °C/1 000 ft a rosný bod zhruba 0,5 °C/1 000 ft; jejich rozdíl se zmenšuje. Hladina, kde se zvedaná částice poprvé nasytí, se označuje LCL (lifting condensation level). Pro dobře promíchanou přízemní vrstvu lze orientačně odhadnout základnu termických kumulů pomocí 400 ft na 1 °C spreadu. Neplatí spolehlivě pro vrstevnaté oblaky, fronty, odlišné zdrojové vrstvy nebo orografii.'
],
'formula' => 'Základna termických Cu ≈ (T − Td) × 400 ft AGL',
'example' => [
'title' => 'T = 22 °C, Td = 12 °C',
'text' => 'Spread 10 °C, orientační základna 4 000 ft AGL nad místem původu částice. Při elevaci 1 200 ft by to bylo asi 5 200 ft AMSL. Skutečnou základnu ověř pozorováním a předpovědí.'
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
'answer' => 'Spread 3 °C × 400 ≈ 1 200 ft AGL; pouze orientační.'
]
]
];
