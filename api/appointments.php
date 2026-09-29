<?php
declare(strict_types=1);
require_once '../includes/db.php';
require_once '../includes/helpers.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$labels = ['scheduled' => 'Prévu', 'completed' => 'Terminé', 'cancelled' => 'Annulé'];

try {
    if ($method === 'GET') {
        $filter = (string)($_GET['filter'] ?? 'upcoming');
        $sql = "SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone
                FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE 1=1";
        if ($filter === 'today') {
            $sql .= " AND DATE(a.appointment_date)=CURDATE() AND a.status='scheduled' ORDER BY a.appointment_date ASC LIMIT 50";
        } elseif ($filter === 'past') {
            $sql .= " AND (a.appointment_date<NOW() OR a.status IN ('completed','cancelled')) ORDER BY a.appointment_date DESC LIMIT 100";
        } else {
            $sql .= " AND a.appointment_date>=NOW() AND a.status='scheduled' ORDER BY a.appointment_date ASC LIMIT 100";
        }
        $rows = $db->query($sql)->fetchAll();
        foreach ($rows as &$row) $row['status_label'] = $labels[$row['status']] ?? $row['status'];
        unset($row);
        jsonSuccess($rows);
    }

    if ($method === 'POST') {
        $data = requestJson();
        $patientId = (int)($data['patient_id'] ?? 0);
        $date = trim((string)($data['appointment_date'] ?? ''));
        $reason = trim((string)($data['reason'] ?? ''));
        if ($patientId <= 0) jsonError('Patient requis.');
        ensurePatientExists($db, $patientId);
        if ($date === '') jsonError('Date et heure requises.');
        try {
            $date = normalizeDateTime($date);
        } catch (Throwable) {
            jsonError('Date et heure invalides.');
        }
        $stmt = $db->prepare("SELECT id FROM appointments WHERE patient_id=? AND appointment_date=? AND status='scheduled' LIMIT 1");
        $stmt->execute([$patientId, $date]);
        if ($stmt->fetch()) jsonError('Ce patient a déjà un rendez-vous à cette date et heure.');
        $stmt = $db->prepare("INSERT INTO appointments (patient_id, appointment_date, reason, status) VALUES (?, ?, ?, 'scheduled')");
        $stmt->execute([$patientId, $date, $reason !== '' ? $reason : null]);
        jsonSuccess(['id' => (int)$db->lastInsertId()], 'Rendez-vous créé.', 201);
    }

    if ($method === 'PATCH') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        $status = (string)($data['status'] ?? '');
        if ($id <= 0) jsonError('ID requis.');
        if (!array_key_exists($status, $labels)) jsonError('Statut invalide.');
        $stmt = $db->prepare('UPDATE appointments SET status=? WHERE id=?');
        $stmt->execute([$status, $id]);
        if ($stmt->rowCount() === 0) jsonError('Rendez-vous introuvable.', 404);
        jsonSuccess([], 'Statut mis à jour.');
    }

    if ($method === 'DELETE') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) jsonError('ID requis.');
        $stmt = $db->prepare('DELETE FROM appointments WHERE id=?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonError('Rendez-vous introuvable.', 404);
        jsonSuccess([], 'Rendez-vous supprimé.');
    }

    jsonError('Méthode non supportée.', 405);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError(APP_DEBUG ? $e->getMessage() : 'Erreur serveur.', 500);
}
