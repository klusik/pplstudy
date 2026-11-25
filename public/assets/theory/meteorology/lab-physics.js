/** Project: PPL Study | Author: Rudolf Klusal | MIT License
 * Physical meteorology, pressure, cloud and wind laboratories.
 */
function registerPhysicsLabs(L) {
    'use strict';
    const {add,fmt,H,clamp,rad,isa,pressure,pa,da,lcl,rh,C,text,line,rect,circle,path,arrow,cloud,plane,svg,out,S,Q,T,D,E,P,caveat,plot,levels,bars,timeline,skyCover,cover,compass}=L;
    add('isa-column','Cesta standardní atmosférou','Zvedni letadlo nad tropopauzu a sleduj teplotu, tlak a hustotu.',[S('h','Výška',0,20000,250,1500,'m')],({h})=>{
        const t=isa(h),p=pressure(h),rho=p*100/(287.05*(t+273.15));
        return out([['Výška',H(h)],['ISA teplota',fmt(t,1)+' °C'],['ISA tlak',fmt(p,1)+' hPa'],['Hustota',fmt(rho,3)+' kg/m³']],levels([['Tropopauza ISA',11000,C.purple],['Letadlo',h,C.blue]],20000),h<11000?'V troposféře ISA klesá teplota o 6,5 °C/km.':'V tomto rozsahu ISA je teplota nad 11 km konstantní; tlak dále klesá.');});
    add('isa-deviation','Odchylka od ISA','Změň výšku a skutečnou OAT, sleduj odchylku od standardu.',[S('h','Výška',0,8000,100,1500,'m'),S('t','OAT',-55,45,1,10,'°C')],({h,t})=>out([['T ISA',fmt(isa(h),1)+' °C'],['ISA odchylka',fmt(t-isa(h),1)+' °C']],plot([{name:'ISA',color:C.blue,points:[[15,0],[isa(8000),8]]}],-60,50,0,8,'Teplota (°C)','Výška (km)',[t,h/1000]),'Bod ukazuje skutečnou OAT v dané výšce, nikoli celý skutečný profil.'));
    add('air-density','Hustota suchého vzduchu','Měň tlak a teplotu nezávisle. Porovnej hustotu s ISA u moře.',[S('p','Tlak v místě',500,1050,5,900,'hPa'),T()],({p,t})=>{
        const rho=100*p/(287.05*(t+273.15));let s=rect(24,22,512,200,C.sky,C.line);
        for(let i=0;i<Math.round(rho/1.225*90);i++)s+=circle(39+(i%18)*27,39+Math.floor(i/18)*27,3.5,C.blue);
        return out([['Hustota',fmt(rho,3)+' kg/m³'],['Vůči ISA u moře',fmt(100*rho/1.225,1)+' %']],svg(s,'Schematická hustota částic v objemu'),'Při stejném tlaku teplejší vzduch řídne. Při stejné teplotě roste hustota s tlakem. ρ = p/(R·T).');});
    add('inversion-profile','Teplotní inverze','Nastav gradient spodních 500 m.',[S('t','Teplota u země',-15,30,1,5,'°C'),S('g','Změna T s výškou',-15,15,1,10,'°C/km')],({t,g})=>out([['T v 500 m',fmt(t+g*.5,1)+' °C'],['Profil',g>0?'Inverze':g===0?'Izotermie':'Pokles teploty']],plot([{name:'Profil',color:C.warm,points:[[t,0],[t+g*.5,.5],[t+g*.5-6.5,1.5]]}],-35,45,0,2,'Teplota (°C)','Výška (km)'),g>0?'Teplota s výškou roste, vrstva omezuje konvekci.':'Inverze nastává až při kladné změně T s výškou.'));
    add('altimeter-settings','Jeden let, tři nastavení výškoměru','Přepínej QNH, QFE a STD při stejné výšce.',[E(),P(),S('h','Výška nad letištěm',0,3000,50,600,'m'),Q('mode','Nastavení',['QNH','QFE','STD'])],({e,q,h,mode})=>{
        const vals=[e+h,h,pa(e+h,q)];
        return out([['Indikace (odhad)',H(vals[mode])],['Nastavený tlak',fmt([q,q-e/8.23,1013.25][mode],2)+' hPa']],levels([['Letiště',e,C.gray],['Letadlo',e+h,C.blue]],6000,e),'QNH odkazuje k moři, QFE k letišti a STD k tlakové referenci 1013,25 hPa. Lineární tlakový odhad bez teplotní chyby.');},caveat);
    add('pressure-altitude','Tlaková výška PA','Změň elevaci a QNH. Nižší QNH zvyšuje PA.',[E(),P()],({e,q})=>out([['PA (odhad)',H(pa(e,q))],['PA − elevace',H(pa(e,q)-e)]],levels([['Elevace',e,C.gray],['PA',pa(e,q),C.blue]],3200,0,'m',-500),'PA ≈ elevace + (1013,25 − QNH) × 8,23 m/hPa.'),caveat);
    add('pressure-travel','High to low, look out below','Ponech staré nastavení a změň místní QNH.',[S('old','Nastavené QNH',970,1050,1,1013,'hPa'),S('actual','Místní QNH',970,1050,1,993,'hPa'),S('ind','Indikovaná AMSL',500,3000,50,1500,'m')],({old,actual,ind})=>{
        const err=(old-actual)*8.23,real=ind-err;
        return out([['Skutečná AMSL (odhad)',H(real)],['Indikace − skutečnost',H(err)]],levels([['Indikace',ind,C.gray],['Skutečnost',real,C.blue]],4000,0,'m',-250),'Při letu do nižšího tlaku bez přenastavení je skutečná výška nižší než indikovaná.');},caveat);
    add('cold-altimetry','Hot to cold, look out below','Měň výšku nad referencí a průměrnou teplotní odchylku.',[S('h','Indikovaná výška nad referencí',0,3000,50,1000,'m'),S('d','Průměrná odchylka od ISA',-35,25,1,-15,'°C')],({h,d})=>{
        const delta=h*.004*d;
        return out([['Skutečná výška (odhad)',H(h+delta)],['Skutečná − indikovaná',H(delta)]],levels([['Indikace',h,C.gray],['Skutečnost',h+delta,C.blue]],4000),'Odhad 0,4 % výšky nad referencí na 1 °C. Rozhoduje průměrná odchylka vrstvy, nikoli izolovaná OAT.');},caveat);
    add('density-altitude','Hustotní výška DA krok za krokem','Změň letiště, QNH a OAT; sleduj navazující výpočty.',[E(),P(),T()],({e,q,t})=>{
        const p=pa(e,q),std=isa(p),d=da(e,q,t);
        return out([['PA',H(p)],['T ISA v PA',fmt(std,1)+' °C'],['OAT − ISA',fmt(t-std,1)+' °C'],['DA (odhad)',H(d)]],levels([['Elevace',e,C.gray],['PA',p,C.blue],['DA',d,C.warm]],5000,0,'m',-2500),'DA ≈ PA + 36,6 × (OAT − T ISA) v metrech. Vyšší DA obvykle zhoršuje výkony.');},'Přibližný model suchého vzduchu. Pro skutečný výkon použij AFM/POH.');
    add('water-state','Skupenství a latentní teplo','Vyber děj a sleduj tok energie.',[Q('process','Děj',['Tání','Tuhnutí','Vypařování','Kondenzace','Sublimace','Desublimace'],3)],({process})=>{
        const a=[['Led','Voda',1],['Voda','Led',-1],['Voda','Pára',1],['Pára','Voda',-1],['Led','Pára',1],['Pára','Led',-1]][process];
        return out([['Přeměna',a[0]+' → '+a[1]],['Latentní teplo',a[2]>0?'Pohlcováno':'Uvolňováno']],svg(rect(40,75,170,95,C.sky,C.line)+rect(350,75,170,95,'#f9eee6',C.line)+text(125,127,a[0])+text(435,127,a[1])+arrow(220,120,340,120,a[2]>0?C.warm:C.blue)+text(280,67,a[2]>0?'Energie vstupuje':'Energie se uvolňuje'),'Přeměna skupenství a latentní teplo'),'Při kondenzaci se uvolňuje latentní teplo, při vypařování se spotřebovává.');});
    add('humidity-dew','Rosný bod a relativní vlhkost','Zvyšuj T při neměnném Td.',[T(),D()],({t,td})=>{
        const d=Math.min(t,td),r=rh(t,d);
        return out([['Efektivní Td',fmt(d)+' °C'],['Relativní vlhkost',fmt(r,1)+' %'],['Deficit',fmt(t-d)+' °C']],bars([['RH',r,C.blue,1],['Do nasycení',100-r,C.gray,1]],100,'%'),td>t?'Td omezen na T; přebytečná pára v modelu kondenzuje.':'Při stejném Td teplejší vzduch dosahuje nižší RH. Magnusův odhad vůči kapalné vodě.');});
    add('parcel-lapse','Suchá a nasycená adiabata','Posouvej částici a nastav výšku kondenzace.',[S('h','Výstup',0,4000,100,1800,'m'),S('l','LCL',0,2500,100,1000,'m'),S('t','Počáteční T',0,35,1,22,'°C')],({h,l,t})=>{
        const p=z=>t-.0098*Math.min(z,l)-.006*Math.max(0,z-l);
        return out([['Teplota částice',fmt(p(h),1)+' °C'],['Stav',h<l?'Nenasycená':'Nasycená (model)']],plot([{name:'Částice',color:C.warm,points:[[t,0],[p(l),l/1000],[p(4000),4]]},{name:'Suchá',color:C.gray,points:[[t,0],[t-39.2,4]]}],-45,40,0,4,'Teplota (°C)','Výška (km)',[p(h),h/1000]),'DALR ≈ 9,8 °C/km; SALR zde pevně 6 °C/km, ve skutečnosti se mění.');});
    add('cloud-base','Jak vysoko vznikne základna Cu?','Měň teplotu a Td u povrchu.',[T(),D()],({t,td})=>{
        const b=lcl(t,td);
        return out([['Deficit',fmt(Math.max(0,t-td))+' °C'],['LCL (odhad)',H(b)]],levels([['LCL',b,C.blue]],9000),'Přibližně 122 m / 400 ft AGL na 1 °C deficitu. Td > T je omezeno na nasycení. Skutečná základna závisí na profilu a zdvihu.');},caveat);
    add('buoyancy','Která částice bude stoupat?','Porovnej gradient prostředí a částice po zdvihu.',[S('g','Pokles T prostředí',-5,14,.5,8,'°C/km'),S('h','Zdvih',0,3000,100,1000,'m'),Q('wet','Částice',['Suchá (9,8 °C/km)','Nasycená (6 °C/km)'])],({g,h,wet})=>{
        const rate=wet?6:9.8,p=20-rate*h/1000,e=20-g*h/1000;
        return out([['Částice',fmt(p,1)+' °C'],['Okolí',fmt(e,1)+' °C'],['Rozdíl',fmt(p-e,1)+' °C']],plot([{name:'Okolí',color:C.blue,points:[[20,0],[20-g*3,3]]},{name:'Částice',color:C.warm,points:[[20,0],[20-rate*3,3]]}],-25,40,0,3,'Teplota (°C)','Výška (km)',[p,h/1000]),p>e?'Teplejší částice má při stejném tlaku kladný vztlak.':p<e?'Chladnější částice má záporný vztlak.':'Částice je v tomto modelu teplotně neutrální. Vlhkost zanedbána.');});
    add('thermal-trigger','Termika pod inverzí','Zvyšuj přehřátí částice a sílu stabilní vrstvy.',[S('heat','Přehřátí u povrchu',0,10,.5,3,'°C'),S('cap','Síla inverze v 0,5 km',0,10,.5,4,'°C')],({heat,cap})=>{
        const p=20+heat-4.9,e=20-3.25+cap;
        return out([['T částice u víka',fmt(p,1)+' °C'],['T okolí u víka',fmt(e,1)+' °C'],['Rozdíl',fmt(p-e,1)+' °C']],plot([{name:'Okolí',color:C.blue,points:[[20,0],[18.05,.3],[e,.5],[e-6.5,1.5]]},{name:'Částice',color:C.warm,points:[[20+heat,0],[20+heat-14.7,1.5]]}],0,35,0,1.5,'Teplota (°C)','Výška (km)',[p,.5]),p>e?'Částice je u víka teplejší; v modelu má kladný vztlak.':'Stabilní vrstva brzdí zdvih. V reálu rozhoduje celý profil a dynamika.');});
    add('cape-cin','CAPE a CIN: plocha vztlaku','Nastav záporný a kladný rozdíl teplot a hloubku vrstvy.',[S('cold','Záporný rozdíl u země',0,6,.5,2,'°C'),S('warm','Maximum kladného rozdílu',0,10,.5,4,'°C'),S('depth','Hloubka kladné vrstvy',500,6000,250,3000,'m')],({cold,warm,depth})=>{
        const cin=9.81*cold*500/2/280,cape=9.81*warm*depth/2/280;
        return out([['CIN (velikost)',fmt(cin)+' J/kg'],['CAPE (odhad)',fmt(cape)+' J/kg']],plot([{name:'ΔT',color:C.warm,points:[[-cold,0],[0,.5],[warm,.5+depth/2000],[0,.5+depth/1000]]},{name:'Rovnováha',color:C.gray,points:[[0,0],[0,7]]}],-7,11,0,7,'T částice − okolí (°C)','Výška (km)'),'Trojúhelníkový profil, referenční T 280 K a zanedbaná vlhkost. Skutečný výpočet využívá virtuální teploty a sondáž.');},caveat);
    add('sounding','Sondáž: T a Td','Změň profil teploty a vlhkosti.',[S('t','T povrchu',0,35,1,20,'°C'),S('dep','Deficit u země',0,20,1,8,'°C'),S('g','Pokles T',2,10,.5,6.5,'°C/km')],({t,dep,g})=>{
        const pts=Array.from({length:7},(_,i)=>{const h=i*.5,T=t-g*h;return [T,Math.min(T,t-dep-2*h),h];});
        return out([['T ve 3 km',fmt(pts[6][0],1)+' °C'],['Td ve 3 km',fmt(pts[6][1],1)+' °C']],plot([{name:'T',color:C.warm,points:pts.map(a=>[a[0],a[2]])},{name:'Td',color:C.blue,points:pts.map(a=>[a[1],a[2]])}],-35,40,0,3,'Teplota (°C)','Výška (km)'),'Sblížení T a Td značí vysokou vlhkost. Schematický profil, nikoli skutečný Skew-T.');});
    add('cloud-genera','Atlas oblaků v řezu','Vyber rod a porovnej jeho typické patro a podobu.',[Q('genus','Rod oblaku',['Ci','Cc','Cs','Ac','As','Ns','Sc','St','Cu','Cb'],8)],({genus})=>{
        const a=[['Cirrus',7,10],['Cirrocumulus',6,9],['Cirrostratus',6,10],['Altocumulus',3,6],['Altostratus',2,6],['Nimbostratus',.5,5],['Stratocumulus',.5,2],['Stratus',.1,1],['Cumulus',.7,3],['Cumulonimbus',.6,11]][genus],y=h=>220-h*17;
        let s=rect(45,18,480,202,C.sky)+rect(45,220,480,12,C.ground);
        for(let h=0;h<=10;h+=2)s+=line(45,y(h),525,y(h),C.line,1,'4 5')+text(38,y(h)+4,h+' km',C.gray,'end',10);
        if(genus===0){for(let i=0;i<5;i++)s+=path('M '+(130+i*62)+' '+y(8)+' q 35 -20 55 -5',C.gray,2);}
        else if([2,4,5,7].includes(genus))s+=rect(90,y(a[2]),380,Math.max(12,y(a[1])-y(a[2])),'#a9c0d0',C.gray,8);
        else if(genus>=8){for(let h=a[1]+.6;h<a[2]+.2;h+=1)s+=cloud(280,y(h),genus===9?1.4:1.1);if(genus===9)s+=rect(180,y(11),220,15,'#a9c0d0','none',12);}
        else for(let i=0;i<6;i++)s+=cloud(95+i*68,y((a[1]+a[2])/2),.75);
        return out([['Rod',a[0]],['Ilustrační patro',fmt(a[1],1)+'–'+fmt(a[2],1)+' km']],svg(s,'Schematický oblak '+a[0]),'Výšky jsou ilustrační pro mírné šířky. Rod se určuje také podle vzhledu, struktury a vývoje.');});
    add('oktas','Kolik oblohy pokrývají oblaka?','Změň počet osmin a základnu.',[S('n','Pokrytí',0,8,1,5,'/ 8'),S('base','Základna',100,4000,100,1000,'ft AGL')],({n,base})=>out([['Množství',cover(n)],['Pokrytí',fmt(n/8*100,1)+' %'],['Základna',n?H(base*.3048):'Bez vrstvy']],skyCover(n,base),'BKN (5–7) a OVC (8) mohou tvořit ceiling. Nejde o automatické kódování celé zprávy METAR.'));
    add('ceiling-layers','Dvě vrstvy, která je ceiling?','Změň množství a základny dvou vrstev.',[S('n1','Nižší vrstva',0,8,1,3,'/ 8'),S('h1','Nižší základna',100,2500,100,800,'ft AGL'),S('n2','Vyšší vrstva',0,8,1,7,'/ 8'),S('gap','Rozestup vrstev',100,3000,100,1400,'ft')],({n1,h1,n2,gap})=>{
        const h2=h1+gap,b=n1>=5?h1:n2>=5?h2:null;
        return out([['Nižší',n1?cover(n1)+' '+fmt(h1)+' ft':'Bez vrstvy'],['Vyšší',n2?cover(n2)+' '+fmt(h2)+' ft':'Bez vrstvy'],['Ceiling',b===null?'Žádná z vrstev':H(b*.3048)]],levels([...(n1?[['Nižší '+cover(n1),h1,C.gray]]:[]),...(n2?[['Vyšší '+cover(n2),h2,C.blue]]:[])],6000,0,'ft'),'Ceiling je nejnižší základna BKN/OVC nebo vertikální dohlednost při zakryté obloze (zde nemodelována).');});
    add('rain-growth','Růst srážkových částic','Přepni mechanismus a fázi růstu.',[Q('kind','Mechanismus',['Koalescence kapek','Ledový proces']),S('stage','Fáze růstu',0,4,1,2)],({kind,stage})=>{
        let s=rect(25,22,510,130,C.sky)+cloud(280,55,2.6);
        for(let i=0;i<9;i++)s+=circle(65+i*51,92+(i%3)*15,2+stage*(i%3+1)*.8,kind?C.purple:C.blue);
        s+=arrow(280,150,280,220,C.blue)+text(298,190,stage>=3?'Vypadávání':'Růst',C.ink,'start');
        return out([['Mechanismus',kind?'Růst ledových částic':'Splývání kapek'],['Fáze',fmt(stage+1)+' / 5']],svg(s,'Schematický růst částic'),'V teplém oblaku se kapky slučují; ve smíšeném oblaku může růst led na úkor přechlazených kapek. Velikosti nejsou v měřítku.');});
    add('pressure-systems','Cyklona a anticyklona','Přepni útvar a polokouli.',[Q('kind','Útvar',['Níže','Výše']),Q('hem','Polokoule',['Severní','Jižní'])],({kind,hem})=>{
        const sign=(kind?1:-1)*(hem?-1:1);let s='';
        for(const r of [36,61,85])s+=circle(205,125,r,'none',C.line,2);
        for(let a=0;a<360;a+=60){const b=rad(a),c=rad(a+sign*27),r2=kind?85:60;s+=arrow(205+74*Math.cos(b),125+74*Math.sin(b),205+r2*Math.cos(c),125+r2*Math.sin(c),C.blue);}
        s+=text(205,130,kind?'H':'L',C.ink,'middle',24)+arrow(420,kind?70:185,420,kind?185:70,C.warm,4)+text(420,218,kind?'Subsidence':'Výstup');
        return out([['Rotace',sign>0?'Po směru hodin':'Proti směru hodin'],['Přízemní tok',kind?'Divergence':'Konvergence']],svg(s,'Tlakový útvar, přízemní a vertikální proudění'),'Schéma třením ovlivněného přízemního proudění. Skutečné pole závisí na rozložení tlaku.');});
    add('high-season','Výše v létě a zimě','Porovnej typický den a noc.',[Q('season','Období',['Léto','Zima'],1),S('hour','Místní čas',0,23,1,6,'h')],({season,hour})=>{
        const day=hour>=8&&hour<=17;let s=rect(30,30,500,180,day?C.sky:'#d5e0ec')+rect(30,210,500,15,C.ground)+circle(440,58,19,day?'#e6ad44':'#f8fafc');
        if(season||!day)s+=rect(30,165,500,45,'#b5c5d5')+text(280,190,'Možná stabilní vrstva / mlha');else for(let x=110;x<460;x+=110)s+=arrow(x,200,x,80,C.warm);
        return out([['Scénář',season?'Zimní výše':'Letní výše'],['Typická tendence',season?'Inverze může přetrvat':day?'Denní konvekce':'Radiační ochlazování']],svg(s,'Typický scénář tlakové výše'),'Zjednodušený den/noc scénář; výše sama nezaručuje bezoblačné počasí.');});
    add('thickness','Tloušťka mezi tlakovými plochami','Změň průměrnou virtuální T a horní tlak.',[S('t','Průměrná virtuální T',-40,30,1,0,'°C'),S('p','Horní tlak',300,900,50,500,'hPa')],({t,p})=>{
        const dz=287.05*(t+273.15)/9.80665*Math.log(1000/p);
        return out([['Dolní tlak','1000 hPa'],['Tloušťka',H(dz)]],levels([['1000 hPa',0,C.gray],[fmt(p)+' hPa',dz,C.blue]],12000),'Hypsometrická rovnice: Δz = R·T̄v/g·ln(p₁/p₂). Teplejší vrstva je mezi stejnými tlaky tlustší.');});
    add('pressure-gradient','Tlakový gradient a geostrofický vítr','Změň rozdíl tlaku, vzdálenost a šířku.',[S('dp','Rozdíl tlaku',1,12,1,4,'hPa'),S('dist','Vzdálenost',100,1000,50,400,'km'),S('lat','Zeměpisná šířka',25,70,5,50,'°')],({dp,dist,lat})=>{
        const a=dp*100/(dist*1000*1.225),v=a/(2*7.292115e-5*Math.sin(rad(lat)));let s='';
        for(let i=0;i<5;i++){const x=100+i*90;s+=line(x,40,x,205,C.line,2)+text(x,25,fmt(1000+i*dp/4,1));}
        s+=arrow(410,100,150,100,C.warm)+arrow(280,195,280,65,C.blue)+text(280,235,'Geostrofický vítr podél izobar');
        return out([['Gradient',fmt(dp/dist*100,2)+' hPa / 100 km'],['Geostrofický vítr (model)',fmt(v*1.94384,1)+' kt']],svg(s,'Rovnováha tlakového gradientu a Coriolisovy síly'),'Rovné izobary, severní polokoule, konstantní hustota 1,225 kg/m³, bez tření. Šipky nejsou v měřítku.');},caveat);
    add('surface-wind','Přízemní vítr k nižšímu tlaku','Změň odklon a pokles rychlosti v důsledku tření.',[S('angle','Odklon k níži',0,45,5,25,'°'),S('v','Výškový vítr',5,50,1,25,'kt'),S('loss','Snížení rychlosti',0,70,5,35,'%')],({angle,v,loss})=>{
        let s='';for(let y=55;y<220;y+=42)s+=line(45,y,515,y,C.line,2);
        s+=text(60,25,'Nižší tlak',C.gray,'start')+arrow(110,150,430,150,C.gray)+arrow(110,150,110+320*(1-loss/100)*Math.cos(rad(angle)),150-320*(1-loss/100)*Math.sin(rad(angle)),C.blue,4);
        return out([['Přízemní rychlost (model)',fmt(v*(1-loss/100),1)+' kt'],['Odklon',fmt(angle)+'°']],svg(s,'Odklon přízemního větru přes izobary'),'Úhel a pokles rychlosti jsou samostatné výukové vstupy, nikoli výpočet z drsnosti skutečného povrchu.');});
    add('wind-components','Boční, čelní a zadní vítr','Otáčej dráhou i směrem větru.',[S('r','Směr dráhy',10,360,10,270,'°'),S('w','Směr větru OD',0,360,10,240,'°'),S('v','Rychlost',0,50,1,16,'kt')],({r,w,v})=>{
        const d=rad(w-r),head=v*Math.cos(d),side=v*Math.sin(d);
        return out([[head>=-1e-9?'Čelní':'Zadní',fmt(Math.abs(head),1)+' kt'],['Boční '+(Math.abs(side)<1e-9?'':side>0?'zprava':'zleva'),fmt(Math.abs(side),1)+' kt']],compass(r,w,v),'Směry musí mít stejnou referenci. Boční složka = V·sin Δ; čelní = V·cos Δ.');},'Výpočet není schválením vzletu či přistání. Ověř AFM/POH a aktuální vítr.');
    add('breeze-cycle','Denní cyklus brízy','Změň teplotní kontrast pevniny a vody.',[S('d','T pevniny − T vody',-15,15,1,8,'°C')],({d})=>{
        let s=rect(20,172,260,45,'#aacfe5')+rect(280,172,260,45,C.ground)+text(145,239,'Voda')+text(410,239,'Pevnina');
        if(d!==0){const l=d>0?145:410,r=d>0?410:145;s+=arrow(l,160,r,160,C.blue)+arrow(r,150,r,60,C.warm)+arrow(r,50,l,50,C.blue)+arrow(l,60,l,145,C.blue);}
        return out([['Typický přízemní tok',d>0?'Z vody na pevninu':d<0?'Z pevniny na vodu':'Bez kontrastu v modelu']],svg(s,'Místní cirkulace poháněná teplotním kontrastem'),'Brízu může výrazně měnit okolní synoptický vítr a členitost pobřeží.');});
}
if (typeof module === 'object' && module.exports) module.exports = registerPhysicsLabs; else registerPhysicsLabs(window.PplMeteorologyLabs);
