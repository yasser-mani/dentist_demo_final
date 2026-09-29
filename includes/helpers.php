<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonResponse(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonSuccess(mixed $data = [], ?string $message = null, int $statusCode = 200): never
{
    $payload = ['success' => true, 'data' => $data];
    if ($message !== null) {
        $payload['message'] = $message;
    }
    jsonResponse($payload, $statusCode);
}

function jsonError(string $message, int $statusCode = 400, array $details = []): never
{
    $payload = ['success' => false, 'error' => $message];
    if ($details !== []) {
        $payload['details'] = $details;
    }
    jsonResponse($payload, $statusCode);
}

function requestJson(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        jsonError('JSON invalide.');
    }
    return $decoded;
}

function isValidPhone(string $phone): bool
{
    return (bool)preg_match('/^[0-9\s.\-+()]{8,25}$/', $phone);
}

function isValidDateTime(string $value): bool
{
    foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i'] as $format) {
        $d = DateTime::createFromFormat($format, $value);
        if ($d !== false && $d->format($format) === $value) {
            return true;
        }
    }
    return false;
}

function isValidDate(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

function normalizeDateTime(string $value): string
{
    $value = trim($value);
    if (!isValidDateTime($value)) {
        throw new InvalidArgumentException('Date et heure invalides.');
    }
    $value = str_replace('T', ' ', $value);
    if (strlen($value) === 16) {
        $value .= ':00';
    }
    return $value;
}

function isValidToothNumber(int $num): bool
{
    static $valid = [
        11,12,13,14,15,16,17,18,
        21,22,23,24,25,26,27,28,
        31,32,33,34,35,36,37,38,
        41,42,43,44,45,46,47,48,
    ];
    return in_array($num, $valid, true);
}

function isValidToothState(string $state): bool
{
    return in_array($state, ['healthy', 'needs_intervention', 'in_progress', 'treated'], true);
}

function formatDate(string|null $date, bool $includeTime = false): string
{
    if (!$date) {
        return '';
    }
    try {
        return (new DateTime($date))->format($includeTime ? 'd/m/Y H:i' : 'd/m/Y');
    } catch (Throwable) {
        return '';
    }
}

function formatFileSize(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' o';
    if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
    return number_format($bytes / (1024 * 1024), 1, ',', ' ') . ' Mo';
}

function generateRandomFilename(string $extension): string
{
    return bin2hex(random_bytes(16)) . '.' . strtolower($extension);
}

function getFileExtension(string $filename): string
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function validateUpload(array $file): ?string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return 'Fichier invalide.';
    }
    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        return 'Erreur lors du téléchargement.';
    }
    if (!isset($file['tmp_name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) {
        return 'Fichier téléchargé invalide.';
    }
    if ((int)$file['size'] <= 0 || (int)$file['size'] > UPLOAD_MAX_SIZE) {
        return 'Fichier trop volumineux ou vide (max 5 Mo).';
    }

    $ext = getFileExtension((string)$file['name']);
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        return 'Type de fichier non autorisé.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_MIMES, true)) {
        return 'Type MIME non autorisé.';
    }
    return null;
}

function ensurePatientExists(PDO $db, int $patientId): void
{
    $stmt = $db->prepare('SELECT id FROM patients WHERE id = ?');
    $stmt->execute([$patientId]);
    if (!$stmt->fetchColumn()) {
        jsonError('Patient introuvable.', 404);
    }
}

function pageUrl(string $path = ''): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $base = rtrim(dirname($script), '/.');
    if ($base === '/' || $base === '\\') $base = '';
    return $base . '/' . ltrim($path, '/');
}
