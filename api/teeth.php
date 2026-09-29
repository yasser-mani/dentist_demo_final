<?php
declare(strict_types=1);
require_once '../includes/db.php';
require_once '../includes/helpers.php';
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    if ($method === 'GET') {
        $patientId = (int)($_GET['patient_id'] ?? 0);
        if ($patientId <= 0) jsonError('Patient ID requis.');
        ensurePatientExists($db, $patientId);
        $stmt = $db->prepare('SELECT * FROM teeth_records WHERE patient_id=? ORDER BY tooth_number');
        $stmt->execute([$patientId]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) $map[(string)$row['tooth_number']] = $row;
        jsonSuccess($map);
    }
    if ($method === 'POST') {
        $data = requestJson();
        $patientId = (int)($data['patient_id'] ?? 0);
        $toothNumber = (int)($data['tooth_number'] ?? 0);
        $state = (string)($data['state'] ?? 'healthy');
        $treatment = trim((string)($data['treatment'] ?? ''));
        $notes = trim((string)($data['notes'] ?? ''));
        $recordDate = trim((string)($data['record_date'] ?? ''));
        if ($patientId <= 0) jsonError('Patient ID requis.');
        ensurePatientExists($db, $patientId);
        if (!isValidToothNumber($toothNumber)) jsonError('Numéro de dent invalide.');
        if (!isValidToothState($state)) jsonError('État invalide.');
        if ($recordDate !== '' && !isValidDate($recordDate)) jsonError('Date invalide.');
        $stmt = $db->prepare("INSERT INTO teeth_records (patient_id,tooth_number,state,treatment,notes,record_date) VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE state=VALUES(state), treatment=VALUES(treatment), notes=VALUES(notes), record_date=VALUES(record_date)");
        $stmt->execute([$patientId,$toothNumber,$state,$treatment !== '' ? $treatment : null,$notes !== '' ? $notes : null,$recordDate !== '' ? $recordDate : null]);
        jsonSuccess([], 'Dent enregistrée.');
    }
    jsonError('Méthode non supportée.', 405);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError(APP_DEBUG ? $e->getMessage() : 'Erreur serveur.', 500);
}
