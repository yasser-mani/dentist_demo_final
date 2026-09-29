<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/helpers.php';
$pageTitle = 'Patients';
require_once 'includes/header.php';
?>
<div class="page-header">
    <div><div class="eyebrow">Dossier patients</div><h2>Gestion des patients</h2><p>Recherchez, ajoutez et mettez à jour les dossiers.</p></div>
    <button class="btn btn-primary" id="btnAddPatient" type="button">+ Ajouter un patient</button>
</div>
<section class="card">
    <div class="card-header filters-row">
        <div class="search-wrap"><span>⌕</span><input type="search" id="searchPatients" class="search-input" placeholder="Rechercher par nom, téléphone ou assurance…" autocomplete="off"></div>
        <select id="filterStatus" class="filter-select"><option value="">Tous les statuts</option><option value="1">Nouveau rendez-vous</option><option value="2">En traitement</option></select>
    </div>
    <div class="card-body table-wrap">
        <table class="data-table" id="patientsTable">
            <thead><tr><th>Nom complet</th><th>Téléphone</th><th>Assurance</th><th>Statut</th><th>Prochain RDV</th><th class="text-right">Actions</th></tr></thead>
            <tbody><tr><td colspan="6" class="loading-state">Chargement…</td></tr></tbody>
        </table>
    </div>
</section>
<?php $pageScripts = ['assets/js/patients.js']; require_once 'includes/footer.php'; ?>
