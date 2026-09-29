<?php
declare(strict_types=1);

// Database - change these values for your local environment.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'dentaflow');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'DentaFlow');
define('APP_TIMEZONE', 'Africa/Casablanca');
date_default_timezone_set(APP_TIMEZONE);

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('ALLOWED_MIMES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
]);

define('APP_DEBUG', true);
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
