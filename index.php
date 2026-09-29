<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/helpers.php';
$pageTitle = 'Tableau de bord';
require_once 'includes/header.php';
?>
<div class="page-header dashboard-heading">
    <div>
        <div class="eyebrow">Aujourd'hui</div>
        <h2>Vue d'ensemble du cabinet</h2>
        <p>Suivez les patients et les rendez-vous depuis un seul espace.</p>
    </div>
    <a class="btn btn-primary" href="patients.php">+ Nouveau patient</a>
</div>

<div class="stats-row" id="statsRow">
    <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="stat-card loading-card"><div class="skeleton-line"></div><div class="skeleton-big"></div></div>
    <?php endfor; ?>
</div>

<div class="dashboard-grid">
    <section class="card">
        <div class="card-header"><div><h3 class="card-title">Rendez-vous d'aujourd'hui</h3><p class="card-subtitle">Les consultations planifiées pour aujourd'hui.</p></div><a class="link-button" href="appointments.php">Voir tous</a></div>
        <div class="card-body"><div id="todayAppointments" class="appointments-list"><div class="loading-state">Chargement…</div></div></div>
    </section>

    <section class="card">
        <div class="card-header"><div><h3 class="card-title">Patients récents</h3><p class="card-subtitle">Les derniers patients enregistrés.</p></div><a class="link-button" href="patients.php">Voir tous</a></div>
        <div class="card-body table-wrap"><table class="data-table" id="recentPatientsTable"><thead><tr><th>Patient</th><th>Téléphone</th><th>Statut</th><th>Ajouté le</th><th></th></tr></thead><tbody><tr><td colspan="5" class="loading-state">Chargement…</td></tr></tbody></table></div>
    </section>
</div>
<?php
$pageScripts = ['assets/js/dashboard.js'];
require_once 'includes/footer.php';
?>
