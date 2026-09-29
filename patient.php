<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/helpers.php';
$patientId = (int)($_GET['id'] ?? 0);
if ($patientId <= 0) { header('Location: patients.php'); exit; }
$pageTitle = 'Profil patient';
require_once 'includes/header.php';
?>
<div class="profile-header">
    <a class="back-link" href="patients.php">← Retour aux patients</a>
    <div class="profile-heading"><div><div class="eyebrow">Dossier patient</div><h2 id="profileTitle">Chargement…</h2><p id="profileSummary">Chargement des informations.</p></div><a class="btn btn-secondary" href="pdf/report.php?patient_id=<?= $patientId ?>">Télécharger le rapport PDF</a></div>
</div>
<div class="profile-grid">
    <div class="profile-main">
        <section class="card" id="patientInfoCard"><div class="card-header"><div><h3 class="card-title">Informations personnelles</h3><p class="card-subtitle">Coordonnées et état du dossier.</p></div><button class="btn btn-sm btn-secondary" id="btnEditInfo">Modifier</button></div><div class="card-body"><div class="loading-state">Chargement…</div></div></section>

        <section class="card dental-card">
            <div class="card-header"><div><h3 class="card-title">Carte dentaire</h3><p class="card-subtitle">Cliquez sur une dent pour enregistrer son état, son traitement et vos observations.</p></div></div>
            <div class="card-body">
                <div class="chart-legend">
                    <span><i class="legend-color healthy"></i>Saine</span><span><i class="legend-color intervention"></i>Intervention nécessaire</span><span><i class="legend-color progress"></i>En cours</span><span><i class="legend-color treated"></i>Traitée</span>
                </div>
                <div id="dentalChart" class="dental-chart"></div>
                <div id="toothPanel" class="tooth-panel" hidden>
                    <div class="tooth-panel-header"><div><strong id="toothPanelTitle">Dent</strong><span class="text-muted">Édition</span></div><button class="icon-button" id="closePanelBtn" type="button" aria-label="Fermer">×</button></div>
                    <div class="tooth-panel-body">
                        <div class="form-grid"><div class="form-group"><label for="toothState">État</label><select id="toothState" class="form-control"><option value="healthy">Saine</option><option value="needs_intervention">Intervention nécessaire</option><option value="in_progress">Traitement en cours</option><option value="treated">Traitée</option></select></div><div class="form-group"><label for="toothDate">Date</label><input type="date" id="toothDate" class="form-control"></div></div>
                        <div class="form-group"><label for="toothTreatment">Traitement</label><input id="toothTreatment" class="form-control" type="text" placeholder="Ex. Couronne céramique"></div>
                        <div class="form-group"><label for="toothNotes">Observations</label><textarea id="toothNotes" class="form-control" rows="3" placeholder="Observations cliniques…"></textarea></div>
                        <button class="btn btn-primary btn-block" id="saveToothBtn" type="button">Enregistrer la dent</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="profile-sidebar">
        <section class="card"><div class="card-header"><div><h3 class="card-title">Notes</h3><p class="card-subtitle">Observations cliniques.</p></div><button class="btn btn-sm btn-secondary" id="btnAddNote">Ajouter</button></div><div class="card-body"><div id="notesList" class="notes-list"><div class="loading-state">Chargement…</div></div></div></section>
        <section class="card"><div class="card-header"><div><h3 class="card-title">Pièces jointes</h3><p class="card-subtitle">Images et documents du dossier.</p></div><label class="btn btn-sm btn-secondary file-button" for="uploadFile">Ajouter</label><input type="file" id="uploadFile" hidden accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"></div><div class="card-body"><div id="attachmentsList" class="attachments-list"><div class="loading-state">Chargement…</div></div></div></section>
    </aside>
</div>
<script>window.PATIENT_ID = <?= $patientId ?>;</script>
<?php $pageScripts = ['assets/js/profile.js','assets/js/dental-chart.js','assets/js/notes.js','assets/js/attachments.js']; require_once 'includes/footer.php'; ?>
