<?php
declare(strict_types=1);
require_once 'includes/config.php';

$message = null;
$error = null;
$log = [];

function executeSqlFile(PDO $pdo, string $file, array &$log): void {
    $sql = file_get_contents($file);
    if ($sql === false) throw new RuntimeException('Impossible de lire ' . basename($file));
    $lines = preg_split('/\R/', $sql) ?: [];
    $clean = '';
    foreach ($lines as $line) {
        if (preg_match('/^\s*--/', $line)) continue;
        $clean .= $line . "\n";
    }
    $statements = array_filter(array_map('trim', preg_split('/;\s*(?:\R|$)/', $clean) ?: []));
    foreach ($statements as $statement) {
        if ($statement !== '') $pdo->exec($statement);
    }
    $log[] = basename($file) . ' exécuté (' . count($statements) . ' requêtes).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        executeSqlFile($pdo, __DIR__ . '/database/schema.sql', $log);
        executeSqlFile($pdo, __DIR__ . '/database/seed.sql', $log);
        $uploadDir = __DIR__ . '/uploads/patients';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) throw new RuntimeException('Impossible de créer uploads/patients.');
        foreach (glob($uploadDir . '/*') ?: [] as $file) {
            if (is_file($file)) @unlink($file);
        }
        $message = 'Installation terminée. La démo a été réinitialisée.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installation — DentaFlow</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="install-body"><div class="install-card"><div class="brand-mark"><img src="assets/img/logo.svg" alt="DentaFlow"><div><strong>DentaFlow</strong><span>Installation</span></div></div><h1>Configurer l'application</h1><p>Cette page crée la base MySQL, les tables et les données de démonstration.</p><?php if ($message): ?><div class="alert success"><?= e($message) ?></div><a class="btn btn-primary btn-block" href="index.php">Ouvrir DentaFlow</a><?php endif; ?><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if ($log): ?><div class="install-log"><?php foreach ($log as $line): ?><div><?= e($line) ?></div><?php endforeach; ?></div><?php endif; ?><form method="post"><button class="btn btn-primary btn-block" type="submit">Installer / réinitialiser la démo</button></form><small>Après installation, supprimez ou protégez install.php pour un environnement réel.</small></div></body></html>
