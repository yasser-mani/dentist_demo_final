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
        $stmt = $db->prepare('SELECT * FROM patient_notes WHERE patient_id=? ORDER BY created_at DESC, id DESC');
        $stmt->execute([$patientId]);
        jsonSuccess($stmt->fetchAll());
    }
    if ($method === 'POST') {
        $data = requestJson();
        $patientId = (int)($data['patient_id'] ?? 0);
        $content = trim((string)($data['content'] ?? ''));
        if ($patientId <= 0) jsonError('Patient ID requis.');
        ensurePatientExists($db, $patientId);
        if ($content === '') jsonError('Le contenu est requis.');
        $stmt = $db->prepare('INSERT INTO patient_notes (patient_id,content) VALUES (?,?)');
        $stmt->execute([$patientId,$content]);
        jsonSuccess(['id'=>(int)$db->lastInsertId()], 'Note ajoutée.', 201);
    }
    if ($method === 'PUT') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        $content = trim((string)($data['content'] ?? ''));
        if ($id <= 0 || $content === '') jsonError('ID et contenu requis.');
        $stmt = $db->prepare('UPDATE patient_notes SET content=? WHERE id=?');
        $stmt->execute([$content,$id]);
        if ($stmt->rowCount() === 0) jsonError('Note introuvable.', 404);
        jsonSuccess([], 'Note modifiée.');
    }
    if ($method === 'DELETE') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) jsonError('ID requis.');
        $stmt = $db->prepare('DELETE FROM patient_notes WHERE id=?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonError('Note introuvable.', 404);
        jsonSuccess([], 'Note supprimée.');
    }
    jsonError('Méthode non supportée.', 405);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError(APP_DEBUG ? $e->getMessage() : 'Erreur serveur.', 500);
}
