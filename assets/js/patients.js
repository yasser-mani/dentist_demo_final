(() => {
  let patients = [];
  const tableBody = document.querySelector('#patientsTable tbody');
  const search = document.getElementById('searchPatients');
  const filter = document.getElementById('filterStatus');

  async function load() {
    tableBody.innerHTML = '<tr><td colspan="6" class="loading-state">Chargement…</td></tr>';
    try {
      const q = new URLSearchParams(); if (search.value.trim()) q.set('search', search.value.trim()); if (filter.value) q.set('status', filter.value);
      const res = await App.fetch(`api/patients.php?${q}`); patients = res.data || []; render();
    } catch (e) { tableBody.innerHTML = `<tr><td colspan="6" class="empty-state">${App.escapeHtml(e.message)}</td></tr>`; }
  }

  function render() {
    if (!patients.length) { tableBody.innerHTML = '<tr><td colspan="6" class="empty-state">Aucun patient trouvé.</td></tr>'; return; }
    tableBody.innerHTML = patients.map(p => `<tr><td><strong>${App.escapeHtml(p.full_name)}</strong></td><td>${App.escapeHtml(p.phone)}</td><td>${p.insurance ? App.escapeHtml(p.insurance) : '<span class="text-muted">—</span>'}</td><td>${App.renderBadge(p.status_label,p.badge_color)}</td><td>${p.next_appointment ? App.formatDate(p.next_appointment,true) : '<span class="text-muted">—</span>'}</td><td class="text-right"><button class="btn btn-sm btn-secondary" data-edit="${Number(p.id)}">Modifier</button> <a class="btn btn-sm btn-primary" href="patient.php?id=${Number(p.id)}">Profil</a></td></tr>`).join('');
  }

  function patientForm(p = {}) {
    const editing = Boolean(p.id);
    return `<div class="form-grid"><div class="form-group"><label>Nom complet *</label><input id="patientName" class="form-control" value="${App.escapeAttr(p.full_name || '')}" maxlength="120"></div><div class="form-group"><label>Téléphone *</label><input id="patientPhone" class="form-control" type="tel" value="${App.escapeAttr(p.phone || '')}" maxlength="25"></div></div><div class="form-group"><label>Assurance</label><input id="patientInsurance" class="form-control" value="${App.escapeAttr(p.insurance || '')}" maxlength="120"></div><div class="form-group"><label>Statut</label><select id="patientStatus" class="form-control"><option value="1" ${Number(p.status_id || 1) === 1 ? 'selected' : ''}>Nouveau rendez-vous</option><option value="2" ${Number(p.status_id) === 2 ? 'selected' : ''}>En traitement</option></select></div>`;
  }

  function openCreate() {
    App.openModal('Nouveau patient', patientForm(), '<button class="btn btn-secondary" type="button" data-close-modal>Annuler</button><button class="btn btn-primary" id="savePatient" type="button">Enregistrer</button>');
    document.getElementById('savePatient').addEventListener('click', () => save());
  }

  async function edit(id) {
    try {
      const res = await App.fetch(`api/patients.php?id=${id}`); const p = res.data;
      App.openModal('Modifier le patient', patientForm(p), '<button class="btn btn-secondary" type="button" data-close-modal>Annuler</button><button class="btn btn-primary" id="savePatient" type="button">Enregistrer</button>');
      document.getElementById('savePatient').addEventListener('click', () => save(id));
    } catch (e) { App.toast(e.message, 'error'); }
  }

  async function save(id = null) {
    const payload = { full_name: document.getElementById('patientName').value.trim(), phone: document.getElementById('patientPhone').value.trim(), insurance: document.getElementById('patientInsurance').value.trim(), status_id: Number(document.getElementById('patientStatus').value) };
    if (!payload.full_name || !payload.phone) return App.toast('Nom et téléphone sont requis.', 'error');
    try { const res = await App.fetch('api/patients.php', { method: id ? 'PUT' : 'POST', body: JSON.stringify(id ? {...payload,id} : payload) }); App.toast(res.message, 'success'); App.closeModal(); await load(); }
    catch (e) { App.toast(e.message, 'error'); }
  }

  document.getElementById('btnAddPatient').addEventListener('click', openCreate);
  search.addEventListener('input', App.debounce(load, 300)); filter.addEventListener('change', load);
  tableBody.addEventListener('click', e => { const btn = e.target.closest('[data-edit]'); if (btn) edit(Number(btn.dataset.edit)); });
  load();
})();
