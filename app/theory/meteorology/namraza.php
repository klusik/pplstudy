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
'title' => 'Námraza draku, motoru a kontaminace',
'lead' => 'Malé letadlo může ztratit výkonnost již s tenkou vrstvou ledu. Pochop, jakou roli hraje přechlazená voda, teplota povrchu a schopnost letadla námrazu zvládat.',
'questions' => [
96,
97,
98,
99
],
'sources' => [
'easa-icing',
'easa-engine',
'caa-winter',
'faa-weather'
],
'memory' => [
[
'label' => 'Zkouškový rozsah rizika námrazy',
'value' => '0 až −12 °C',
'note' => 'Časté rizikové podmínky s přechlazenou kapalnou vodou, ne absolutní hranice. Námraza je možná i mimo rozsah.',
'overview' => true
],
[
'label' => 'Nulová izoterma',
'value' => '0 °C',
'note' => 'Zjišťuj její výšku v m i ft, ale nezaměňuj ji za hranici výskytu kapalné vody.',
'overview' => false
],
[
'label' => 'Karburátorová námraza',
'value' => 'Riziko i při kladné OAT',
'note' => 'Ochlazení v sání; carb heat či alternate air používej podle AFM/POH konkrétního stroje.',
'overview' => false
],
[
'label' => 'Druh námrazy',
'value' => 'Malé kapky: rime · velké kapky: clear/glaze',
'note' => 'Tvar a velikost nánosu ovlivňuje aerodynamiku; nečekej na určitý typ ledu před rozhodnutím.',
'overview' => false
]
],
'sections' => [
[
'heading' => 'Podmínky vzniku námrazy',
'paras' => [
'Kapalná voda může zůstat přechlazená i pod 0 °C. Když taková kapka dopadne na vhodně chladný povrch letadla, může částečně nebo zcela zmrznout. Množství kapalné vody, velikost kapek, teplota, rychlost letadla a tvar povrchu ovlivňují rychlost a místo usazování ledu. Největší pravděpodobnost výrazné námrazy bývá v mnoha běžných oblačných situacích kolem 0 až −12 °C, což je rozsah zdůrazněný v bance. Námraza je však možná i mimo něj, včetně podstatně nižších teplot a za určitých okolností při kladné OAT na studeném povrchu.',
'Nulová izoterma je hladina, kde je okolní teplota 0 °C; její výšku čti s referencí a v metrech i stopách (např. 1 500 m ≈ 4 921 ft AMSL), může jich být i několik při inverzi. Samotná záporná teplota v suchém vzduchu není totéž co námraza. Mrak, mrznoucí srážky nebo namrzající mlha představují konkrétní zdroj vody. Velké přechlazené kapky (SLD, Supercooled Large Droplets), například mrznoucí déšť FZRA nebo mrznoucí mrholení FZDZ, mohou stékat za chráněné části draku.'
],
'figure' => 'icing'
],
[
'heading' => 'Zrnitá, čirá a smíšená námraza',
'figure' => 'ice-types',
'paras' => [
'Při rychlém zmrznutí malých kapek zůstává v ledu zachycen vzduch a výsledkem je bělavá, neprůhledná, drsná zrnitá námraza (rime ice). Větší přechlazené kapky mohou mrznout pomaleji, rozlévat se po povrchu a vytvářet tvrdší, průhlednější ledovku (clear/glaze ice) s nepravidelnými výstupky. Smíšená námraza má znaky obou mechanismů. Krystalky ledu samy o sobě obvykle nepřilnou stejně jako kapalná voda; zkoušková formulace o malých kapkách a krystalcích je zjednodušená.'
],
'table' => [
'headers' => [
'Druh',
'Typický vzhled',
'Mechanismus'
],
'rows' => [
[
'Rime / zrnitá',
'matný bílý drsný povrch',
'malé kapky, rychlé mrznutí'
],
[
'Clear / ledovka',
'čirý nebo průsvitný tvrdý led, rohy',
'větší kapky, rozlévání a postupné mrznutí'
],
[
'Mixed',
'nepravidelný smíšený vzhled',
'kombinace obou režimů'
],
[
'Frost / jinovatka',
'jemné krystalky na chladném povrchu',
'desublimace vodní páry'
]
]
]
],
[
'heading' => 'Co led udělá s letadlem',
'figure' => 'iced-airfoil',
'paras' => [
'Led mění geometrii profilu křídla, zvyšuje odpor, obvykle snižuje maximální součinitel vztlaku a mění průběh odtržení proudění. Pád může nastat při vyšší IAS nebo jiném úhlu náběhu než u čistého letadla. Námraza na ocasních plochách může zásadně zhoršit ovladatelnost, na vrtuli snižuje tah a vytváří vibrace. Pitotova trubice, statické otvory, antény a čelní sklo mohou být omezeny. Vyhřívaný pitot není systém ochrany celého letadla.',
'Při výcviku na C172 nebo podobném stroji vždy ověř konkrétní AFM/POH a schválené vybavení. Typické nespecializované výcvikové letadlo není schváleno pro úmyslný let do očekávaných podmínek námrazy. EASA NCO.OP.170 vyžaduje odpovídající certifikaci a vybavení; při nečekané námraze mimo schválený rozsah je nutné bez prodlení podmínky opustit, případně vyhlásit nouzi. Výběr hladiny nebo trasy musí respektovat terén, provoz a situaci.'
],
'note' => 'Nevzlétávej s jinovatkou, ledem nebo sněhem, které by mohly ovlivnit ovladatelnost či výkon, s výjimkou podmínek výslovně povolených AFM. Tenká průhledná vrstva není neškodná.'
],
[
'heading' => 'Námraza sání a karburátoru',
'figure' => 'carb-icing',
'paras' => [
'Karburátorový motor je ohrožen i za kladných teplot: tlakový pokles ve Venturiho trubici a odpařování paliva mohou výrazně ochladit směs. Při vhodné vlhkosti se tvoří led v sání, zužuje průtok a snižuje výkon. Při nízkém výkonu během sestupu a přiblížení může být riziko výrazné. U motoru s pevnou vrtulí se může projevit poklesem RPM; u stavitelné vrtule může být první změnou pokles plnicího tlaku, případně nepravidelný chod.',
'Vstřikování paliva odstraňuje klasický karburátor jako zdroj tohoto mechanismu, ale ne všechna rizika námrazy sání, například na filtru či vstupu. Použití carb heat (ohřev vzduchu pro karburátor), alternate air (alternativní vstup vzduchu) a pitot heat (vyhřívání pitotovy trubice) se řídí checklistem konkrétního letadla. Nikdy nepředpokládej stejný postup pro karburátorovou a vstřikovací verzi stejného typu.'
],
'example' => [
'title' => 'C172 a teplota +12 °C ve vlhkém vzduchu',
'text' => 'Při sestupu s redukovaným výkonem může u karburátorové varianty vznikat námraza uvnitř sání, i když na křídlech není led. Vstřikovací C172S má odlišnou konstrukci; postup musí vycházet z jejího POH.'
]
]
],
'checks' => [
[
'question' => 'Je oblast −15 °C mimo riziko námrazy?',
'answer' => 'Ne. Rozsah 0 až −12 °C je zkoušková pomůcka, ne hranice bezpečnosti.'
],
[
'question' => 'Která námraza je typicky spojena s velkými přechlazenými kapkami?',
'answer' => 'Čirá námraza / ledovka.'
],
[
'question' => 'Proč lze mít námrazu karburátoru při +15 °C?',
'answer' => 'V sání dochází k ochlazení expanzí a odpařováním paliva.'
]
]
];
