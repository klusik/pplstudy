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
'title' => 'Tlakové hladiny, výškoměr a hustotní výška',
'lead' => 'Výškoměr je barometr s převodem na výšku. Rozlišuj referenční tlak, indikovanou a skutečnou výšku, jinak vzniká nebezpečná iluze bezpečného odstupu od terénu.',
'questions' => [
16,
17,
19,
21,
22,
23,
24,
25
],
'sources' => [
'faa-weather',
'faa-handbooks',
'nws-pressure'
],
'sections' => [
[
'heading' => 'Co skutečně měří barometrický výškoměr',
'paras' => [
'Přístroj měří statický tlak okolního vzduchu a převádí ho podle standardního vztahu na výšku vůči nastavené referenční tlakové ploše. Nevidí terén. Výška nad zemí (AGL), nad střední hladinou moře (AMSL) a tlaková výška nejsou vzájemně zaměnitelné. Nastavení tlaku mění zobrazenou výšku, ne fyzickou polohu letadla. Letová hladina FL je vyjádřena ve stovkách stop při standardním nastavení 1013,25 hPa.'
],
'figure' => 'altimeter',
'table' => [
'headers' => [
'Nastavení / pojem',
'Co znamená',
'Na letišti'
],
'rows' => [
[
'QNH',
'Tlak redukovaný na MSL podle standardního vztahu; výškoměr indikuje altitude',
'Přibližně elevace letiště'
],
[
'QFE',
'Tlak na referenční úrovni letiště nebo prahu',
'Přibližně 0 ft na referenci'
],
[
'STD (QNE v učebním kontextu)',
'1013,25 hPa; tlaková výška/FL. QNE přesněji označuje indikaci při standardním nastavení v daném místě.',
'Nemusí odpovídat elevaci'
],
[
'QFF',
'Meteorologický tlak přepočtený na MSL s ohledem na skutečnou teplotu/vzduchový sloupec',
'Používá se na synoptické mapě, ne jako náhrada QNH'
]
]
],
'note' => 'Přechodovou nadmořskou výšku a aktuální převodní hladinu zjisti z platné AIP, ATIS nebo ATS. Nepřebírej americké pravidlo 18 000 ft do českého prostoru.'
],
[
'heading' => 'Tlaková odchylka: high to low',
'paras' => [
'Letíš-li při nezměněném nastavení z vyššího tlaku do nižšího, skutečná výška klesá vzhledem k indikované. Výškoměr ukazuje více, než by ukazoval při aktualizovaném QNH. U země odpovídá změna 1 hPa přibližně 27 ft (v jednoduchých testových úlohách se někdy používá 30 ft/hPa). Je to lokální aproximace, ne univerzální fyzikální konstanta. Před odletem porovnej výškoměr nastavený na QNH s publikovanou elevací na známém místě; přípustnou odchylku ověř podle předpisu a dokumentace.'
],
'formula' => 'H indikovaná (QNH) ≈ H tlaková + (QNH − 1013,25) × 27 ft/hPa',
'example' => [
'title' => 'FL85 a QNH 983 hPa',
'text' => 'FL85 = 8 500 ft tlakové výšky. QNH je asi o 30 hPa nižší než STD. Při 27 ft/hPa vychází indikovaná altitude asi 7 690 ft. Použití 30 ft/hPa by dalo asi 7 590 ft. Při ISA −10 je navíc třeba teplotní korekce.'
]
],
[
'heading' => 'Teplotní odchylka: hot to cold',
'paras' => [
'V chladném vzduchovém sloupci leží tlakové hladiny blíže u sebe. Při stejném indikovaném údaji se tak letadlo nachází geometricky níže než ve standardní nebo teplejší atmosféře. Jednoduché pravidlo pro odhad korekce v chladném vzduchu je asi 4 ft na každých 1 000 ft výšky nad referenčním místem a každý °C odchylky pod ISA. Pro provozní postupy je nutné použít příslušné tabulky, referenční elevaci, skutečný profil a pravidla pro korekci; jediná OAT ve FL sama o sobě celý profil neurčuje.'
],
'example' => [
'title' => 'Zkoušková úloha ÚCL č. 25 (FL85, QNH 983, ISA −10)',
'text' => 'Bankovní očekávaná odpověď je 7 300 ft. Při učební aproximaci 30 ft/hPa: 8 500 − 30 × 30 = 7 600 ft; studený sloupec odečte asi 4 × 7,6 × 10 = 304 ft, tedy ≈7 300 ft. Při jiné aproximační konstantě vyjde mírně jiné číslo. Jde o testovou aproximaci, nikoli přesnou skutečnou altitude bez dalších údajů.'
],
'note' => 'V chladu nad kopci nikdy nepovažuj indikované AMSL za přesně skutečnou altitude. Zahrň bezpečnou rezervu nad terénem; při letu v řízeném prostoru respektuj pravidla pro povolené hladiny.'
],
[
'heading' => 'Hustotní výška (density altitude)',
'paras' => [
'Hustotní výška je výška v ISA, při níž by vzduch měl stejnou hustotu jako právě v daných podmínkách. Neříká, kolik je letadlo nad zemí. Pro orientační výpočet nejprve zjisti tlakovou výšku letiště, potom použij OAT a teplotu ISA pro tuto výšku. Zjednodušená pomůcka kolem běžných GA podmínek je tlaková výška plus zhruba 120 ft na každý stupeň nad ISA; přesnou výkonnost odečti z POH. Vlhkost a specifika motoru mohou odhad dále změnit.'
],
'formula' => 'DA ≈ PA + 120 × (OAT − TISA) [ft]',
'example' => [
'title' => 'Letiště 1 500 ft, QNH 1003 hPa, OAT +30 °C',
'text' => 'PA ≈ 1 500 + (1013 − 1003) × 27 = 1 770 ft. TISA ≈ 15 − 2 × 1,77 = 11,5 °C. DA ≈ 1 770 + 120 × 18,5 = 3 990 ft. Letadlo je na zemi v 1 500 ft AMSL, ale hustota odpovídá přibližně 4 000 ft ISA.'
]
]
],
'checks' => [
[
'question' => 'Při nastavení QFE na prahu dráhy co indikuje výškoměr?',
'answer' => 'Přibližně nulu vzhledem k referenčnímu prahu.'
],
[
'question' => 'Při letu do nižšího tlaku bez přenastavení je skutečná výška vyšší, nebo nižší?',
'answer' => 'Nižší než odpovídá indikaci.'
],
[
'question' => 'Co odliší QFF a QNH?',
'answer' => 'QFF je meteorologická redukce s ohledem na reálné podmínky; QNH je letecké nastavení výškoměru.'
]
]
];
