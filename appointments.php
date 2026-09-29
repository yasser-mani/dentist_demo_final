<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/helpers.php';
$pageTitle = 'Rendez-vous';
require_once 'includes/header.php';
?>
<div class="page-header">
    <div><div class="eyebrow">Planning</div><h2>Rendez-vous</h2><p>Planifiez et suivez les consultations du cabinet.</p></div>
    <button class="btn btn-primary" id="btnAddAppointment" type="button">+ Nouveau rendez-vous</button>
</div>
<div class="appointments-grid">
    <section class="card"><div class="card-header"><div><h3 class="card-title">À venir</h3><p class="card-subtitle">Rendez-vous encore planifiés.</p></div></div><div class="card-body table-wrap"><table class="data-table" id="upcomingTable"><thead><tr><th>Patient</th><th>Date et heure</th><th>Motif</th><th>Statut</th><th class="text-right">Actions</th></tr></thead><tbody><tr><td colspan="5" class="loading-state">Chargement…</td></tr></tbody></table></div></section>
    <section class="card"><div class="card-header"><div><h3 class="card-title">Historique</h3><p class="card-subtitle">Rendez-vous terminés ou annulés.</p></div></div><div class="card-body table-wrap"><table class="data-table" id="pastTable"><thead><tr><th>Patient</th><th>Date et heure</th><th>Motif</th><th>Statut</th><th class="text-right">Actions</th></tr></thead><tbody><tr><td colspan="5" class="loading-state">Chargement…</td></tr></tbody></table></div></section>
</div>
<?php $pageScripts = ['assets/js/appointments.js']; require_once 'includes/footer.php'; ?>
