/** Project: PPL Study | Author: Rudolf Klusal | MIT License
 * Weather phenomena, reports and briefing laboratories.
 */
function registerWeatherLabs(L) {
    'use strict';
    const {add,fmt,H,clamp,rad,isa,pressure,pa,da,lcl,rh,C,text,line,rect,circle,path,arrow,cloud,plane,svg,out,S,Q,T,D,E,P,caveat,plot,levels,bars,timeline,skyCover,cover,compass}=L;
    add('airmass','Původ vzduchové hmoty','Vyber zdrojovou oblast a povrch, nad kterým se vzduch formuje.',[Q('moist','Povrch',['Oceán','Pevnina']),Q('lat','Oblast',['Polární','Tropická'])],({moist,lat})=>{
        const code=(moist?'c':'m')+(lat?'T':'P');
        return out([['Označení',code],['Typická vlastnost',(lat?'Teplejší':'Chladnější')+', '+(moist?'sušší':'vlhčí')]],svg(rect(25,30,510,185,lat?'#f9eee6':C.sky)+rect(25,175,510,40,moist?C.ground:'#a8cee5')+cloud(280,65,moist?.65:1.6)+arrow(280,155,280,100,C.blue)+text(280,240,moist?'Pevnina':'Oceán'),'Schematický původ vzduchové hmoty'),'Při přesunu nad jiný povrch se vlastnosti vzduchu mění. Značka vyjadřuje původ, ne automaticky počasí v cíli.');});
    add('front-section','Teplá a studená fronta v řezu','Přepni typ fronty a posouvej pozorovatele vzhledem k přízemnímu rozhraní.',[Q('kind','Fronta',['Teplá','Studená']),S('x','Poloha vůči frontě',-200,200,10,-70,'km')],({kind,x})=>{
        const xx=280+x,top=kind?205:500;
        let s=rect(25,20,510,195,'#f9eee6')+path('M 280 215 L '+top+' 35 L '+(kind?25:535)+' 35 L '+(kind?25:535)+' 215 Z',C.blue,2,'#d4e7f3')+rect(25,215,510,12,C.ground);
        if(kind)s+=cloud(295,80,1.3)+cloud(315,48,1.1);
        else for(let i=0;i<4;i++)s+=cloud(320+i*48,150-i*30,.85);
        s+=line(xx,215,xx,180,C.ink,2)+plane(xx,174)+arrow(240,235,320,235,C.gray)+text(280,248,'Postup fronty',C.gray,'middle',11);
        return out([['Poloha',x===0?'Na rozhraní':fmt(Math.abs(x))+' km '+(x<0?'za frontou':'před frontou')],['Typické rozhraní',kind?'Strmější':'Pozvolnější']],svg(s,'Schematický vertikální řez frontou'),'Studená fronta mívá strmější rozhraní, teplá rozsáhlejší vrstevnatou oblačnost. Nákres a vzdálenosti nejsou ve společném měřítku.');});
    add('front-symbols','Rozpoznávání front na mapě','Vyber symbol a sleduj stranu postupu.',[Q('type','Fronta',['Teplá','Studená','Okluzní','Stacionární'])],({type})=>{
        let s=line(35,125,525,125,type===0?C.red:type===1?C.blue:type===2?C.purple:C.gray,4);
        for(let i=0;i<7;i++){const x=70+i*70,tri=type===1||((type===2||type===3)&&i%2===0),bottom=type===3&&tri,co=type===2?C.purple:tri?C.blue:C.red;
            s+=tri?path('M '+(x-15)+' 125 L '+x+' '+(bottom?148:102)+' L '+(x+15)+' 125 Z',co,2,co):path('M '+(x-15)+' 125 A 15 15 0 0 1 '+(x+15)+' 125 Z',co,2,co);}
        if(type!==3)s+=arrow(280,91,280,38,C.gray);
        return out([['Význam',['Teplý vzduch postupuje','Studený vzduch postupuje','Okluzní rozhraní','Bez výrazného postupu'][type]]],svg(s,'Symbol fronty'),'Půlkruhy a trojúhelníky označují stranu postupu; u stacionární fronty jsou na protilehlých stranách.');});
    add('front-timeline','Přechod frontální níže','Posouvej čas pozorování v typickém sledu počasí.',[S('stage','Fáze',0,4,1,1)],({stage})=>{
        const a=[['Před frontou','Ci / Cs'],['Teplá fronta','As / Ns'],['Teplý sektor','St / Sc'],['Studená fronta','Cu / Cb'],['Za frontou','Přeháňky']];
        return out([['Fáze',a[stage][0]],['Typická oblaka',a[stage][1]]],timeline(a,stage),['Tlak často klesá, oblačnost přibývá.','Rozsáhlé srážky a nízké základny jsou možné.','Může přetrvat nízká vrstevnatá oblačnost.','Možné přeháňky, změna větru a turbulence.','Často lepší dohlednost mimo přeháňky, stále riziko konvekce.'][stage]);});
    add('visibility-ceiling','Dohlednost a základna jsou nezávislé','Nastav vodorovnou dohlednost a základnu nízké vrstvy.',[S('v','Dohlednost',.2,15,.2,3,'km'),S('h','Základna',100,2500,100,600,'ft AGL')],({v,h})=>{
        let s=rect(25,25,510,192,C.sky)+rect(25,210,510,20,C.ground);
        const y=205-h/2500*160;
        s+=rect(25,y-25,510,25,'#bbcddd')+line(25,y,535,y,C.gray,2,'5 4');
        for(let i=0;i<6;i++){const x=65+i*84,op=clamp(1-i*2.5/Math.max(.2,v),.04,1);s+='<g opacity="'+op+'">'+rect(x,165,15,45,C.gray)+path('M '+(x-4)+' 165 L '+(x+7)+' 145 L '+(x+19)+' 165',C.gray,2)+'</g>';}
        s+=text(280,244,'Schematické zeslabení kontrastu s dálkou',C.gray,'middle',12);
        return out([['Horizontální VIS',fmt(v,1)+' km'],['Vertikální základna',H(h*.3048)]],svg(s,'Dohlednost a nízká oblačnost'),'Dobrá dohlednost u země neznamená dostatek vertikálního prostoru. Obrázek není optický model RVR.');});
    add('radiation-fog','Noční ochlazování k rosnému bodu','Posuň pokles teploty při stejném počátečním Td.',[S('t','Večerní T',-5,25,1,12,'°C'),S('dep','Večerní deficit',0,15,1,4,'°C'),S('cool','Ochlazení',0,15,1,5,'°C')],({t,dep,cool})=>{
        const td=t-dep,end=t-cool,r=rh(end,Math.min(td,end));
        return out([['Konečná T',fmt(end)+' °C'],['RH (omezená na nasycení)',fmt(r,1)+' %'],['Stav',end<=td?'Dosaženo nasycení':'Nenasyceno']],plot([{name:'T',color:C.warm,points:[[0,t],[1,end]]},{name:'Poč. Td',color:C.blue,points:[[0,td],[1,td]]}],0,1,-35,30,'Relativní průběh noci','Teplota (°C)'),end<=td?'Model dosáhl rosného bodu. V reálu se Td při kondenzaci mění a vznik mlhy závisí na dalších faktorech.':'Ještě chybí ochlazení k rosnému bodu.');});
    add('advection-fog','Vlhký vzduch nad studeným povrchem','Nastav rosný bod přitékajícího vzduchu a teplotu podkladu.',[S('td','Rosný bod vzduchu',-10,25,1,12,'°C'),S('s','Teplota podkladu',-15,25,1,7,'°C')],({td,s})=>{
        const d=td-s;let body=rect(25,40,510,170,C.sky)+rect(25,210,510,20,'#a8c8d8')+arrow(55,105,480,105,C.blue,4);
        if(d>=0)for(let i=0;i<6;i++)body+=cloud(65+i*84,190,.85);
        return out([['Td − T podkladu',fmt(d)+' °C'],['Možnost nasycení',d>=0?'Při dostatečném ochlazení':'Ne samotným ochlazením na tento podklad']],svg(body,'Advekce vzduchu přes podklad'),'Mlha může vzniknout, pokud se přitékající vzduch skutečně ochladí k Td. Rozhoduje doba kontaktu, mísení a vítr.');});
    add('visibility-trend','Zhoršující se dohlednost na trase','Nastav počáteční dohlednost a fiktivní lineární trend.',[S('v','Počáteční dohlednost',2,15,.5,8,'km'),S('rate','Pokles za hodinu',0,4,.5,2,'km/h'),S('h','Čas od pozorování',0,3,.25,1.5,'h')],({v,rate,h})=>{
        const val=Math.max(0,v-rate*h);
        return out([['Modelová dohlednost',fmt(val,1)+' km'],['Čas',fmt(h,2)+' h']],plot([{name:'Trend',color:C.blue,points:Array.from({length:13},(_,i)=>[i/4,Math.max(0,v-rate*i/4)])}],0,3,0,16,'Čas (h)','Dohlednost (km)',[h,val]),'Lineární trend je myšlenkový experiment. Nelze odvodit dobu, do které je let bezpečný.');},caveat);
    add('icing-conditions','Teplota sama o sobě nestačí','Změň OAT a přítomnost kapalných kapek.',[S('t','OAT',-40,15,1,-8,'°C'),Q('water','Prostředí',['Bez zadané kapalné vody','Kapalné kapky'],1)],({t,water})=>{
        let s=rect(25,20,510,210,C.sky)+plane(280,125);
        if(water)for(let i=0;i<12;i++)s+=circle(55+(i%6)*89,70+Math.floor(i/6)*100,4,C.blue);
        if(water&&t<=0)s+=path('M 300 125 L 285 118 L 274 108 M 285 132 L 274 142',C.purple,5);
        return out([['OAT',fmt(t)+' °C'],['Modelová podmínka',water&&t<=0?'Kapalné kapky a mráz':water?'Kapalná voda; T nad nulou':'Kapky nezadány']],svg(s,'Podmínky námrazy draku'),'Model znázorňuje typickou kombinaci, nikoli hranice výskytu. Teplota povrchu, velikost kapek, FZRA a další jevy mění situaci.');},'Absence označené kombinace neznamená bezpečný let ani vyloučení námrazy.');
    add('ice-shapes','Tři podoby námrazy','Přepínej typ nánosu na náběžném profilu.',[Q('kind','Nános',['Bez nánosu','Zrnitá','Čirá','Smíšená'],2)],({kind})=>{
        let s=path('M 130 125 Q 160 70 265 94 L 475 125 Q 270 158 130 125 Z',C.gray,2,'#dce8f0');
        if(kind===1)for(let i=0;i<7;i++)s+=circle(133+i*5,122-i*5,6,'#a8bdcf');
        if(kind===2)s+=path('M 160 98 Q 115 88 110 123 Q 120 142 162 140 L 182 136 Q 142 126 160 98 Z',C.blue,2,'#c4dfef');
        if(kind===3)s+=path('M 159 101 L 120 78 L 127 115 L 105 127 L 134 135 L 125 158 L 167 140',C.blue,3,'#a8bdcf');
        return out([['Typ',['Čistý profil','Rime','Clear / glaze','Mixed'][kind]]],svg(s,'Schematický tvar nánosu'),'Zrnitá námraza obvykle rychle namrzá a je drsnější, čirá může vytvářet nepravidelné rohy. Nákres není predikcí skutečného tvaru.');});
    add('wing-contamination','Kontaminace a proudění','Porovnej čistý profil a stupně schematické kontaminace.',[S('ice','Ilustrační kontaminace',0,4,1,2)],({ice})=>{
        let s=path('M 125 128 Q 170 70 290 98 L 490 128 Q 275 158 125 128 Z',C.gray,2,'#dce8f0');
        if(ice)s+=path('M 145 105 L '+(120-ice*4)+' '+(112-ice*5)+' L 123 128 L '+(123-ice*3)+' '+(133+ice*5)+' L 154 139',C.blue,3,'#b2d5e7');
        for(let i=0;i<3;i++){const y=35+i*15;s+=path('M 20 '+y+' Q 190 '+(y-10)+' 315 '+(y+(ice?ice*5:0))+' T 545 '+(y+(ice?ice*8:0)),ice?C.warm:C.blue,1.5);}
        return out([['Profil',ice?'Kontaminovaný':'Čistý'],['Tendence',ice?'Vyšší odpor, nižší CLmax':'Referenční stav']],svg(s,'Ilustrační proudnice kolem profilu'),'Stupeň je pouze vizuální, nikoli tloušťka v mm. Nelze z něj počítat pádovou rychlost, vztlak ani bezpečný limit.');});
    add('carb-cooling','Ochlazení v karburátoru','Měň OAT a modelový pokles teploty při průtoku.',[S('t','OAT',-10,35,1,15,'°C'),S('drop','Pokles v karburátoru',0,35,1,20,'°C'),S('dep','Deficit T − Td u vstupu',0,20,1,3,'°C')],({t,drop,dep})=>{
        const v=t-drop;let s=rect(35,65,490,120,C.sky,C.line)+path('M 35 65 L 220 105 L 340 105 L 525 65 M 35 185 L 220 145 L 340 145 L 525 185',C.gray,3)+arrow(55,125,500,125,C.blue);
        if(v<=0&&drop>=dep)s+=rect(225,105,110,12,'#a8cde3')+rect(225,133,110,12,'#a8cde3');
        return out([['T v zúžení (model)',fmt(v)+' °C'],['Td na vstupu',fmt(t-dep)+' °C']],svg(s,'Průtok zúžením a schematická kondenzace nebo led'),'Pokles tlaku a odpařování paliva ochlazují směs. OAT nad nulou nevylučuje námrazu karburátoru. Model neřeší změnu rosného bodu s tlakem.');},'Nejde o provozní diagram rizika. Postupuj podle AFM/POH konkrétního motoru.');
    add('storm-life','Životní cyklus bouřkové buňky','Přepínej vývoj, zralost a rozpad.',[S('stage','Fáze',0,2,1,1)],({stage})=>{
        let s=rect(25,20,510,195,C.sky)+rect(25,215,510,15,C.ground);
        for(let i=0;i<(stage===0?3:5);i++)s+=cloud(280,176-i*31,1.2+i*.16);
        if(stage!==2)s+=arrow(240,202,240,stage===0?100:40,C.warm,4);
        if(stage!==0){s+=arrow(325,50,325,205,C.blue,4);for(let x=330;x<430;x+=20)s+=line(x,155,x-8,204,C.blue,1.5);}
        if(stage===1)s+=rect(225,36,210,12,'#b5cbd9','none',9);
        return out([['Fáze',['Vývojová','Zralá','Rozpadová'][stage]],['Proudění',['Převážně výstupné','Výstupné i sestupné','Převážně sestupné'][stage]]],svg(s,'Vertikální pohyb v bouřkové buňce'),'Skutečné bouřky mohou obsahovat více buněk současně. I rozpadající se buňka může být nebezpečná.');});
    add('microburst','Microburst v podélném řezu','Posouvej letadlo v idealizovaném poli proudění.',[S('x','Poloha vůči středu',-1000,1000,50,-650,'m'),S('v','Maximum horizontálního výtoku',5,40,1,25,'kt'),S('sink','Maximum sestupného proudu',0,3000,100,1600,'ft/min')],({x,v,sink})=>{
        const head=-Math.sign(x)*v*Math.sin(Math.PI*Math.abs(x)/1000),down=sink*Math.max(0,1-Math.abs(x)/700);
        let s=rect(25,20,510,200,C.sky)+rect(25,220,510,10,C.ground)+cloud(280,42,1.6)+arrow(280,70,280,190,C.blue,5)+arrow(280,190,65,190,C.blue,4)+arrow(280,190,495,190,C.blue,4)+plane(280+x*.23,155);
        return out([[head>=0?'Čelní složka':'Zadní složka',fmt(Math.abs(head),1)+' kt'],['Sestupná složka (model)',fmt(down)+' ft/min']],svg(s,'Sestupný proud a rozbíhající se výtok'),'Při pohybu zleva doprava se čelní složka mění na zadní. Čísla představují idealizované pole, nikoli letovou simulaci.');},'Nejde o návod k průletu microburstem ani výpočet výkonnostní rezervy.');
    add('wind-shear','Střih: změna vektoru větru','Změň směr a rychlost větru ve dvou hladinách.',[S('v1','Dolní rychlost',0,50,1,10,'kt'),S('d1','Dolní směr OD',0,360,10,180,'°'),S('v2','Horní rychlost',0,60,1,30,'kt'),S('d2','Horní směr OD',0,360,10,270,'°'),S('gap','Svislý rozestup',100,2000,100,500,'m')],({v1,d1,v2,d2,gap})=>{
        const dx=v2*Math.sin(rad(d2))-v1*Math.sin(rad(d1)),dy=v2*Math.cos(rad(d2))-v1*Math.cos(rad(d1)),delta=Math.hypot(dx,dy);
        let s=rect(25,25,510,190,C.sky)+line(25,125,535,125,C.line,2,'5 4');
        for(const [v,d,y] of [[v1,d1,173],[v2,d2,72]])s+=arrow(280+Math.sin(rad(d))*v*1.7,y-Math.cos(rad(d))*v*.65,280,y,C.blue,3)+text(60,y+4,fmt(d)+'° / '+fmt(v)+' kt',C.ink,'start');
        return out([['Změna vektoru',fmt(delta,1)+' kt'],['Na 100 m (průměr)',fmt(delta/gap*100,1)+' kt / 100 m']],svg(s,'Vektory větru ve dvou vrstvách'),'Střih zahrnuje změnu směru i rychlosti. Tento průměr neurčuje intenzitu turbulence ani bezpečný limit.');});
    add('orographic-lift','Návětří a základna oblačnosti','Změň hřeben a deficit u paty hory.',[S('h','Hřeben nad patou',200,3500,100,1700,'m'),S('dep','Deficit T − Td u paty',0,20,1,8,'°C')],({h,dep})=>{
        const l=122*dep,y=215-h/3500*175;
        let s=rect(25,20,510,195,C.sky)+path('M 25 215 L 105 215 L 280 '+y+' L 445 215 L 535 215 Z',C.gray,2,C.ground)+arrow(85,185,235,Math.max(25,y+5),C.blue)+arrow(325,Math.max(25,y+5),480,185,C.warm);
        if(h>=l)s+=cloud(245,Math.max(28,215-l/3500*175-13),1.1);
        return out([['LCL nad patou (odhad)',H(l)],['Vrchol vůči LCL',H(h-l)]],svg(s,'Vynucený výstup na návětří'),'Pokud výstup dosáhne kondenzace, může vzniknout orografická oblačnost. Skutečný profil a blokování proudění zde nejsou zahrnuty.');});
    add('mountain-wave','Horská vlna a rotor','Měň vlnovou délku a amplitudu schematické proudnice.',[S('w','Vlnová délka (schéma)',70,220,10,140,'rel. j.'),S('a','Amplituda (schéma)',0,45,5,25,'rel. j.')],({w,a})=>{
        let s=rect(20,20,520,200,C.sky)+path('M 20 220 L 100 220 L 185 110 L 270 220 L 540 220 Z',C.gray,2,C.ground);
        for(let row=0;row<3;row++){let d='';for(let x=185;x<=530;x+=5){const y=62+row*42-a*Math.sin((x-185)*2*Math.PI/w);d+=(x===185?'M ':' L ')+x+' '+y;}s+=path(d,C.blue,2);}
        if(a>0)s+=circle(300,196,15,'none',C.warm,2)+arrow(290,187,306,184,C.warm);
        if(a>10)s+=cloud(185+w,52-a*.5,.9);
        return out([['Vlnová délka',fmt(w)+' rel. j.'],['Amplituda',fmt(a)+' rel. j.']],svg(s,'Idealizovaná horská vlna a závětrný rotor'),'Parametry jsou kreslicí, nikoli odvozené z větru nebo stability. Rotor a silné sestupné proudy mohou existovat bez viditelných oblaků.');});
    add('foehn','Fén: výstup a sestup částice','Nastav T, deficit a výšku hřebene; porovnej teploty na stejné elevaci.',[S('t','T u paty návětří',0,30,1,20,'°C'),S('dep','Deficit u paty',0,20,1,8,'°C'),S('h','Hřeben nad patou',0,3500,100,2000,'m')],({t,dep,h})=>{
        const l=Math.min(h,dep*122),top=t-.0098*l-.006*(h-l),lee=top+.0098*h;
        return out([['LCL (odhad)',H(dep*122)],['T na hřebeni',fmt(top,1)+' °C'],['T v závětří',fmt(lee,1)+' °C'],['Rozdíl',fmt(lee-t,1)+' °C']],plot([{name:'Výstup',color:C.blue,points:[[t,0],[t-.0098*l,l/1000],[top,h/1000]]},{name:'Sestup',color:C.warm,points:[[lee,0],[top,h/1000]]}],-40,50,0,3.5,'Teplota (°C)','Výška nad patou (km)'),'Předpoklad: po dosažení LCL pevná SALR 6 °C/km a odstranění kondenzátu, sestup suchý 9,8 °C/km. Skutečný fén má i jiné mechanismy.');});
    add('regional-wind','Mistrál a bora','Vyber vítr a prohlédni si typický terénní mechanismus.',[Q('kind','Vítr',['Mistrál','Bora'])],({kind})=>{
        let s=rect(25,25,510,195,C.sky)+path(kind?'M 25 220 L 140 80 L 260 220 L 535 220 Z':'M 25 220 L 125 75 L 210 220 L 350 220 L 435 75 L 535 220 Z',C.gray,2,C.ground);
        if(kind)s+=arrow(140,64,425,195,C.blue,4)+text(430,237,'Pobřeží');else s+=arrow(280,45,280,205,C.blue,5)+text(280,237,'Údolí Rhôny');
        return out([['Vítr',kind?'Bora':'Mistrál'],['Typický mechanismus',kind?'Studený sestup k pobřeží':'Kanálování severního proudění']],svg(s,'Idealizované regionální proudění'),'Regionální větry závisejí na synoptické situaci a reliéfu. Schéma nepředpovídá nárazy.');});
    add('weather-products','Který produkt odpoví na otázku?','Vyber, co potřebuješ zjistit při přípravě.',[Q('question','Potřebuji zjistit',['Pozorování na letišti','Předpověď na letišti','Významný jev v oblasti','Vývoj srážek','Oblačnost v širší oblasti'])],({question})=>{
        const a=[['METAR','Pozorování'],['TAF','Předpověď'],['SIGMET / AIRMET','Výstraha / oblast'],['Radar','Odrazivost srážek'],['Družice','Oblačnost']][question];
        return out([['Vhodný produkt',a[0]]],svg(rect(30,35,500,165,C.sky,C.line)+text(280,87,a[0],C.blue,'middle',26)+text(280,125,a[1],C.ink,'middle',16)+text(280,165,'Zkontroluj čas, platnost a oblast',C.gray),'Meteorologický produkt pro zvolenou informaci'),'Jeden produkt nestačí k úplnému briefingu. Porovnej pozorování, předpověď a výstrahy.');});
    add('metar-build','Sestav modelový fragment METAR','Měň vítr, dohlednost, oblačnost a QNH; čti význam kódu.',[S('w','Směr větru OD',10,360,10,240,'°'),S('v','Rychlost',0,40,1,12,'kt'),S('vis','Dohlednost (model)',1000,10000,1000,7000,'m'),S('n','Pokrytí',1,8,1,6,'/ 8'),S('b','Základna',100,5000,100,3200,'ft AGL'),P()],({w,v,vis,n,b,q})=>{
        const pad=(v,n)=>String(v).padStart(n,'0'),wind=v===0?'00000KT':pad(w,3)+pad(v,2)+'KT',code=wind+' '+(vis>=10000?'9999':pad(vis,4))+' '+cover(n)+pad(b/100,3)+' Q'+pad(q,4);
        return out([['Fragment METAR (fiktivní)',code],['Základna',H(b*.3048)],['Dohlednost',vis===10000?'10 km nebo více':fmt(vis)+' m']],skyCover(n,b),'Fragment bez identifikace, času a dalších skupin. Základna je ve stovkách ft nad letištěm.');},'Syntetický příklad, ne aktuální METAR. CAVOK nelze z těchto vstupů automaticky určit.');
    add('taf-window','TAF: FM, BECMG a TEMPO','Posouvej plánovaný čas v modelovém 24h TAF.',[S('h','Hodiny od 12 UTC dne 24',0,23.5,.5,7,'h')],({h})=>{
        const stage=h<4?0:h<6?1:h<12?2:3,utc=(12+h)%24,day=h<12?24:25,clock=String(Math.floor(utc)).padStart(2,'0')+':'+(utc%1?'30':'00'),a=[['12Z','Základ'],['16–18Z','BECMG'],['18–00Z','TEMPO'],['00Z','FM']];
        return out([['Plánovaný čas',day+'. '+clock+' UTC'],['Interpretace',['Výchozí podmínky','Přechod BECMG; okamžik změny není přesný','Nový základ + možné dočasné odchylky TEMPO','FM: nové převládající podmínky'][stage]]],timeline(a,stage),'Autorský příklad: BECMG 2416/2418, TEMPO 2418/2500, FM250000. TEMPO neznamená nepřetržité podmínky v celém intervalu.');},'Nejde o aktuální předpověď.');
    add('sigmet-volume','Výstraha má i vertikální rozsah','Posuň hladinu a horní/dolní hranice fiktivní výstrahy.',[S('fl','Plánovaná tlaková hladina',0,200,5,65,'FL'),S('low','Spodní hranice',0,150,5,50,'FL'),S('span','Vertikální rozsah',10,100,5,80,'FL')],({fl,low,span})=>{
        const high=low+span,Y=v=>210-v/250*175,s=rect(30,35,500,175,C.sky)+rect(100,Y(high),360,Y(low)-Y(high),'#ead8ce',C.warm)+line(30,Y(fl),530,Y(fl),C.blue,2,'5 4')+plane(280,Y(fl))+text(112,Y(high)+15,'Výstraha',C.warm,'start');
        return out([['Rozsah','FL'+fmt(low)+' až FL'+fmt(high)],['Vertikální porovnání',fl>=low&&fl<=high?'Uvnitř rozsahu':'Mimo zadaný rozsah']],svg(s,'Vertikální rozsah výstrahy a letová hladina'),'Ověř i horizontální polohu, čas, pohyb a skutečnou vertikální strukturu. Mimo zakreslenou vrstvu neznamená bezpečno.');},caveat);
    add('briefing-sequence','Briefing od oblasti k letišti','Procházej vrstvy informací v doporučeném pořadí.',[S('step','Krok',0,4,1,0)],({step})=>{
        const a=[['Synoptika','Fronty'],['Výstrahy','SIGMET'],['Trasa','Oblast'],['Letiště','METAR/TAF'],['Alternativy','Únik']];
        return out([['Krok',a[step][0]],['Zaměření',a[step][1]]],timeline(a,step),['Velké měřítko: tlakové pole, vzduchové hmoty a fronty.','Zjisti významné jevy, jejich pohyb, platnost a rozsah.','Posuď profil trasy, terén, vítr, dohlednost a oblačnost.','Porovnej podmínky odletu, cíle a návratu.','Připrav alternativu, časové rezervy a kritéria včasného přerušení.'][step]);});
    add('winter-valley','Zimní výše a údolní inverze','Měň vrchol schematické mlhy a výšku letadla.',[S('top','Vrchol mlhy AMSL',300,1200,25,650,'m'),S('air','Výška letadla AMSL',300,1500,25,800,'m')],({top,air})=>{
        const y=h=>215-(h-300)/1200*175;
        let s=rect(25,30,510,185,C.sky)+rect(25,y(top),510,215-y(top),'#bbc9d4')+path('M 25 215 L 25 85 L 130 155 L 200 215 L 360 215 L 440 150 L 535 75 L 535 215 Z',C.gray,2,C.ground)+plane(280,y(air));
        return out([['Výška vůči vrcholu mlhy',H(air-top)],['Modelová poloha',air>top?'Nad vrcholem':air<top?'Pod vrcholem':'Na vrcholu']],svg(s,'Mlha vyplňující údolí'),'Poloha nad mlhou neřeší možnost sestupu, únikové trasy ani požadavky VFR. Vrchol je zjednodušeně vodorovný.');},caveat);
    add('convection-day','Odpolední konvekce během návratu','Posouvej čas ve fiktivním dni.',[S('h','Místní čas',7,20,1,15,'h')],({h})=>{
        const stage=h<10?0:h<13?1:h<17?2:3;
        let s=rect(25,25,510,192,C.sky)+rect(25,217,510,12,C.ground)+circle(80+(h-7)*30,60+Math.abs(h-13)*5,17,'#e6ad44');
        if(stage>0)for(let i=0;i<stage+1;i++)s+=cloud(155+i*86,170-i*29,1+stage*.14);
        if(stage>=2)s+=arrow(330,180,330,65,C.warm)+line(375,110,360,185,C.blue,2);
        return out([['Čas',fmt(h)+':00'],['Fiktivní scénář',['Ranní stabilita','Rozvoj Cu','Možná silná konvekce','Možné doznívání / trvající buňky'][stage]]],svg(s,'Ilustrační denní vývoj konvekce'),'Denní ohřev může zvyšovat konvekci. Časové hranice jsou ilustrační. Bouřky mohou vzniknout jindy nebo přetrvat v noci.');},caveat);
    add('terrain-ceiling','Oblačnost, terén a dostupný prostor','Přepočti základnu nad letištěm na AMSL a porovnej ji s terénem.',[S('e','Elevace letiště',0,1500,25,400,'m AMSL'),S('base','Základna nad letištěm',100,4000,100,1500,'ft AGL'),S('terrain','Nejvyšší zadaný terén',0,2500,50,700,'m AMSL')],({e,base,terrain})=>{
        const b=e+base*.3048,gap=b-terrain;
        return out([['Základna AMSL (odhad)',H(b)],['Prostor nad terénem',H(gap)]],levels([['Terén',terrain,C.gray],['Základna',b,C.blue],['Letiště',e,C.warm]],3500,terrain),'Základna na letišti není garancí základny na trase. Záporný rozdíl znamená, že modelová základna leží pod zadaným terénem.');},'Nejde o vyhodnocení VMC, minimální výšky ani povolení letu.');
    add('hot-runway','Teplý den: co se děje s DA?','Porovnej dva dny na stejném letišti a se stejným QNH.',[E(),P(),S('a','První den',-10,25,1,10,'°C'),S('b','Druhý den',10,45,1,30,'°C')],({e,q,a,b})=>{
        const x=da(e,q,a),y=da(e,q,b);
        return out([['DA první den',H(x)],['DA druhý den',H(y)],['Rozdíl druhý − první',H(y-x)]],levels([['První den',x,C.blue],['Druhý den',y,C.warm]],5500,0,'m',-2500),'Vyšší hustotní výška obvykle snižuje stoupavost a zvyšuje potřebnou délku vzletu. Velikost změny určí pouze výkonové údaje konkrétního stroje.');},'Žádný výpočet délky dráhy ani Go/No-Go. Použij AFM/POH.');
    add('briefing-options','Rozhodovací cvičení','Vyber vývoj počasí a porovnej možnosti, ne automatický verdikt.',[Q('change','Změna',['Dohlednost se zhoršuje','Základna klesá','Konvekce sílí','Vítr a DA rostou'])],({change})=>{
        const a=[['Dohlednost','Aktuální pozorování po trase, alternativa a včasný návrat.'],['Základna','AMSL vs terén, vývoj po celé trase, nikoli pouze na letišti.'],['Konvekce','Radar, výstrahy, pohyb buněk, nečekat na přímý vstup do nebezpečí.'],['Výkony','Přepočet podle AFM/POH, složky větru, dráha, konfigurace a rezervy.']][change];
        return out([['Prověř',a[0]]],svg(rect(30,28,500,190,C.sky)+rect(195,45,170,44,'white',C.line)+text(280,72,a[0])+arrow(280,89,150,150,C.blue)+arrow(280,89,410,150,C.blue)+rect(45,150,210,50,'white',C.line)+rect(305,150,210,50,'white',C.line)+text(150,179,'Změnit plán')+text(410,179,'Odložit / ukončit'),'Rozhodovací možnosti'),a[1]);},'Cvičení nevyhodnocuje legálnost ani bezpečnost skutečného letu.');
}
if (typeof module === 'object' && module.exports) module.exports = registerWeatherLabs; else { registerWeatherLabs(window.PplMeteorologyLabs); window.PplMeteorologyLabs.mount(document); }
