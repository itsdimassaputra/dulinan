<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Game Hub</title>
<style>
:root{--bg:#f6f5f1;--fg:#1d1d1b;--card:#fff;--muted:#6b6a65;--accent:#2f6fed;--line:#dcdad2;--ok:#1f9d57;
box-sizing:border-box;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--bg:#17181b;--fg:#ecebe6;--card:#22242a;--muted:#9a9990;--accent:#6c9bff;--line:#363943;--ok:#4cc789;color-scheme:dark}}
:root[data-theme="dark"]{--bg:#17181b;--fg:#ecebe6;--card:#22242a;--muted:#9a9990;--accent:#6c9bff;--line:#363943;--ok:#4cc789;color-scheme:dark}
*{box-sizing:border-box}
html{scroll-padding-top:env(safe-area-inset-top,0px)}
body{margin:0;background:var(--bg);color:var(--fg);font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;line-height:1.4}
main{max-width:480px;margin:0 auto;padding:20px 16px 40px}
h1{font-size:24px;margin:0 0 4px}
.sub{color:var(--muted);margin:0 0 16px;font-size:14px}
nav{display:flex;gap:6px;overflow-x:auto;margin-bottom:16px}
nav button{flex:1;white-space:nowrap;padding:10px 12px;border:1px solid var(--line);background:var(--card);color:var(--fg);border-radius:10px;font:inherit;cursor:pointer}
nav button.on{background:var(--accent);border-color:var(--accent);color:#fff}
.panel{display:none;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px}
.panel.on{display:block}
.bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:14px;color:var(--muted)}
.btn{padding:9px 16px;border:0;border-radius:9px;background:var(--accent);color:#fff;font:inherit;cursor:pointer}
.msg{min-height:22px;margin:10px 0 0;text-align:center;font-weight:600}
.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.cell{aspect-ratio:1;border:1px solid var(--line);background:var(--bg);color:var(--fg);border-radius:10px;font-size:40px;font-weight:700;cursor:pointer}
.grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.card{aspect-ratio:1;border:1px solid var(--line);background:var(--accent);color:transparent;border-radius:10px;font-size:32px;cursor:pointer}
.card.up{background:var(--bg);color:inherit}
.card.done{background:var(--bg);color:inherit;border-color:var(--ok)}
canvas{width:100%;max-width:320px;display:block;margin:0 auto;background:var(--bg);border:1px solid var(--line);border-radius:10px;touch-action:none}
.pad{display:grid;grid-template-columns:repeat(3,56px);gap:6px;justify-content:center;margin-top:12px}
.pad button{height:48px;border:1px solid var(--line);background:var(--bg);color:var(--fg);border-radius:10px;font-size:20px;cursor:pointer}
.row{display:flex;gap:8px}
input{flex:1;min-width:0;padding:10px;border:1px solid var(--line);border-radius:9px;background:var(--bg);color:var(--fg);font:inherit}
</style>
</head>
<body>
<main>
<p><a href="index.php" style="color:var(--accent);text-decoration:none">← Kembali ke menu</a></p>
<h1>🕹️ Game Klasik</h1>
<p class="sub">Kumpulan game santai untuk mengisi waktu luang.</p>
<nav id="tabs"></nav>

<section class="panel" id="p-ttt">
  <div class="bar"><span>Kamu: X · Komputer: O</span><span>Menang: <b id="tw">0</b> · Kalah: <b id="tl">0</b></span></div>
  <div class="grid3" id="tb"></div>
  <p class="msg" id="tm"></p>
  <button class="btn" onclick="tttReset()">Main lagi</button>
</section>

<section class="panel" id="p-mem">
  <div class="bar"><span>Langkah: <b id="ms">0</b></span><span>Terbaik: <b id="mb">-</b></span></div>
  <div class="grid4" id="mg"></div>
  <p class="msg" id="mm"></p>
  <button class="btn" onclick="memReset()">Acak ulang</button>
</section>

<section class="panel" id="p-snk">
  <div class="bar"><span>Skor: <b id="ss">0</b></span><span>Rekor: <b id="sb">0</b></span></div>
  <canvas id="sc" width="300" height="300"></canvas>
  <div class="pad">
    <span></span><button data-d="u">▲</button><span></span>
    <button data-d="l">◀</button><button data-d="d">▼</button><button data-d="r">▶</button>
  </div>
  <p class="msg" id="sm">Tekan Mulai, lalu pakai tombol panah.</p>
  <button class="btn" onclick="snkStart()">Mulai</button>
</section>

<section class="panel" id="p-gs">
  <div class="bar"><span>Tebak angka 1 sampai 100</span><span>Percobaan: <b id="gc">0</b></span></div>
  <div class="row"><input id="gi" type="number" min="1" max="100" placeholder="Angka tebakanmu"><button class="btn" onclick="gsGuess()">Tebak</button></div>
  <p class="msg" id="gm"></p>
  <button class="btn" onclick="gsReset()">Ulang</button>
</section>
</main>

<script>
const $=id=>document.getElementById(id);
function ld(k,d){try{const v=localStorage.getItem(k);return v===null?d:JSON.parse(v)}catch(e){return d}}
function sv(k,v){try{localStorage.setItem(k,JSON.stringify(v))}catch(e){}}

// Tabs
const games=[["ttt","Tic-Tac-Toe"],["mem","Memori"],["snk","Ular"],["gs","Tebak Angka"]];
games.forEach(([id,name],i)=>{
  const b=document.createElement("button");b.textContent=name;b.onclick=()=>show(id);b.dataset.id=id;$("tabs").appendChild(b);
});
function show(id){
  document.querySelectorAll(".panel").forEach(p=>p.classList.toggle("on",p.id==="p-"+id));
  document.querySelectorAll("nav button").forEach(b=>b.classList.toggle("on",b.dataset.id===id));
  if(id!=="snk")clearInterval(snk.t);
}

// Tic-Tac-Toe
let tb,tover,tw=ld("tw",0),tl=ld("tl",0);
const L=[[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
function win(b,p){return L.some(l=>l.every(i=>b[i]===p))}
function tttReset(){
  tb=Array(9).fill("");tover=false;$("tm").textContent="";
  $("tb").innerHTML="";
  for(let i=0;i<9;i++){const c=document.createElement("button");c.className="cell";c.onclick=()=>tttMove(i);$("tb").appendChild(c)}
  $("tw").textContent=tw;$("tl").textContent=tl;
}
function tttDraw(){[...$("tb").children].forEach((c,i)=>c.textContent=tb[i])}
function tttEnd(){
  if(win(tb,"X")){tw++;sv("tw",tw);$("tm").textContent="Kamu menang! 🎉";tover=true}
  else if(win(tb,"O")){tl++;sv("tl",tl);$("tm").textContent="Komputer menang.";tover=true}
  else if(tb.every(x=>x)){$("tm").textContent="Seri.";tover=true}
  $("tw").textContent=tw;$("tl").textContent=tl;
}
function tttMove(i){
  if(tover||tb[i])return;
  tb[i]="X";tttEnd();
  if(!tover){
    const free=tb.map((v,k)=>v?-1:k).filter(k=>k>=0);
    const tryWin=p=>free.find(k=>{tb[k]=p;const w=win(tb,p);tb[k]="";return w});
    let m=tryWin("O");if(m===undefined)m=tryWin("X");
    if(m===undefined)m=free.includes(4)?4:free[Math.floor(Math.random()*free.length)];
    tb[m]="O";tttEnd();
  }
  tttDraw();
}

// Memory
let mc,mo,mlock,mst,mdone,mbest=ld("mbest",null);
function memReset(){
  const e=["🍎","🚀","🐱","⚽","🎸","🌵","🍕","🐳"];
  mc=[...e,...e].sort(()=>Math.random()-.5);mo=[];mlock=false;mst=0;mdone=0;
  $("ms").textContent=0;$("mm").textContent="";$("mb").textContent=mbest??"-";
  $("mg").innerHTML="";
  mc.forEach((v,i)=>{const b=document.createElement("button");b.className="card";b.textContent=v;b.onclick=()=>memFlip(i,b);$("mg").appendChild(b)});
}
function memFlip(i,b){
  if(mlock||b.classList.contains("up")||b.classList.contains("done"))return;
  b.classList.add("up");mo.push([i,b]);
  if(mo.length===2){
    mst++;$("ms").textContent=mst;mlock=true;
    const [[a,x],[c,y]]=mo;
    setTimeout(()=>{
      if(mc[a]===mc[c]){x.className=y.className="card done";mdone++;
        if(mdone===8){$("mm").textContent="Selesai dalam "+mst+" langkah! 🎉";if(mbest===null||mst<mbest){mbest=mst;sv("mbest",mbest);$("mb").textContent=mbest}}}
      else{x.classList.remove("up");y.classList.remove("up")}
      mo=[];mlock=false;
    },650);
  }
}

// Snake
const snk={t:null,s:[],d:"r",nd:"r",f:null,sc:0,best:ld("sbest",0)};
const N=15,Z=20;
function snkStart(){
  clearInterval(snk.t);
  snk.s=[{x:7,y:7},{x:6,y:7},{x:5,y:7}];snk.d=snk.nd="r";snk.sc=0;
  $("ss").textContent=0;$("sb").textContent=snk.best;$("sm").textContent="";
  snkFood();snk.t=setInterval(snkTick,130);snkDraw();
}
function snkFood(){do{snk.f={x:Math.floor(Math.random()*N),y:Math.floor(Math.random()*N)}}while(snk.s.some(p=>p.x===snk.f.x&&p.y===snk.f.y))}
function snkTick(){
  snk.d=snk.nd;const h={...snk.s[0]};
  if(snk.d==="u")h.y--;if(snk.d==="d")h.y++;if(snk.d==="l")h.x--;if(snk.d==="r")h.x++;
  if(h.x<0||h.y<0||h.x>=N||h.y>=N||snk.s.some(p=>p.x===h.x&&p.y===h.y)){
    clearInterval(snk.t);$("sm").textContent="Game over! Skor "+snk.sc;
    if(snk.sc>snk.best){snk.best=snk.sc;sv("sbest",snk.best);$("sb").textContent=snk.best}return;
  }
  snk.s.unshift(h);
  if(h.x===snk.f.x&&h.y===snk.f.y){snk.sc++;$("ss").textContent=snk.sc;snkFood()}else snk.s.pop();
  snkDraw();
}
function snkDraw(){
  const c=$("sc").getContext("2d");c.clearRect(0,0,300,300);
  const css=getComputedStyle(document.documentElement);
  c.fillStyle=css.getPropertyValue("--ok");snk.s.forEach(p=>c.fillRect(p.x*Z+1,p.y*Z+1,Z-2,Z-2));
  c.fillStyle="#e5484d";c.fillRect(snk.f.x*Z+2,snk.f.y*Z+2,Z-4,Z-4);
}
function snkDir(d){
  const o={u:"d",d:"u",l:"r",r:"l"};if(d!==o[snk.d])snk.nd=d;
}
document.querySelectorAll(".pad button").forEach(b=>b.onclick=()=>snkDir(b.dataset.d));
document.addEventListener("keydown",e=>{
  const m={ArrowUp:"u",ArrowDown:"d",ArrowLeft:"l",ArrowRight:"r"};
  if(m[e.key]&&$("p-snk").classList.contains("on")){e.preventDefault();snkDir(m[e.key])}
});

// Tebak angka
let gn,gc,gend;
function gsReset(){gn=1+Math.floor(Math.random()*100);gc=0;gend=false;$("gc").textContent=0;$("gm").textContent="";$("gi").value=""}
function gsGuess(){
  if(gend)return;const v=parseInt($("gi").value,10);
  if(!(v>=1&&v<=100)){$("gm").textContent="Masukkan angka 1 sampai 100.";return}
  gc++;$("gc").textContent=gc;
  if(v===gn){gend=true;$("gm").textContent="Benar! Angkanya "+gn+" 🎉"}
  else $("gm").textContent=v<gn?"Terlalu kecil ⬆️":"Terlalu besar ⬇️";
  $("gi").value="";
}
$("gi").addEventListener("keydown",e=>{if(e.key==="Enter")gsGuess()});

tttReset();memReset();gsReset();show("ttt");
</script>
</body>
</html>
