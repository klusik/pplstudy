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
'nws-pressure',
'faa-pa-da',
'nws-pa-da',
'rlp-units',
'rlp-altimeter'
],
'memory' => [
[
'label' => 'Standardní nastavení',
'value' => '1013,25 hPa (STD)',
'note' => 'FL vyjadřuje tlakovou výšku ve stovkách ft; převod na m není skutečná AMSL.',
'overview' => false
],
[
'label' => 'Tlaková výška (PA)',
'value' => 'PA = indikace při STD 1013,25 hPa',
'note' => 'Přibližně elevace + (1013,25 − QNH) × 8,23 m/hPa nebo 27 ft/hPa; při nižším QNH PA roste.',
'overview' => true
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
'label' => 'Výpočet DA: správné pořadí',
'value' => 'PA → TISA → OAT − TISA → DA',
'note' => 'Nejprve tlaková výška, poté standardní teplota v PA, teplotní rozdíl a nakonec hustotní výška.',
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
'Barometrický výškoměr měří statický tlak vzduchu. Tlak převádí na výšku pomocí vztahu odvozeného z ISA (standardní atmosféry z kapitoly 1). Na stupnici tlaku nastavíš referenci v hPa. Změnou tohoto nastavení se změní indikovaný údaj, ale nikoli fyzická poloha letadla. Samotný výškoměr nezná skutečnou teplotu celého sloupce vzduchu ani terén.',
'Než začneme počítat, rozlišme výšky. MSL (Mean Sea Level) je střední hladina moře. AMSL (Above Mean Sea Level) označuje výšku vztaženou k MSL. AGL (Above Ground Level) je výška nad terénem přímo pod letadlem. Elevace je nadmořská výška bodu na zemském povrchu, například referenčního místa letiště. Indikovaná výška je údaj na výškoměru při aktuálním nastavení; skutečná nadmořská výška je geometrická výška nad MSL a může se od indikace lišit.',
'QNH, QFE a STD dávají odlišné reference. Při správném QNH má výškoměr na referenčním místě letiště ukazovat přibližně publikovanou elevaci; při QFE přibližně nulu vůči příslušné referenci. QFF je meteorologická redukce tlaku pro analýzu počasí, nikoli nastavení používané místo QNH. STD je standardní nastavení 1013,25 hPa (doplňkově 29,92 inHg). Termín QNE označuje hodnotu indikovanou při standardním nastavení na příslušném referenčním místě. Co přesně ukáže při STD a proč, vysvětluje následující oddíl.'
],
'figure' => 'altimeter',
'table' => [
'headers' => [
'Pojem',
'Referenční hodnota',
'Co vyjadřuje'
],
'rows' => [
[
'Elevace letiště',
'MSL',
'Výška referenčního místa na zemi, např. 457 m / 1 500 ft AMSL'
],
[
'Indikovaná altitude při QNH',
'Nastavené místní QNH',
'Barometrický odhad nadmořské výšky; v reálné atmosféře může být zatížen chybou'
],
[
'Skutečná altitude',
'MSL',
'Geometrická nadmořská výška'
],
[
'Height (AGL)',
'Terén pod letadlem',
'Geometrická výška nad zemí; nevyplývá automaticky z QFE'
],
[
'QFE',
'Tlak na stanovené referenci letiště nebo prahu',
'Přibližně 0 m / 0 ft na této referenci'
],
[
'STD',
'1013,25 hPa',
'Tlaková výška (PA)'
],
[
'QFF',
'Tlak redukovaný na MSL s ohledem na skutečný teplotní profil',
'Meteorologická analýza, např. synoptická mapa'
]
]
],
'note' => 'V AIP ČR GEN 2.1 jsou výšky publikovány v metrech i stopách, ale ve vztahu k letadlu za letu se používají stopy. Vertikální rychlost se v leteckých hlášeních uvádí ve ft/min, vítr v kt, QNH v hPa, dohlednost a RVR v km nebo m. Metrické převody v učebnici slouží také pro letadla s metrickými přístroji. Dodržuj platná pravidla ATS, nikoli vlastní převod přidělené hladiny.'
],
[
'heading' => 'Tlaková výška (pressure altitude, PA)',
'paras' => [
'Pressure altitude (PA, tlaková výška) je výška, ve které by v modelu ISA panoval stejný tlak, jaký právě naměříme. ISA přiřazuje každému tlaku odpovídající výšku. Například tlak kolem 500 hPa odpovídá v ISA přibližně 5 570 m / 18 270 ft. PA proto není ani skutečnou nadmořskou výškou, ani výškou nad terénem. Závisí na naměřeném statickém tlaku, nikoli na aktuální venkovní teplotě.',
'PA zjistíš odečtem výškoměru při standardním nastavení STD = 1013,25 hPa. Na zemi lze tlakový údaj také vypočítat z elevace letiště a místního QNH. V běžných podmínkách blízko MSL použij přibližně 8,23 m/hPa (27 ft/hPa); v některých zkouškových úlohách 9,14 m/hPa (30 ft/hPa). QNH pod STD znamená vyšší PA než elevace, protože atmosférický tlak je nižší. QNH nad STD znamená nižší PA. Tyto konstanty jsou zjednodušené a neplatí přesně ve všech výškách.',
'PA má dvě hlavní využití: výkonové výpočty podle příručky letadla (AFM/POH) a letové hladiny (FL, Flight Level). FL85 znamená tlakovou výšku 8 500 ft (≈ 2 591 m), nikoli garantovanou výšku AMSL. PA budeme potřebovat při výpočtu hustotní výšky. Po případném kontrolním odečtu PA na zemi nastav zpět tlak požadovaný pro daný let podle platných postupů.'
],
'figure' => 'pressure-altitude',
'formula' => 'PA [m] ≈ elevace [m] + (1013,25 − QNH[hPa]) × 8,23 m/hPa
PA [ft] ≈ elevace [ft] + (1013,25 − QNH[hPa]) × 27 ft/hPa',
'example' => [
'title' => 'Letiště 457,2 m / 1 500 ft AMSL, QNH 1003 hPa',
'text' => 'Pro jednoduchý výpočet zaokrouhlíme STD na 1013 hPa, takže rozdíl je 10 hPa. Při přesné hodnotě STD vyjde rozdíl 10,25 hPa; odhad se bude mírně lišit.',
'steps' => [
[
'label' => 'Urči rozdíl tlaků',
'detail' => '1013 − 1003 = 10 hPa. QNH je nižší, takže PA bude vyšší než elevace.'
],
[
'label' => 'Vypočti přírůstek výšky',
'detail' => '10 × 27 = 270 ft; 10 × 8,23 = 82,3 m.'
],
[
'label' => 'Přičti k elevaci',
'detail' => 'PA ≈ 1 500 + 270 = 1 770 ft; PA ≈ 457,2 + 82,3 = 539,5 m. Letadlo se přitom vůbec nepohnulo.'
]
]
],
'note' => 'V ČR se převod mezi QNH a STD řídí aktuální AIP/VFR příručkou a ATS. Převodní nadmořská výška je obvykle publikována jako 5 000 ft / 1 500 m AMSL (s horskou výjimkou). Tyto publikované dvojice jsou provozní zaokrouhlení; matematicky je 5 000 ft = 1 524 m. Převodní hladina je odlišný pojem. Nepoužívej americkou převodní výšku 18 000 ft (5 486 m).'
],
[
'heading' => 'Tlaková odchylka: high to low',
'paras' => [
'Představ si dvě oblasti s rozdílným QNH a letadlo, jehož výškoměr zůstává na původním vyšším QNH. Při zachování stejného indikovaného údaje sleduje letadlo přibližně stejnou tlakovou hladinu. Tato hladina je v oblasti nižšího tlaku níže (při jinak srovnatelném teplotním profilu), takže se zmenšuje skutečná výška nad MSL a může se zmenšit odstup od terénu.',
'Jestliže v nové oblasti nastavíš nižší aktuální QNH, indikovaný údaj klesne. V okamžiku přenastavení se geometrická výška nemění. Přibližná změna indikace v běžných podmínkách u MSL je 8,23 m / 27 ft na 1 hPa. Zkoušková aproximace 9,14 m / 30 ft na 1 hPa je jen alternativní zaokrouhlená pomůcka. Pro celý výškový rozsah neexistuje jediná přesná lineární konstanta.',
'Z předchozího vztahu pro PA lze opačnou úpravou odhadnout indikaci při QNH: odečteš standardní tlak od QNH a příslušnou korekci přičteš k PA. Výsledkem zatím není skutečná výška: teplotní chybu vyřeší následující oddíl.'
],
'figure' => 'pressure-error',
'formula' => 'Hind [m] ≈ PA [m] + (QNH[hPa] − 1013,25) × 8,23 m/hPa
Hind [ft] ≈ PA [ft] + (QNH[hPa] − 1013,25) × 27 ft/hPa',
'example' => [
'title' => 'Přechod z QNH 1013 hPa do oblasti QNH 993 hPa',
'text' => 'Rozdíl nastavení je −20 hPa. Při neměnné skutečné poloze po přenastavení výškoměru klesne jeho indikace přibližně o 20 × 8,23 = 165 m (20 × 27 = 540 ft). Pokud bys místo přenastavení stále držel původní indikovanou výšku, mohl bys být v modelové situaci přibližně o tuto hodnotu níže. Jde o odhad u MSL, nikoli bezpečnostní rezervu.'
],
'note' => 'High to low, look out below. Před odletem nastav QNH a porovnej výškoměr s elevací v příslušném referenčním místě. Povolenou odchylku a postup ověř v předpisu a dokumentaci konkrétního letadla.'
],
[
'heading' => 'Teplotní odchylka: hot to cold',
'paras' => [
'Teď přidáme další veličinu: teplotu. Teplejší sloupec vzduchu má mezi dvěma stejnými tlakovými hladinami větší tloušťku než chladný. Při správném QNH proto může výškoměr stále ukazovat jinou výšku, než je skutečná geometrická nadmořská výška. V chladném vzduchu je skutečná výška nad referenčním místem při stejné indikaci menší.',
'Pro orientační výpočet použij pomůcku 4 % výšky nad referencí na každých 10 °C pod ISA, tedy 0,4 % na 1 °C. H je indikovaná výška NAD referenčním letištěm, nikoli automaticky altitude AMSL. ΔT je kladný rozdíl mezi referenční teplotou ISA a skutečnou teplotou v chladnějším sloupci. Při jednoduché zkouškové úloze se často předpokládá stejná odchylka od ISA v celé vrstvě.',
'Pokud odhaduješ skutečnou výšku, kladnou hodnotu korekce odečítáš od indikované výšky nad referencí. Pokud naopak stanovuješ požadovanou indikovanou výšku pro předepsaný odstup od terénu, může se podle příslušného provozního postupu korekce přičítat. Nezaměňuj tyto dva účely. Skutečný profil, referenční elevace a předepsané tabulky jsou pro provoz rozhodující.'
],
'figure' => 'cold-error',
'formula' => 'ΔH [m] ≈ H [m] × 0,004 × ΔT [°C]
ΔH [ft] ≈ H [ft] × 0,004 × ΔT [°C]
V chladu: Hskutečná ≈ Hindikovaná − ΔH',
'example' => [
'title' => 'Úloha ÚCL č. 25: FL85, QNH 983 hPa, ISA −10 °C',
'text' => 'Banka očekává 7 300 ft (≈ 2 225 m). Pro tuto zkouškovou aproximaci použijeme rozdíl 30 hPa, koeficient 30 ft/hPa a předpoklad reference u MSL.',
'steps' => [
[
'label' => 'Z FL zjisti tlakovou výšku',
'detail' => 'FL85 = PA 8 500 ft (≈ 2 591 m).'
],
[
'label' => 'Přepočítej indikaci při QNH',
'detail' => '8 500 − 30 × 30 = 7 600 ft (≈ 2 316 m).'
],
[
'label' => 'Odhadni teplotní chybu',
'detail' => '0,004 × 10 × 7 600 = 304 ft (≈ 93 m).'
],
[
'label' => 'Odhad skutečné výšky',
'detail' => '7 600 − 304 = 7 296 ft, po zaokrouhlení 7 300 ft (≈ 2 225 m).'
]
]
],
'note' => 'Jde o výukový odhad, nikoli náhradu předepsané korekce při skutečném letu. Samotná OAT v jedné hladině nepopisuje celý teplotní profil. Při jiné aproximační konstantě vyjde jiné číslo.'
],
[
'heading' => 'Hustotní výška (density altitude, DA)',
'paras' => [
'V kapitole 1 jsme zjistili, že hustota vzduchu závisí na tlaku a teplotě (a také vlhkosti). PA z předchozího oddílu převede skutečný tlak na odpovídající výšku v ISA. Hustotní výška (DA, Density Altitude) postupuje podobně, ale místo tlaku přiřadí výšku ISA skutečné hustotě vzduchu. DA není údaj o poloze letadla, nýbrž popis podmínek pro jeho výkon.',
'Nejprve zjistíš PA. Pak podle gradientu ISA vypočteš TISA, tedy standardní teplotu právě v této tlakové výšce, nikoliv v elevaci letiště. OAT (Outside Air Temperature) je skutečná venkovní teplota. Rozdíl OAT − TISA říká, o kolik je vzduch teplejší nebo chladnější než standard. V ISA jsou PA a DA shodné. V suchém vzduchu nad ISA je DA vyšší než PA; pod ISA nižší. Přibližný výpočet používá 36,6 m / 120 ft na každý stupeň Celsia odchylky od ISA.',
'Vyšší DA obvykle zhoršuje vzlet a stoupání malého letadla: při stejné IAS (indikované vzdušné rychlosti) je vyšší TAS (skutečná vzdušná rychlost vůči vzduchu), motor bez přeplňování má méně kyslíku k dispozici a vrtule pracuje v řidším vzduchu. Vlhkost dále ovlivňuje skutečnou hustotu, ale uvedená pomůcka ji nezahrnuje. Pro konkrétní výkon nepoužívej DA jako univerzální náhradu tabulek AFM (Aircraft Flight Manual) nebo POH (Pilot’s Operating Handbook); některé tabulky přímo vyžadují PA a OAT.'
],
'figure' => 'density-altitude',
'formula' => 'TISA [°C] ≈ 15 − 0,65 × PA [m] / 100
TISA [°C] ≈ 15 − 2 × PA [ft] / 1 000
DA [m] ≈ PA [m] + 36,6 m/°C × (OAT − TISA)
DA [ft] ≈ PA [ft] + 120 ft/°C × (OAT − TISA)',
'example' => [
'title' => 'Navazující příklad: stejné letiště, QNH 1003 hPa, OAT +30 °C',
'text' => 'Z výpočtu PA už víme: elevace 457,2 m / 1 500 ft a tlaková výška přibližně 539,5 m / 1 770 ft. Nyní dopočteme hustotní výšku. Obě cesty používají zaokrouhlené výukové konstanty.',
'steps' => [
[
'label' => 'PA: převezmi hodnotu z předchozího oddílu',
'detail' => 'PA ≈ 539,5 m / 1 770 ft.'
],
[
'label' => 'TISA: vypočti standardní teplotu v PA',
'detail' => 'Metricky: 15 − 0,65 × 5,395 ≈ +11,5 °C. Ve stopách: 15 − 2 × 1,77 ≈ +11,5 °C.'
],
[
'label' => 'Rozdíl skutečné a standardní teploty',
'detail' => 'OAT − TISA ≈ 30 − 11,5 = +18,5 °C.'
],
[
'label' => 'DA: přičti teplotní přírůstek k PA',
'detail' => '539,5 + 36,6 × 18,5 ≈ 1 217 m. 1 770 + 120 × 18,5 ≈ 3 990 ft.'
],
[
'label' => 'Vylož výsledek',
'detail' => 'Letadlo je stále na zemi ve výšce 457 m / 1 500 ft AMSL. Hustota vzduchu však přibližně odpovídá 1 220 m / 4 000 ft v ISA. Pro rozjezd, délku vzletu a stoupání pokračuj výkonovým výpočtem podle AFM/POH.'
]
]
],
'note' => 'Při práci s jednotkami použij vždy celý metrický nebo celý stopový výpočet. Pokud zaokrouhluješ mezivýsledky, rozdíl několika metrů či stop je normální. Nejde o přesný výpočet hustoty vlhkého vzduchu.'
]
],
'checks' => [
[
'question' => 'Co je tlaková výška a jak ji odečteš?',
'answer' => 'Výška, které v ISA odpovídá naměřený tlak. Odečteš ji z výškoměru při STD 1013,25 hPa. Není geometrická výška.'
],
[
'question' => 'Letiště 457 m / 1 500 ft má QNH 1003 hPa. Jakou PA přibližně použiješ?',
'answer' => 'Při zaokrouhlení STD na 1013 hPa je rozdíl 10 hPa. PA ≈ 539,5 m / 1 770 ft; letadlo zůstává na zemi.'
],
[
'question' => 'V jaké výšce se počítá TISA pro DA a co je OAT?',
'answer' => 'TISA je teplota ISA v tlakové výšce PA. OAT je skutečná venkovní teplota. Rozdíl OAT − TISA použij pro DA, nikoli rozdíl od konstantních 15 °C.'
],

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
