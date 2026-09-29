(() => {
  const upcomingBody = document.querySelector('#upcomingTable tbody');
  const pastBody = document.querySelector('#pastTable tbody');
  const labels = {scheduled:['Prévu','blue'], completed:['Terminé','green'], cancelled:['Annulé','red']};

  async function load() {
    upcomingBody.innerHTML = '<tr><td colspan="5" class="loading-state">Chargement…</td></tr>';
    pastBody.innerHTML = '<tr><td colspan="5" class="loading-state">Chargement…</td></tr>';
    try { const [u,p] = await Promise.all([App.fetch('api/appointments.php?filter=upcoming'),App.fetch('api/appointments.php?filter=past')]); render(upcomingBody,u.data||[],true); render(pastBody,p.data||[],false); }
    catch(e) { App.toast(e.message,'error'); }
  }
  function render(body, rows, upcoming) {
    if (!rows.length) { body.innerHTML = `<tr><td colspan="5" class="empty-state">Aucun rendez-vous ${upcoming?'à venir':'dans l’historique'}.</td></tr>`; return; }
    body.innerHTML = rows.map(a => `<tr><td><a href="patient.php?id=${Number(a.patient_id)}"><strong>${App.escapeHtml(a.patient_name)}</strong></a></td><td>${App.formatDate(a.appointment_date,true)}</td><td>${a.reason?App.escapeHtml(a.reason):'<span class="text-muted">—</span>'}</td><td>${App.renderBadge((labels[a.status]||[a.status,'gray'])[0],(labels[a.status]||[a.status,'gray'])[1])}</td><td class="text-right">${upcoming?`<button class="btn btn-sm btn-success" data-status="completed" data-id="${Number(a.id)}">Terminé</button> <button class="btn btn-sm btn-danger" data-status="cancelled" data-id="${Number(a.id)}">Annuler</button>`:`<button class="btn btn-sm btn-secondary" data-delete="${Number(a.id)}">Supprimer</button>`}</td></tr>`).join('');
  }
  async function setStatus(id,status){try{const r=await App.fetch('api/appointments.php',{method:'PATCH',body:JSON.stringify({id,status})});App.toast(r.message,'success');await load();}catch(e){App.toast(e.message,'error')}}
  async function remove(id){if(!confirm('Supprimer définitivement ce rendez-vous ?'))return;try{const r=await App.fetch('api/appointments.php',{method:'DELETE',body:JSON.stringify({id})});App.toast(r.message,'success');await load();}catch(e){App.toast(e.message,'error')}}
  async function newAppointment(){
    try{
      const res=await App.fetch('api/patients.php?limit=200'); const rows=res.data||[];
      const options=rows.length?rows.map(p=>`<option value="${Number(p.id)}">${App.escapeHtml(p.full_name)} — ${App.escapeHtml(p.phone)}</option>`).join(''):'<option value="">Aucun patient</option>';
      const body=`<div class="form-group"><label>Patient *</label><select id="apptPatient" class="form-control">${options}</select></div><div class="form-group"><label>Date et heure *</label><input id="apptDate" class="form-control" type="datetime-local" value="${App.localDateTimeValue(new Date(Date.now()+30*60*1000))}"></div><div class="form-group"><label>Motif</label><input id="apptReason" class="form-control" maxlength="255" placeholder="Ex. Contrôle annuel"></div>`;
      App.openModal('Nouveau rendez-vous',body,'<button class="btn btn-secondary" type="button" data-close-modal>Annuler</button><button class="btn btn-primary" id="saveAppt" type="button">Créer le rendez-vous</button>');
      if(!rows.length){App.toast('Ajoutez d’abord un patient.','info');return;}
      document.getElementById('saveAppt').addEventListener('click',async()=>{const payload={patient_id:Number(document.getElementById('apptPatient').value),appointment_date:document.getElementById('apptDate').value,reason:document.getElementById('apptReason').value.trim()};if(!payload.patient_id||!payload.appointment_date)return App.toast('Patient et date sont requis.','error');try{const r=await App.fetch('api/appointments.php',{method:'POST',body:JSON.stringify(payload)});App.toast(r.message,'success');App.closeModal();await load();}catch(e){App.toast(e.message,'error')}});
    }catch(e){App.toast(e.message,'error')}
  }
  document.getElementById('btnAddAppointment').addEventListener('click',newAppointment);
  document.body.addEventListener('click',e=>{const s=e.target.closest('[data-status]');if(s)setStatus(Number(s.dataset.id),s.dataset.status);const d=e.target.closest('[data-delete]');if(d)remove(Number(d.dataset.delete));});
  load();
})();
