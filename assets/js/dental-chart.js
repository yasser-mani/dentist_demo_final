(() => {
  const container = document.getElementById('dentalChart');
  const panel = document.getElementById('toothPanel');
  if (!container || !panel) return;
  const upper=[18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
  const lower=[48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];
  let data={}; let active=null;
  const shape = n => { const last=n%10; if(last<=2)return 'incisor'; if(last===3)return 'canine'; if(last<=5)return 'premolar'; return 'molar'; };
  const paths={
    incisor:'<path class="tooth-body" d="M11 10C11 5 14 3 20 3s9 2 9 7c0 6-2 16-5 26-2 6-3 10-4 10s-2-4-4-10c-3-10-5-20-5-26Z"/><path class="tooth-detail" d="M14 9h12"/>',
    canine:'<path class="tooth-body" d="M12 14C12 8 15 3 20 3s8 5 8 11c1 6-1 14-4 23-2 6-3 10-4 10s-2-4-4-10c-3-9-5-17-4-23Z"/><path class="tooth-detail" d="M16 13c2 2 6 2 8 0"/>',
    premolar:'<path class="tooth-body" d="M10 12C10 6 14 3 20 3s10 3 10 9c2 6 1 14-1 23-1 5-3 11-6 10-2-1-2-10-3-15-1 5-1 14-3 15-3 1-5-5-6-10-2-9-3-17-1-23Z"/><path class="tooth-detail" d="M15 11c3 1 7 1 10 0M20 12v8"/>',
    molar:'<path class="tooth-body" d="M8 12C8 6 13 3 20 3s12 3 12 9c2 6 1 14-1 22-1 4-3 8-5 11-2 2-3-5-4-11-1-6-2-6-4 0-1 6-2 13-4 11-2-3-4-7-5-11-2-8-3-16-1-22Z"/><path class="tooth-detail" d="M14 11c4 2 8 2 12 0M20 13v9"/>'
  };
  const stateLabel={healthy:'Saine',needs_intervention:'Intervention nécessaire',in_progress:'En traitement',treated:'Traitée'};

  function tooth(n,flip){const d=data[String(n)]||{};return `<button class="tooth-item state-${d.state||'healthy'}" data-tooth="${n}" type="button"><span class="tooth-number">${n}</span><span class="tooth-svg-wrapper ${flip?'flip':''}"><svg class="tooth-svg" viewBox="0 0 40 50">${paths[shape(n)]}</svg></span></button>`}
  function render(){container.innerHTML=`<div class="arch-label">Arcade supérieure</div><div class="dental-arch upper-arch">${upper.map(n=>tooth(n,false)).join('')}</div><div class="arch-label">Arcade inférieure</div><div class="dental-arch lower-arch">${lower.map(n=>tooth(n,true)).join('')}</div>`;}
  function open(n){active=String(n);const d=data[active]||{};document.getElementById('toothPanelTitle').textContent=`Dent ${n}`;document.getElementById('toothState').value=d.state||'healthy';document.getElementById('toothTreatment').value=d.treatment||'';document.getElementById('toothNotes').value=d.notes||'';document.getElementById('toothDate').value=d.record_date||new Date().toISOString().slice(0,10);panel.hidden=false;panel.scrollIntoView({behavior:'smooth',block:'nearest'});}
  container.addEventListener('click',e=>{const b=e.target.closest('[data-tooth]');if(b)open(Number(b.dataset.tooth));});
  document.getElementById('closePanelBtn').addEventListener('click',()=>{panel.hidden=true;active=null;});
  document.getElementById('saveToothBtn').addEventListener('click',async()=>{if(!active)return;const payload={patient_id:Number(window.PATIENT_ID),tooth_number:Number(active),state:document.getElementById('toothState').value,treatment:document.getElementById('toothTreatment').value.trim(),notes:document.getElementById('toothNotes').value.trim(),record_date:document.getElementById('toothDate').value};try{const r=await App.fetch('api/teeth.php',{method:'POST',body:JSON.stringify(payload)});data[active]={...data[active],...payload};render();panel.hidden=true;active=null;App.toast(r.message,'success');}catch(e){App.toast(e.message,'error')}});
  (async()=>{try{const r=await App.fetch(`api/teeth.php?patient_id=${Number(window.PATIENT_ID)}`);data=r.data||{};}catch(e){App.toast(e.message,'error')}finally{render();}})();
})();
