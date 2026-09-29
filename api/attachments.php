<?php
declare(strict_types=1);
require_once '../includes/db.php';
require_once '../includes/helpers.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uploadRoot = realpath(__DIR__ . '/../uploads');
if ($uploadRoot === false) {
    $uploadRoot = __DIR__ . '/../uploads';
    if (!is_dir($uploadRoot)) mkdir($uploadRoot, 0755, true);
}
$patientUploadDir = $uploadRoot . '/patients';
if (!is_dir($patientUploadDir)) mkdir($patientUploadDir, 0755, true);

function safeHeaderFilename(string $name): string {
    $name = preg_replace('/[\r\n"]+/', '', $name) ?? 'fichier';
    return $name !== '' ? $name : 'fichier';
}
try {
    if ($method === 'GET') {
        if (($_GET['action'] ?? '') === 'view') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { http_response_code(400); exit('ID requis.'); }
            $stmt = $db->prepare('SELECT * FROM attachments WHERE id=?');
            $stmt->execute([$id]);
            $file = $stmt->fetch();
            if (!$file) { http_response_code(404); exit('Fichier introuvable.'); }
            $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim((string)$file['file_path'], '/\\'));
            $base = realpath($uploadRoot);
            $path = realpath($uploadRoot . DIRECTORY_SEPARATOR . $relative);
            if (!$base || !$path || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
                http_response_code(404); exit('Fichier physique introuvable.');
            }
            header('Content-Type: ' . $file['mime_type']);
            header('X-Content-Type-Options: nosniff');
            header('Content-Length: ' . (string)filesize($path));
            $disposition = isset($_GET['download']) ? 'attachment' : (in_array($file['mime_type'], ['image/jpeg','image/png','application/pdf'], true) ? 'inline' : 'attachment');
            header('Content-Disposition: ' . $disposition . '; filename="' . safeHeaderFilename((string)$file['original_name']) . '"');
            readfile($path); exit;
        }
        $patientId = (int)($_GET['patient_id'] ?? 0);
        if ($patientId <= 0) jsonError('Patient ID requis.');
        ensurePatientExists($db, $patientId);
        $stmt = $db->prepare('SELECT * FROM attachments WHERE patient_id=? ORDER BY uploaded_at DESC, id DESC');
        $stmt->execute([$patientId]);
        jsonSuccess($stmt->fetchAll());
    }

    if ($method === 'POST') {
        $patientId = (int)($_POST['patient_id'] ?? 0);
        if ($patientId <= 0) jsonError('Patient ID requis.');
        ensurePatientExists($db, $patientId);
        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) jsonError('Aucun fichier.');
        $error = validateUpload($_FILES['file']);
        if ($error) jsonError($error);
        $originalName = basename((string)$_FILES['file']['name']);
        $ext = getFileExtension($originalName);
        $storedName = generateRandomFilename($ext);
        $relativePath = 'patients/' . $storedName;
        $fullPath = $uploadRoot . DIRECTORY_SEPARATOR . $relativePath;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $fullPath)) jsonError('Échec du téléchargement.');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fullPath) ?: 'application/octet-stream';
        $size = (int)filesize($fullPath);
        $stmt = $db->prepare('INSERT INTO attachments (patient_id,original_name,stored_name,file_type,mime_type,file_size,file_path) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$patientId,$originalName,$storedName,$ext,$mime,$size,$relativePath]);
        jsonSuccess(['id'=>(int)$db->lastInsertId()], 'Fichier téléchargé.', 201);
    }

    if ($method === 'DELETE') {
        $data = requestJson();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) jsonError('ID requis.');
        $stmt = $db->prepare('SELECT * FROM attachments WHERE id=?');
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        if (!$file) jsonError('Fichier introuvable.', 404);
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim((string)$file['file_path'], '/\\'));
        $path = realpath($uploadRoot . DIRECTORY_SEPARATOR . $relative);
        $base = realpath($uploadRoot);
        if ($path && $base && str_starts_with($path, $base . DIRECTORY_SEPARATOR) && is_file($path)) @unlink($path);
        $stmt = $db->prepare('DELETE FROM attachments WHERE id=?');
        $stmt->execute([$id]);
        jsonSuccess([], 'Fichier supprimé.');
    }
    jsonError('Méthode non supportée.', 405);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError(APP_DEBUG ? $e->getMessage() : 'Erreur serveur.', 500);
}
