
const V=(t,r)=>`<div class="verse">“${t}” <b>(${r})</b></div>`;
const MODS=[
{t:"Introdução — Conectando as Crianças a Jesus",
html:`<p>Baseado nos ensinamentos de Jesus é nossa missão e responsabilidade bíblica conduzir as crianças ao conhecimento de Deus. Trabalhar com o ministério infantil não é apenas um serviço educacional, mas uma resposta direta ao chamado de Jesus de ir e pregar o Evangelho (<i>Ide</i>).</p>
<p>As atividades desenvolvidas na sala infantil não visam isolar as crianças do culto principal, mas oferecer um ambiente pedagogicamente adaptado, com uma linguagem acessível onde elas possam compreender a Palavra de Deus, ser edificadas e ter um encontro real com Jesus. Através da oração e do ensino dedicado, atuamos como os intermediários mencionados nas Escrituras:</p>
<div class="verse"><b>“aqueles que apresentam as crianças a Jesus e revelam o amor de Deus aos seus corações.”</b></div>
${V("Depois, trouxeram crianças a Jesus, para que lhes impusesse as mãos e orasse por elas, mas os discípulos os repreendiam. Então, Jesus disse: - Deixem vir a mim as crianças e não as impeçam, pois o reino dos céus pertence aos que são semelhantes a elas.","Mateus 19: 13,14")}`,
v:"",q:[
{q:"Qual é o propósito central do Ministério Infantil apresentado na apostila?",o:["Manter as crianças ocupadas durante o culto","Conectar as crianças a Jesus e ensinar a Palavra","Substituir a responsabilidade da família","Realizar atividades recreativas sem ensino bíblico"],a:1},
{q:"O que deve orientar o trabalho com as crianças?",o:["A preferência pessoal do professor","A quantidade de brincadeiras","Os ensinamentos de Jesus e a Palavra de Deus","A busca por terminar a aula rapidamente"],a:2},
{q:"O professor deve entender seu serviço como:",o:["Uma tarefa sem planejamento","Um chamado e uma responsabilidade diante de Deus","Uma atividade para preencher o tempo","Uma função que depende apenas de talento natural"],a:1},
{q:"Qual atitude combina com a proposta da apostila?",o:["Ensinar sem considerar a criança","Priorizar somente o entretenimento","Ensinar a verdade bíblica de modo compreensível e aplicável","Evitar perguntas e participação"],a:2},
{q:"A aula no Ministério Infantil deve buscar:",o:["Apenas silêncio absoluto","A participação e o aprendizado da criança sobre Jesus","Somente memorizar nomes bíblicos","Preencher todo o tempo com atividades"],a:1}]},
{t:"1. Os Atributos de um Verdadeiro Líder"},{t:"2. Didática Guiada pelo Espírito Santo"},{t:"3. Preparação do Ambiente e Boas Práticas"},
{t:"4. Técnicas e Estratégias — Parte 1"},{t:"5. Técnicas e Estratégias — Parte 2"},{t:"6. Como Evitar a Indisciplina"},{t:"7. Autoridade Espiritual"}];
MODS.forEach(m=>{m.html=m.html||`<div class="todo">Conteúdo deste módulo ainda não inserido. Cole o texto fiel da apostila aqui (campo <code>html</code> deste módulo no código).</div>`;m.q=m.q||[];m.v=m.v||""});

const U=window.CURSO_USER||{};
let S={name:U.nome||"Aluno",started:true,done:[],exam:null};
const save=()=>fetch(U.api,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify({done:S.done,exam:S.exam})}).catch(()=>{});
fetch(U.api,{credentials:"same-origin"}).then(r=>r.json()).then(d=>{S.done=(d.done||[]).map(Number);S.exam=d.exam||null}).catch(()=>{}).then(()=>route());
const $=s=>document.querySelector(s),app=$("#app"),dr=$("#drawer");
const unlocked=i=>i==0||S.done.includes(i-1), allDone=()=>MODS.every((_,i)=>S.done.includes(i));
const examOk=()=>S.exam&&S.exam.pct>=70;

