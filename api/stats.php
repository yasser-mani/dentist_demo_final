<?php
declare(strict_types=1);
require_once '../includes/db.php';
require_once '../includes/helpers.php';
try {
    $db = getDB();
    $total = (int)$db->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    $today = (int)$db->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date)=CURDATE() AND status='scheduled'")->fetchColumn();
    $treatment = (int)$db->query("SELECT COUNT(*) FROM patients WHERE status_id=2")->fetchColumn();
    $new = (int)$db->query('SELECT COUNT(*) FROM patients WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
    jsonSuccess([
        'total_patients' => $total,
        'today_appointments' => $today,
        'in_treatment' => $treatment,
        'new_patients' => $new,
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonError(APP_DEBUG ? $e->getMessage() : 'Erreur serveur.', 500);
}
