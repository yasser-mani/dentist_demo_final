(async () => {
  const id = Number(window.PATIENT_ID);
  const title = document.getElementById('profileTitle');
  const summary = document.getElementById('profileSummary');
  const card = document.getElementById('patientInfoCard');
  let patient = null;

  function render() {
    title.textContent = patient.full_name;
    summary.textContent = `${patient.phone} · ${patient.status_label}`;
    card.querySelector('.card-body').innerHTML = `<div class="info-grid"><div class="info-item"><label>Nom complet</label><div class="value">${App.escapeHtml(patient.full_name)}</div></div><div class="info-item"><label>Téléphone</label><div class="value">${App.escapeHtml(patient.phone)}</div></div><div class="info-item"><label>Assurance</label><div class="value">${patient.insurance?App.escapeHtml(patient.insurance):'<span class="text-muted">Aucune</span>'}</div></div><div class="info-item"><label>Statut</label><div class="value">${App.renderBadge(patient.status_label,patient.badge_color)}</div></div><div class="info-item"><label>Prochain RDV</label><div class="value">${patient.next_appointment?App.formatDate(patient.next_appointment,true):'<span class="text-muted">—</span>'}</div></div><div class="info-item"><label>Patient depuis</label><div class="value">${App.formatDate(patient.created_at)}</div></div></div>`;
  }
  function openEdit(){
    const body=`<div class="form-grid"><div class="form-group"><label>Nom complet *</label><input id="editName" class="form-control" value="${App.escapeAttr(patient.full_name)}"></div><div class="form-group"><label>Téléphone *</label><input id="editPhone" class="form-control" value="${App.escapeAttr(patient.phone)}"></div></div><div class="form-group"><label>Assurance</label><input id="editInsurance" class="form-control" value="${App.escapeAttr(patient.insurance||'')}"></div><div class="form-group"><label>Statut</label><select id="editStatus" class="form-control"><option value="1" ${Number(patient.status_id)===1?'selected':''}>Nouveau rendez-vous</option><option value="2" ${Number(patient.status_id)===2?'selected':''}>En traitement</option></select></div>`;
    App.openModal('Modifier les informations',body,'<button class="btn btn-secondary" type="button" data-close-modal>Annuler</button><button class="btn btn-primary" id="updateInfo" type="button">Enregistrer</button>');
    document.getElementById('updateInfo').addEventListener('click',async()=>{const payload={id,full_name:document.getElementById('editName').value.trim(),phone:document.getElementById('editPhone').value.trim(),insurance:document.getElementById('editInsurance').value.trim(),status_id:Number(document.getElementById('editStatus').value)};if(!payload.full_name||!payload.phone)return App.toast('Nom et téléphone sont requis.','error');try{const r=await App.fetch('api/patients.php',{method:'PUT',body:JSON.stringify(payload)});App.toast(r.message,'success');App.closeModal();await load();}catch(e){App.toast(e.message,'error')}});
  }
  async function load(){
    try{const r=await App.fetch(`api/patients.php?id=${id}`);patient=r.data;render();}catch(e){title.textContent='Patient introuvable';summary.textContent=e.message;App.toast(e.message,'error');return;}
  }
  document.getElementById('btnEditInfo').addEventListener('click',()=>patient&&openEdit());
  await load();
})();