function dock(){const d=$("#dock");d.hidden=!S.started;
const it=[["🏠","Painel","#painel"],["📚","Módulos","m"],["🎬","Vídeos","v"],["✏️","Exercícios","e"],["🔍","Pesquisa","s"],["📝","Avaliação","#avaliacao"],["🏁","Conclusão","#conclusao"],["🎓","Certificado","#certificado"]];
d.innerHTML=it.map(([i,l,t])=>`<button class="sph" data-t="${t}" aria-label="${l}">${i}<span class="lbl">${l}</span></button>`).join("");
d.onclick=e=>{const b=e.target.closest(".sph");if(!b)return;const t=b.dataset.t;if(t[0]=="#"){dr.classList.remove("open");location.hash=t}else drawer(t)}}
function drawer(k){if(dr.classList.contains("open")&&dr.dataset.k==k){dr.classList.remove("open");return}
dr.dataset.k=k;let h="";
if(k=="s")h=`<h3>Pesquisa</h3><input type="text" id="sq" placeholder="Buscar no conteúdo…"><div id="sr"></div>`;
else{h=`<h3>${{m:"Módulos",v:"Vídeos",e:"Exercícios"}[k]}</h3>`+MODS.map((m,i)=>`<a class="${unlocked(i)?"":"off"}" data-go="${k}-${i}">${S.done.includes(i)?"✅":unlocked(i)?"▶️":"🔒"} ${m.t}</a>`).join("")}
dr.innerHTML=h;dr.classList.add("open");
dr.onclick=e=>{const a=e.target.closest("[data-go]");if(!a)return;const[k2,i]=a.dataset.go.split("-");dr.classList.remove("open");location.hash=k2=="m"?"modulo-"+i:k2=="v"?"videos":"exercicios"};
const sq=$("#sq");if(sq)sq.oninput=()=>{const q=sq.value.toLowerCase().trim();$("#sr").innerHTML=q.length<3?"":MODS.map((m,i)=>{const t=m.html.replace(/<[^>]+>/g," ");const p=t.toLowerCase().indexOf(q);return p<0&&!m.t.toLowerCase().includes(q)?"":`<a data-go="m-${i}">${m.t}<br><small>${p<0?"":"…"+t.slice(Math.max(0,p-30),p+70)+"…"}</small></a>`}).join("")||"<p>Nada encontrado.</p>"}}


function painel(){const p=Math.round(S.done.length/MODS.length*100);
app.innerHTML=`<main><h1>Olá, ${S.name}!</h1><p>Seu progresso: ${p}%</p><div class="bar"><i style="width:${p}%"></i></div><br><div class="grid">`+
MODS.map((m,i)=>`<div class="card ${unlocked(i)?"":"lock"} ${S.done.includes(i)?"done":""}"><span class="tag">${i?"Módulo "+i:"Introdução"}</span><b>${m.t.replace(/^\d\.\s/,"")}</b><span>${S.done.includes(i)?"✅ Concluído":unlocked(i)?"Disponível":"🔒 Bloqueado"}</span><a class="btn" href="#modulo-${i}">${S.done.includes(i)?"Rever":"Abrir"}</a></div>`).join("")+
`<div class="card ${allDone()?"":"lock"}"><span class="tag">Final</span><b>Conclusão e material de apoio</b><a class="btn" href="#conclusao">Abrir</a></div></div></main>`}

function quiz(m,i,box,onchange){box.innerHTML=m.q.map((q,n)=>`<div class="q" data-n="${n}"><b>${n+1}. ${q.q}</b>${q.o.map((o,k)=>`<button class="opt" data-k="${k}">${"ABCD"[k]}) ${o}</button>`).join("")}</div>`).join("");
const ans={};box.onclick=e=>{const b=e.target.closest(".opt");if(!b)return;const qd=b.closest(".q"),n=+qd.dataset.n;if(n in ans)return;const k=+b.dataset.k;ans[n]=k;
qd.querySelectorAll(".opt").forEach((x,j)=>{x.classList.toggle("right",j==m.q[n].a);if(j==k&&k!=m.q[n].a)x.classList.add("wrong")});onchange(Object.keys(ans).length==m.q.length)}}

function modulo(i){if(!unlocked(i)){location.hash="painel";return}const m=MODS[i];
app.innerHTML=`<main><p class="tag">${i?"Módulo "+i:"Introdução"}</p><h1>${m.t}</h1>${m.html}
<h2>🎬 Vídeo</h2>${m.v?`<a class="btn gold" target="_blank" rel="noopener" href="https://youtu.be/${m.v}">Assistir no YouTube</a>`:`<p class="todo">Vídeo em breve.</p>`}
<h2>✏️ Atividades de fixação</h2><p>Assinale apenas uma alternativa em cada questão.</p><div id="qz"></div>
<p><button class="btn" id="fin" ${m.q.length?"disabled":""}>Concluir módulo</button> <small id="hint">${m.q.length?"Responda todas as questões para concluir.":""}</small></p></main>`;
quiz(m,i,$("#qz"),ok=>{$("#fin").disabled=!ok});
$("#fin").onclick=()=>{if(!S.done.includes(i))S.done.push(i);save();location.hash=i==MODS.length-1?"conclusao":"painel"}}

