
const qs=(s,c=document)=>c.querySelector(s), qsa=(s,c=document)=>[...c.querySelectorAll(s)];
const toggle=qs('.nav-toggle'); if(toggle) toggle.addEventListener('click',()=>qs('.main-nav')?.classList.toggle('open'));
qsa('.accordion button').forEach(b=>b.addEventListener('click',()=>b.parentElement.classList.toggle('open')));
qsa('[data-filter]').forEach(b=>b.addEventListener('click',()=>{qsa('[data-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');const f=b.dataset.filter;qsa('[data-category]').forEach(i=>i.style.display=(f==='all'||i.dataset.category===f)?'block':'none')}));
const modal=qs('.modal');qsa('.gallery-item').forEach(i=>i.addEventListener('click',()=>{if(modal){qs('img',modal).src=qs('img',i).src;modal.classList.add('open')}}));qsa('.modal-close').forEach(b=>b.addEventListener('click',()=>modal.classList.remove('open')));if(modal)modal.addEventListener('click',e=>{if(e.target===modal)modal.classList.remove('open')});
qsa('[data-tab]').forEach(b=>b.addEventListener('click',()=>{const root=b.closest('[data-tabs-root]')||document;qsa('[data-tab]',root).forEach(x=>x.classList.remove('active'));qsa('.tab-pane',root).forEach(x=>x.classList.remove('active'));b.classList.add('active');qs('#'+b.dataset.tab,root)?.classList.add('active')}));
qsa('[data-toast]').forEach(b=>b.addEventListener('click',()=>{const t=qs('.toast');if(t){t.textContent=b.dataset.toast||'Saved successfully';t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2600)}}));
qsa('[data-admin-menu]').forEach(b=>b.addEventListener('click',()=>qs('.admin-sidebar')?.classList.toggle('open')));
const cookie=qs('.cookie');if(cookie&&!localStorage.getItem('scb-cookie'))setTimeout(()=>cookie.classList.add('show'),500);qsa('[data-cookie]').forEach(b=>b.addEventListener('click',()=>{localStorage.setItem('scb-cookie',b.dataset.cookie);cookie?.classList.remove('show')}));
