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
'memory' => [
[
'label' => 'Standardní nastavení',
'value' => '1013,25 hPa (STD)',
'note' => 'FL vyjadřuje tlakovou výšku ve stovkách ft; převod na m není skutečná AMSL.',
'overview' => false
],
[
'label' => 'Tlaková korekce blízko MSL',
'value' => '1 hPa ≈ 8,2 m ≈ 27 ft',
'note' => 'Zaokrouhlená zkoušková alternativa 1 hPa ≈ 9 m ≈ 30 ft. Používej konzistentně jednu aproximaci.',
'overview' => true
],
[
'label' => 'Teplotní korekce v chladu',
'value' => '4 m / 1 000 m / °C = 4 ft / 1 000 ft / °C',
'note' => 'Přibližně 0,4 % výšky nad referencí na každý °C rozdílu. Za chladu je skutečná výška nižší; provozně používej předepsané postupy.',
'overview' => true
],
[
'label' => 'Hustotní výška (DA)',
'value' => '≈ 36,6 m/°C ≈ 120 ft/°C',
'note' => 'Přírůstek nad tlakovou výšku na každý °C nad ISA. Pro výkon použij AFM/POH.',
'overview' => true
],
[
'label' => 'High to low / hot to cold',
'value' => 'Skutečná výška je nižší',
'note' => 'Pozor při letu do nižšího tlaku bez přenastavení nebo do chladnějšího vzduchu.',
'overview' => true
]
],
'sections' => [
[
'heading' => 'Co skutečně měří barometrický výškoměr',
'paras' => [
'Přístroj měří statický tlak okolního vzduchu a převádí ho podle standardního vztahu na výšku vůči nastavené referenční tlakové ploše. Nevidí terén. Výška nad zemí (AGL), nad střední hladinou moře (AMSL) a tlaková výška nejsou vzájemně zaměnitelné. Nastavení tlaku mění zobrazenou výšku, ne fyzickou polohu letadla. Letová hladina FL je vyjádřena ve stovkách stop při standardním nastavení 1013,25 hPa: FL85 znamená tlakovou výšku 8 500 ft (≈ 2 591 m). V českém vzdušném prostoru nepřeváděj přidělené FL na nový údaj pro hlášení ATS; metrický údaj slouží k pochopení a práci s příslušně vybaveným letadlem.'
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
'Přibližně 0 m / 0 ft na referenci'
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
'note' => 'Přechodovou nadmořskou výšku a aktuální převodní hladinu zjisti z platné AIP, ATIS nebo ATS. Nepřebírej americké pravidlo 18 000 ft (≈ 5 486 m) do českého prostoru.'
],
[
'heading' => 'Tlaková odchylka: high to low',
'paras' => [
'Letíš-li při nezměněném nastavení z vyššího tlaku do nižšího, skutečná výška klesá vzhledem k indikované. Výškoměr ukazuje více, než by ukazoval při aktualizovaném QNH. U země odpovídá změna 1 hPa přibližně 8,2 m / 27 ft (v jednoduchých testových úlohách se někdy používá 9,1 m / 30 ft na hPa). Je to lokální aproximace, ne univerzální fyzikální konstanta. Před odletem porovnej výškoměr nastavený na QNH s publikovanou elevací na známém místě; přípustnou odchylku ověř podle předpisu a dokumentace.'
],
'formula' => 'Hind [m] ≈ Htlak [m] + (QNH[hPa] − 1013,25) × 8,23
Hind [ft] ≈ Htlak [ft] + (QNH[hPa] − 1013,25) × 27',
'example' => [
'title' => 'FL85 a QNH 983 hPa',
'text' => 'FL85 = 8 500 ft (≈ 2 591 m) tlakové výšky. QNH je o 30,25 hPa nižší než STD. Při 27 ft/hPa (≈ 8,23 m/hPa) vychází indikovaná altitude asi 7 683 ft (2 342 m). Použití 30 ft/hPa (≈ 9,14 m/hPa) by dalo asi 7 593 ft (2 314 m). Při ISA −10 °C je navíc třeba teplotní korekce.'
]
],
[
'heading' => 'Teplotní odchylka: hot to cold',
'paras' => [
'V chladném vzduchovém sloupci leží tlakové hladiny blíže u sebe. Při stejném indikovaném údaji se tak letadlo nachází geometricky níže než ve standardní nebo teplejší atmosféře. Jednoduché pravidlo pro odhad korekce v chladném vzduchu je asi 4 m na každých 1 000 m nebo 4 ft na každých 1 000 ft výšky nad referenčním místem a každý °C odchylky pod ISA (0,4 %/°C). Pro provozní postupy je nutné použít příslušné tabulky, referenční elevaci, skutečný profil a pravidla pro korekci; jediná OAT ve FL sama o sobě celý profil neurčuje.'
],
'example' => [
'title' => 'Zkoušková úloha ÚCL č. 25 (FL85, QNH 983, ISA −10)',
'text' => 'Bankovní očekávaná odpověď je 7 300 ft (≈ 2 225 m). Při učební aproximaci 30 ft/hPa ≈ 9,14 m/hPa a zaokrouhleném rozdílu 30 hPa: 8 500 − 30 × 30 = 7 600 ft (≈ 2 316 m); studený sloupec odečte asi 4 × 7,6 × 10 = 304 ft (≈ 93 m), tedy přibližně 7 300 ft (2 225 m). Při jiné aproximační konstantě vyjde mírně jiné číslo. Jde o testovou aproximaci, nikoli přesnou skutečnou altitude bez dalších údajů.'
],
'note' => 'V chladu nad kopci nikdy nepovažuj indikované AMSL za přesně skutečnou altitude. Zahrň bezpečnou rezervu nad terénem; při letu v řízeném prostoru respektuj pravidla pro povolené hladiny.'
],
[
'heading' => 'Hustotní výška (density altitude)',
'paras' => [
'Hustotní výška je výška v ISA, při níž by vzduch měl stejnou hustotu jako právě v daných podmínkách. Neříká, kolik je letadlo nad zemí. Pro orientační výpočet nejprve zjisti tlakovou výšku letiště, potom použij OAT a teplotu ISA pro tuto výšku. Zjednodušená pomůcka kolem běžných GA podmínek je tlaková výška plus zhruba 36,6 m (120 ft) na každý °C nad ISA; přesnou výkonnost odečti z POH. Vlhkost a specifika motoru mohou odhad dále změnit.'
],
'formula' => 'DA [m] ≈ PA [m] + 36,6 × (OAT − TISA) [°C]
DA [ft] ≈ PA [ft] + 120 × (OAT − TISA) [°C]',
'example' => [
'title' => 'Letiště 457 m / 1 500 ft, QNH 1003 hPa, OAT +30 °C',
'text' => 'Ve stopách: PA ≈ 1 500 + (1013 − 1003) × 27 = 1 770 ft. TISA ≈ 15 − 2 × 1,77 = 11,5 °C. DA ≈ 1 770 + 120 × 18,5 = 3 990 ft. V metrech: PA ≈ 457 + 10 × 8,23 = 539 m; TISA ≈ 15 − 0,65 × 5,39 = 11,5 °C; DA ≈ 539 + 36,6 × 18,5 = 1 216 m. Letadlo je na zemi v 457 m / 1 500 ft AMSL, ale hustota odpovídá asi 1 220 m / 4 000 ft ISA.'
]
]
],
'checks' => [
[
'question' => 'Při nastavení QFE na prahu dráhy co indikuje výškoměr?',
'answer' => 'Přibližně 0 m / 0 ft vzhledem k referenčnímu prahu.'
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
