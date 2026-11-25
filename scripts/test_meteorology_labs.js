/** Project: PPL Study | Author: Rudolf Klusal | MIT License
 * Model, registration, reference, and independent native-control contract tests.
 */
'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const L = require('../public/assets/theory/meteorology/lab-engine.js')();
require('../public/assets/theory/meteorology/lab-physics.js')(L);
require('../public/assets/theory/meteorology/lab-weather.js')(L);
const registry = fs.readFileSync(path.join(__dirname,'../app/services/theory_labs.php'),'utf8');
const ids = [...registry.matchAll(/^\s{12}'[^']+'\s*=>\s*\[([^\]]+)\],$/gm)].flatMap(m=>[...m[1].matchAll(/'([a-z0-9-]+)'/g)].map(x=>x[1]));
assert.equal(ids.length,57);
assert.equal(ids.length,new Set(ids).size);
assert.deepEqual([...ids].sort(),Object.keys(L.models).sort());
let evaluated=0;
for (const [id,model] of Object.entries(L.models)) {
    const defaults=Object.fromEntries(model.controls.map(c=>[c.key,c.value]));
    const values=c=>c.options?c.options.map((_,i)=>i):[c.min,c.max];
    const cases=[defaults,...model.controls.flatMap(c=>values(c).map(v=>({...defaults,[c.key]:v})))];
    // Pairwise boundaries catch interactions without an exponential input grid.
    for(let i=0;i<model.controls.length;i++)for(let j=i+1;j<model.controls.length;j++){
        const a=model.controls[i],b=model.controls[j];
        for(const x of values(a))for(const y of values(b))cases.push({...defaults,[a.key]:x,[b.key]:y});
    }
    cases.push(Object.fromEntries(model.controls.map(c=>[c.key,values(c)[0]])));
    cases.push(Object.fromEntries(model.controls.map(c=>[c.key,values(c).at(-1)])));
    for(const input of cases){
        const result=model.compute(input);
        assert.match(result.visual,/^<svg /,id);
        assert.doesNotMatch(result.visual+JSON.stringify(result.metrics),/NaN|Infinity|undefined|<script|<foreignObject/i,id);
        assert.ok(result.metrics.length && result.explanation,id);
        evaluated++;
    }
}
const near=(a,b,e)=>assert.ok(Math.abs(a-b)<=e,a+' != '+b);
near(L.isa(0),15,.001);
near(L.isa(11000),-56.5,.001);
near(L.isa(20000),-56.5,.001);
near(L.pressure(0),1013.25,.001);
near(L.pressure(11000),226.32,.1);
near(L.pa(457.2,1003),541.5575,.01);
near(L.da(457.2,1003,30),1219.394,.01);
near(L.lcl(22,12),1220,.01);
near(L.rh(12,12),100,.01);
assert.ok(L.rh(24,14)<L.rh(16,14));
assert.ok(L.da(450,1003,30)>L.da(450,1003,10));
const wind=L.models['wind-components'].compute({r:270,w:240,v:16}).metrics;
assert.equal(wind[0][0],'Čelní');
assert.equal(wind[0][1],L.fmt(16*Math.cos(Math.PI/6),1)+' kt');
assert.equal(wind[1][0],'Boční zleva');
assert.equal(wind[1][1],L.fmt(8,1)+' kt');
assert.match(L.models['metar-build'].compute({w:240,v:12,vis:7000,n:6,b:3200,q:1008}).metrics[0][1],/^24012KT 7000 BKN032 Q1008$/);
assert.match(L.models['metar-build'].compute({w:360,v:0,vis:10000,n:8,b:100,q:1013}).metrics[0][1],/^00000KT 9999 OVC001 Q1013$/);
// Minimal DOM contract fixture. The real browser acceptance remains separate.
class Element {
    constructor(tag,doc){this.tagName=tag;this.ownerDocument=doc;this.children=[];this.events={};this.attributes={};this.dataset={};this.hidden=true;this.textContent='';this.innerHTML='';}
    append(...children){this.children.push(...children);}
    replaceChildren(...children){this.children=children;}
    setAttribute(k,v){this.attributes[k]=v;}
    addEventListener(k,fn){this.events[k]=fn;}
}
const doc={createElement(tag){return new Element(tag,this);},createDocumentFragment(){return new Element('fragment',this);}};
const hosts=[doc.createElement('div'),doc.createElement('div')];
for(const host of hosts)host.dataset.meteorologyApplet='isa-column';
L.mount({querySelectorAll(){return hosts;}});
assert.ok(hosts.every(host=>!host.hidden));
const walk=(el,p)=>p(el)?el:el.children.map(c=>walk(c,p)).find(Boolean);
const first=walk(hosts[0],e=>e.tagName==='input'),reset=walk(hosts[0],e=>e.tagName==='button');
const visual1=walk(hosts[0],e=>e.className==='meteo-lab-visual'),visual2=walk(hosts[1],e=>e.className==='meteo-lab-visual');
const initial=visual1.innerHTML;
first.value='5000';first.events.input();
assert.notEqual(visual1.innerHTML,initial);
assert.equal(visual2.innerHTML,initial);
reset.events.click();
assert.equal(visual1.innerHTML,initial);
assert.equal(first.value,'1500');
console.log('Meteorology: '+ids.length+' registered laboratories; '+evaluated+' model evaluations and isolated control/reset contract passed.');