function exercicios(){app.innerHTML=`<main><h1>Exercícios</h1>`+MODS.map((m,i)=>unlocked(i)&&m.q.length?`<h2>${m.t}</h2><div id="x${i}"></div>`:"").join("")+`</main>`;
MODS.forEach((m,i)=>{const b=$("#x"+i);if(b)quiz(m,i,b,()=>{})})}

function videos(){app.innerHTML=`<main><h1>Vídeos</h1><div class="grid">`+MODS.map((m,i)=>`<div class="card ${unlocked(i)?"":"lock"}"><span class="tag">${i?"Módulo "+i:"Introdução"}</span><b>${m.t}</b>${m.v?`<a class="btn gold" target="_blank" rel="noopener" href="https://youtu.be/${m.v}">▶ Assistir</a>`:"<span>Vídeo em breve</span>"}</div>`).join("")+`</div></main>`}

function avaliacao(){if(!allDone()){app.innerHTML=`<main><h1>Avaliação final</h1><p class="todo">Conclua todos os módulos para liberar a prova.</p></main>`;return}
const pool=MODS.flatMap(m=>m.q).sort(()=>Math.random()-.5).slice(0,10);
app.innerHTML=`<main><h1>Avaliação final</h1><p>${pool.length} questões sorteadas dos exercícios. Nota mínima: 70%.</p><div id="qz"></div><p><button class="btn" id="fin" disabled>Finalizar prova</button></p><div id="res"></div></main>`;
let hits=0;const box=$("#qz");box.innerHTML=pool.map((q,n)=>`<div class="q" data-n="${n}"><b>${n+1}. ${q.q}</b>${q.o.map((o,k)=>`<button class="opt" data-k="${k}">${"ABCD"[k]}) ${o}</button>`).join("")}</div>`).join("");
const ans={};box.onclick=e=>{const b=e.target.closest(".opt");if(!b)return;const qd=b.closest(".q"),n=+qd.dataset.n;if(n in ans)return;ans[n]=+b.dataset.k;qd.querySelectorAll(".opt").forEach(x=>x.style.outline="none");b.style.borderColor="var(--gold)";b.style.background="var(--card)";$("#fin").disabled=Object.keys(ans).length<pool.length}
$("#fin").onclick=()=>{pool.forEach((q,n)=>{if(ans[n]==q.a)hits++;box.querySelectorAll(".q")[n].querySelectorAll(".opt").forEach((x,j)=>{x.classList.toggle("right",j==q.a);if(j==ans[n]&&j!=q.a)x.classList.add("wrong")})});
const pct=Math.round(hits/pool.length*100);S.exam={pct,date:new Date().toLocaleDateString("pt-BR")};save();$("#fin").disabled=true;
$("#res").innerHTML=pct>=70?`<h2>🎉 Aprovado(a) com ${pct}%!</h2><a class="btn" href="#certificado">Ver certificado</a>`:`<h2>Você fez ${pct}%.</h2><a class="btn" href="#avaliacao" onclick="setTimeout(route)">Tentar novamente</a>`;scrollTo(0,document.body.scrollHeight)}}

function conclusao(){if(!allDone()){location.hash="painel";return}
app.innerHTML=`<main><h1>Conclusão</h1><p>Parabéns, ${S.name}! Você concluiu todos os módulos.</p>${V("Tudo o que fizerem, façam de todo o coração, como para o Senhor.","Colossenses 3:23")}
<p><b>Servir com excelência é transformar o comum em extraordinário!</b></p>
<p><a class="btn gold" href="${U.pdf||'apostila.pdf'}" download>⬇ Baixar a apostila em PDF</a></p><p><a class="btn" href="#avaliacao">Fazer a avaliação final</a></p></main>`}

function certificado(){if(!examOk()){app.innerHTML=`<main><h1>Certificado</h1><p class="todo">Seja aprovado(a) na avaliação final para liberar o certificado.</p></main>`;return}
app.innerHTML=`<main><div class="cert"><h2>Certificado de Participação</h2><p>Certificamos que</p><h1>${S.name}</h1><p>concluiu o curso <b>Preparando Líderes para o Ministério Infantil</b> com aproveitamento de ${S.exam.pct}%.</p><p>${S.exam.date}</p></div><p class="noprint"><button class="btn" onclick="print()">🖨 Imprimir / salvar PDF</button></p></main>`}

function route(){const h=location.hash.slice(1)||"";dr.classList.remove("open");scrollTo(0,0);
dock();
if(h.startsWith("modulo-"))return modulo(+h.split("-")[1]);
({painel,exercicios,videos,avaliacao,conclusao,certificado}[h]||painel)()}
addEventListener("hashchange",route);
