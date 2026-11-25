/** Project: PPL Study | Author: Rudolf Klusal | MIT License
 * Shared pure calculation, SVG and native control primitives for local teaching laboratories.
 */
function createMeteorologyEngine() {
    'use strict';
    const models = {};
    const fmt = (n,d=0) => Number(n).toLocaleString('cs-CZ',{minimumFractionDigits:d,maximumFractionDigits:d});
    const H=m=>fmt(m)+' m / '+fmt(m/.3048)+' ft';
    const clamp=(x,a,b)=>Math.max(a,Math.min(b,x));
    const rad=x=>x*Math.PI/180;
    const isa=h=>15-.0065*Math.min(h,11000);
    const pressure=h=>h<=11000?1013.25*Math.pow(1-.0065*h/288.15,5.25588):226.32*Math.exp(-(h-11000)/6341.6);
    const pa=(e,q)=>e+(1013.25-q)*8.23;
    const da=(e,q,t)=>pa(e,q)+36.6*(t-isa(pa(e,q)));
    const lcl=(t,td)=>Math.max(0,(t-td)*122);
    const rh=(t,td)=>100*Math.exp(17.625*td/(243.04+td)-17.625*t/(243.04+t));
    const esc=x=>String(x).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const C={blue:'#166497',warm:'#b85b26',green:'#11734f',gray:'#64748b',red:'#a63b36',purple:'#7254a3',sky:'#eaf4fb',line:'#cbd5e1',ink:'#10253f',ground:'#d6c6a7'};
    const text=(x,y,s,c=C.ink,anchor='middle',size=14)=>'<text x="'+x+'" y="'+y+'" fill="'+c+'" text-anchor="'+anchor+'" font-size="'+size+'">'+esc(s)+'</text>';
    const line=(x,y,X,Y,c=C.gray,w=2,dash='')=>'<line x1="'+x+'" y1="'+y+'" x2="'+X+'" y2="'+Y+'" stroke="'+c+'" stroke-width="'+w+'" stroke-linecap="round"'+(dash?' stroke-dasharray="'+dash+'"':'')+'/>';
    const rect=(x,y,w,h,fill,stroke='none',r=5)=>'<rect x="'+x+'" y="'+y+'" width="'+Math.max(0,w)+'" height="'+Math.max(0,h)+'" fill="'+fill+'" stroke="'+stroke+'" rx="'+r+'"/>';
    const circle=(x,y,r,fill,stroke='none',w=1)=>'<circle cx="'+x+'" cy="'+y+'" r="'+r+'" fill="'+fill+'" stroke="'+stroke+'" stroke-width="'+w+'"/>';
    const path=(d,c=C.blue,w=3,fill='none')=>'<path d="'+d+'" stroke="'+c+'" stroke-width="'+w+'" fill="'+fill+'" stroke-linejoin="round" stroke-linecap="round"/>';
    const arrow=(x,y,X,Y,c=C.blue,w=3)=>{if(Math.hypot(X-x,Y-y)<.01)return circle(x,y,3,c);const a=Math.atan2(Y-y,X-x),s=8;return line(x,y,X,Y,c,w)+path('M '+(X-s*Math.cos(a-.5))+' '+(Y-s*Math.sin(a-.5))+' L '+X+' '+Y+' L '+(X-s*Math.cos(a+.5))+' '+(Y-s*Math.sin(a+.5)),c,w);};
    const cloud=(x,y,k=1,c='#bdcfdc')=>'<g transform="translate('+x+' '+y+') scale('+k+')">'+circle(-20,0,15,c)+circle(0,-10,23,c)+circle(24,0,17,c)+rect(-35,0,75,15,c,'none',8)+'</g>';
    const plane=(x,y,a=0)=>'<g transform="translate('+x+' '+y+') rotate('+a+')"><path d="M 20 0 L 5 4 L -6 17 L -11 17 L -6 4 L -20 5 L -20 -5 L -6 -4 L -11 -17 L -6 -17 L 5 -4 Z" fill="'+C.ink+'"/></g>';
    const svg=(s,label)=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 560 250" role="img" aria-label="'+esc(label)+'"><title>'+esc(label)+'</title>'+s+'</svg>';
    const out=(metrics,visual,explanation)=>({metrics,visual,explanation});
    const S=(key,label,min,max,step,value,unit='')=>({key,label,min,max,step,value,unit});
    const Q=(key,label,options,value=0)=>({key,label,options,value});
    const T=()=>S('t','Teplota T',-25,40,1,22,'°C');
    const D=()=>S('td','Rosný bod Td',-30,35,1,12,'°C');
    const E=()=>S('e','Elevace letiště',0,2500,50,450,'m AMSL');
    const P=()=>S('q','QNH',970,1050,1,1003,'hPa');
    const add=(id,title,task,controls,compute,note='')=>{if(models[id])throw Error('Duplicate lab '+id);models[id]={title,task,controls,compute,note};};
    const caveat='Výukový model, nikoli provozní výpočet, letové minimum nebo předpověď.';
    function plot(series,xmin,xmax,ymin,ymax,xlabel,ylabel,marker) {
        const X=x=>65+470*(x-xmin)/(xmax-xmin),Y=y=>204-171*(y-ymin)/(ymax-ymin);
        let s=rect(65,33,470,171,'#f8fafc');
        for(let i=0;i<=4;i++){const xx=xmin+(xmax-xmin)*i/4,yy=ymin+(ymax-ymin)*i/4;s+=line(X(xx),33,X(xx),204,C.line,1)+line(65,Y(yy),535,Y(yy),C.line,1)+text(X(xx),221,fmt(xx,Number.isInteger(xx)?0:1),C.gray,'middle',12)+text(57,Y(yy)+4,fmt(yy,Number.isInteger(yy)?0:1),C.gray,'end',12);}
        s+=line(65,204,535,204)+line(65,33,65,204)+text(300,242,xlabel)+text(65,20,ylabel,C.gray,'start',12);
        for(const [i,a] of series.entries()){s+='<polyline points="'+a.points.map(p=>X(p[0])+','+Y(p[1])).join(' ')+'" fill="none" stroke="'+a.color+'" stroke-width="3" stroke-linejoin="round"/>';s+=line(270+i*130,16,286+i*130,16,a.color,3)+text(291+i*130,20,a.name,a.color,'start',12);}
        if(marker){const [x,y]=marker;if(x>=xmin&&x<=xmax&&y>=ymin&&y<=ymax)s+=circle(X(x),Y(y),5,C.ink,'white',2);}
        return svg(s,xlabel+' / '+ylabel);
    }
    function levels(items,max,terrain=0,unit='m',min=0) {
        const Y=h=>209-174*clamp((h-min)/(max-min),0,1);
        let s=rect(38,35,484,174,C.sky)+rect(38,Y(terrain),484,209-Y(terrain),C.ground,'none',0)+line(38,209,522,209,C.gray)+text(38,232,fmt(min)+' '+unit,C.gray,'start')+text(38,25,fmt(max)+' '+unit,C.gray,'start',12);
        for(const [i,a] of items.entries()){const y=Y(a[1]),x=46+i*(440/Math.max(1,items.length));s+=line(38,y,522,y,a[2]||C.blue,2,'5 4')+circle(x,y,5,a[2]||C.blue)+text(x,clamp(y-10,17,198),a[0],a[2]||C.blue,'start',12);}
        return svg(s,'Svislý řez: '+items.map(a=>a[0]).join(', '));
    }
    function bars(items,max,unit=''){
        let s='';const row=192/items.length;
        items.forEach((a,i)=>{const y=25+i*row;s+=text(15,y+15,a[0],C.ink,'start',13)+rect(170,y,294,22,'#e8eef3')+rect(170,y,294*clamp(Math.abs(a[1])/max,0,1),22,a[2]||C.blue)+text(550,y+16,fmt(a[1],a[3]||0)+' '+unit,C.ink,'end',12);});
        return svg(s,'Porovnání velikostí: '+items.map(a=>a[0]).join(', '));
    }
    function timeline(stages,active){
        const x=i=>55+i*(450/Math.max(1,stages.length-1));let s=line(55,120,505,120,C.line,5);
        stages.forEach((a,i)=>{const xx=x(i);s+=circle(xx,120,i===active?12:7,i===active?C.blue:C.line)+text(xx,96,a[0],i===active?C.blue:C.gray,'middle',12)+text(xx,150,a[1],C.ink,'middle',12);});
        return svg(s,'Časová osa: '+stages.map(a=>a[0]).join(', '));
    }
    function skyCover(n,base=1200){
        let s=rect(28,20,504,190,C.sky)+rect(28,209,504,18,C.ground);
        for(let i=0;i<8;i++){const x=65+i*61;s+=rect(x-25,35,50,31,i<n?C.blue:'white',C.line,7);if(i<n)s+=cloud(x,180-clamp(base,0,5000)*.024,.58);}
        return svg(s,n+' z osmi částí oblohy zakryto');
    }
    const cover=n=>n===0?'Bez vrstvy':n<=2?'FEW':n<=4?'SCT':n<=7?'BKN':'OVC';
    function compass(r,w,v){
        const cx=280,cy=125,p=(a,d)=>[cx+Math.sin(rad(a))*d,cy-Math.cos(rad(a))*d];let s=circle(cx,cy,85,'#f5f9fc',C.line,2);
        for(let a=0;a<360;a+=30){const [x,y]=p(a,85),[u,z]=p(a,78);s+=line(x,y,u,z,C.gray,1);}
        for(const [a,l] of [[0,'N'],[90,'E'],[180,'S'],[270,'W']]){const [x,y]=p(a,103);s+=text(x,y+4,l);}
        const [x,y]=p(r+180,71),[X,Y]=p(r,71),[wx,wy]=p(w,Math.min(77,15+v*1.5));
        s+=line(x,y,X,Y,C.gray,14)+line(x,y,X,Y,'white',1,'6 5')+(v?arrow(wx,wy,cx,cy,C.blue,4):'')+plane(cx,cy,r-90)+text(18,28,'Dráha '+fmt(r)+'°',C.gray,'start')+text(18,48,'Vítr OD '+fmt(w)+'°',C.blue,'start');
        return svg(s,'Dráha a směr proudění v půdorysu');
    }
    function mount(root){
        for(const host of root.querySelectorAll('[data-meteorology-applet]')){
            const model=models[host.dataset.meteorologyApplet];if(!model)continue;
            try{
                const doc=host.ownerDocument,make=(tag,cls,value)=>{const el=doc.createElement(tag);if(cls)el.className=cls;if(value!==undefined)el.textContent=value;return el;};
                const title=make('h3','meteo-lab-title',model.title),header=make('div','meteo-lab-header'),headings=make('div'),reset=make('button','meteo-lab-reset','Obnovit');
                reset.type='button';headings.append(make('span','meteo-lab-kicker','Interaktivní laboratoř'),title);header.append(headings,reset);
                const controls=make('div','meteo-lab-controls'),metrics=make('dl','meteo-lab-metrics'),visual=make('div','meteo-lab-visual'),explanation=make('p','meteo-lab-explanation'),announce=make('span','meteo-lab-sr');
                announce.setAttribute('aria-live','polite');
                const state={},bindings=[];
                function render(){
                    const result=model.compute(state),fragment=doc.createDocumentFragment();
                    for(const [key,value] of result.metrics){const row=make('div','meteo-lab-metric');row.append(make('dt','',key),make('dd','',value));fragment.append(row);}
                    metrics.replaceChildren(fragment);
                    // SVG is generated from local numeric state and escaped, project-owned labels only.
                    visual.innerHTML=result.visual;explanation.textContent=result.explanation;return result;
                }
                for(const c of model.controls){
                    const label=make('label','meteo-lab-control'),caption=make('span','meteo-lab-control-label'),value=make('span','meteo-lab-value'),input=make(c.options?'select':'input');
                    caption.append(make('span','',c.label),value);
                    if(c.options)c.options.forEach((s,i)=>{const op=make('option','',s);op.value=String(i);input.append(op);});
                    else{input.type='range';input.min=String(c.min);input.max=String(c.max);input.step=String(c.step);}
                    input.setAttribute('aria-label',c.label);
                    function update(v){state[c.key]=v;input.value=String(v);if(!c.options){value.textContent=Number(v).toLocaleString('cs-CZ',{maximumFractionDigits:2})+(c.unit?' '+c.unit:'');input.setAttribute('aria-valuetext',value.textContent);}}
                    update(c.value);bindings.push(()=>update(c.value));
                    input.addEventListener('input',()=>{update(Number(input.value));render();});
                    input.addEventListener('change',()=>{update(Number(input.value));announce.textContent=render().metrics.map(m=>m.join(': ')).join('; ');});
                    label.append(caption,input);controls.append(label);
                }
                reset.addEventListener('click',()=>{bindings.forEach(fn=>fn());render();announce.textContent='Výchozí hodnoty obnoveny.';});
                host.append(header,make('p','meteo-lab-task',model.task),controls,metrics,visual,explanation);
                if(model.note)host.append(make('p','meteo-lab-note',model.note));
                host.append(announce);render();host.hidden=false;
            }catch(error){host.hidden=true;console.error('Meteorology laboratory failed:',host.dataset.meteorologyApplet,error);}
        }
    }
    return {models,add,fmt,H,clamp,rad,isa,pressure,pa,da,lcl,rh,C,text,line,rect,circle,path,arrow,cloud,plane,svg,out,S,Q,T,D,E,P,caveat,plot,levels,bars,timeline,skyCover,cover,compass,mount};
}
if (typeof module === 'object' && module.exports) module.exports = createMeteorologyEngine; else window.PplMeteorologyLabs = createMeteorologyEngine();
