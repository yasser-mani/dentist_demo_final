<?php
declare(strict_types=1);
require_once '../includes/db.php';
require_once '../includes/helpers.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            if ($id <= 0) jsonError('ID patient invalide.');
            $stmt = $db->prepare("SELECT p.*, ps.label AS status_label, ps.badge_color,
                (SELECT appointment_date FROM appointments WHERE patient_id=p.id AND status='scheduled' AND appointment_date>=NOW() ORDER BY appointment_date LIMIT 1) AS next_appointment
                FROM patients p JOIN patient_statuses ps ON ps.id=p.status_id WHERE p.id=?");
            $stmt->execute([$id]);
            $patient = $stmt->fetch();
            if (!$patient) jsonError('Patient introuvable.', 404);
            jsonSuccess($patient);
        }

        $search = trim((string)($_GET['search'] ?? ''));
        $statusId = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : null;
        $limit = max(1, min(200, (int)($_GET['limit'] ?? 50)));
        $sql = "SELECT p.*, ps.label AS status_label, ps.badge_color,
                (SELECT appointment_date FROM appointments WHERE patient_id=p.id AND status='scheduled' AND appointment_date>=NOW() ORDER BY appointment_date LIMIT 1) AS next_appointment
                FROM patients p JOIN patient_statuses ps ON ps.id=p.status_id WHERE 1=1";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (p.full_name LIKE ? OR p.phone LIKE ? OR COALESCE(p.insurance, "") LIKE ?)';
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term);
        }
        if ($statusId !== null && $statusId > 0) {
            $sql .= ' AND p.status_id = ?';
            $params[] = $statusId;
        }
        $sql .= ' ORDER BY p.created_at DESC LIMIT ' . $limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        jsonSuccess($stmt->fetchAll());
    }

    if ($method === 'POST') {
        $data = requestJson();
        $name = trim((string)($data['full_name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $insurance = trim((string)($data['insurance'] ?? ''));
        $statusId = (int)($data['status_id'] ?? 1);

        if ($name === '') jsonError('Le nom est requis.');
        if ($phone === '') jsonError('Le téléphone est requis.');
        if (!isValidPhone($phone)) jsonError('Numéro de téléphone invalide.');
        if (!in_array($statusId, [1, 2], true)) jsonError('Statut invalide.');

        $stmt = $db->prepare('INSERT INTO patients (full_name, phone, insurance, status_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $phone, $insurance !== '' ? $insurance : null, $statusId]);
        jsonSuccess(['id' => (int)$db->lastInsertId()], 'Patient créé avec succès.', 201);
    }

    if ($method === 'PUT') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        $name = trim((string)($data['full_name'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $insurance = trim((string)($data['insurance'] ?? ''));
        $statusId = (int)($data['status_id'] ?? 1);
        if ($id <= 0) jsonError('ID requis.');
        if ($name === '' || $phone === '') jsonError('Nom et téléphone sont requis.');
        if (!isValidPhone($phone)) jsonError('Numéro de téléphone invalide.');
        if (!in_array($statusId, [1, 2], true)) jsonError('Statut invalide.');
        ensurePatientExists($db, $id);
        $stmt = $db->prepare('UPDATE patients SET full_name=?, phone=?, insurance=?, status_id=? WHERE id=?');
        $stmt->execute([$name, $phone, $insurance !== '' ? $insurance : null, $statusId, $id]);
        jsonSuccess([], 'Patient mis à jour.');
    }

    if ($method === 'DELETE') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) jsonError('ID requis.');
        ensurePatientExists($db, $id);
        $stmt = $db->prepare('DELETE FROM patients WHERE id=?');
        $stmt->execute([$id]);
        jsonSuccess([], 'Patient supprimé.');
    }

    jsonError('Méthode non supportée.', 405);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError(APP_DEBUG ? $e->getMessage() : 'Erreur serveur.', 500);
}
