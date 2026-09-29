(async () => {
  const statsRow = document.getElementById('statsRow');
  const todayContainer = document.getElementById('todayAppointments');
  const recentBody = document.querySelector('#recentPatientsTable tbody');

  function renderStats(stats) {
    const items = [
      ['Patients', stats.total_patients, '👥'],
      ["RDV aujourd'hui", stats.today_appointments, '◷'],
      ['En traitement', stats.in_treatment, '✚'],
      ['Nouveaux (30j)', stats.new_patients, '＋'],
    ];
    statsRow.innerHTML = items.map(([label,value,icon]) => `<div class="stat-card"><div class="stat-top"><div><div class="stat-label">${label}</div><div class="stat-value">${Number(value) || 0}</div></div><div class="stat-icon">${icon}</div></div></div>`).join('');
  }

  function renderToday(rows) {
    if (!rows.length) { todayContainer.innerHTML = '<div class="empty-state">Aucun rendez-vous planifié aujourd’hui.</div>'; return; }
    todayContainer.innerHTML = rows.map(a => `<div class="appointment-row"><div class="appointment-time">${App.formatDate(a.appointment_date,true).split(' ')[1] || ''}</div><div class="appointment-main"><a href="patient.php?id=${Number(a.patient_id)}"><strong>${App.escapeHtml(a.patient_name)}</strong></a><span>${a.reason ? App.escapeHtml(a.reason) : 'Consultation'}</span></div><a class="btn btn-sm btn-secondary" href="patient.php?id=${Number(a.patient_id)}">Dossier</a></div>`).join('');
  }

  function renderRecent(rows) {
    if (!rows.length) { recentBody.innerHTML = '<tr><td colspan="5" class="empty-state">Aucun patient.</td></tr>'; return; }
    recentBody.innerHTML = rows.slice(0,8).map(p => `<tr><td><strong>${App.escapeHtml(p.full_name)}</strong></td><td>${App.escapeHtml(p.phone)}</td><td>${App.renderBadge(p.status_label,p.badge_color)}</td><td>${App.formatDate(p.created_at)}</td><td class="text-right"><a class="btn btn-sm btn-secondary" href="patient.php?id=${Number(p.id)}">Ouvrir</a></td></tr>`).join('');
  }

  try {
    const [stats, appointments, patients] = await Promise.all([
      App.fetch('api/stats.php'), App.fetch('api/appointments.php?filter=today'), App.fetch('api/patients.php?limit=8')
    ]);
    renderStats(stats.data || {}); renderToday(appointments.data || []); renderRecent(patients.data || []);
  } catch (e) {
    App.toast(e.message, 'error');
    todayContainer.innerHTML = '<div class="empty-state">Impossible de charger les données.</div>';
    recentBody.innerHTML = '<tr><td colspan="5" class="empty-state">Impossible de charger les données.</td></tr>';
  }
})();
