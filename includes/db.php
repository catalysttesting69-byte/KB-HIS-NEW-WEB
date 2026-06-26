<?php
/**
 * includes/db.php
 * ─────────────────────────────────────────────────────────────
 * PDO database connection.
 * ─────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/config.php';

// --- ENVIRONMENT DETECTION ---
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    // LOCAL XAMPP SETTINGS
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'villa_db'); // Local database name
    define('DB_USER', 'root');
    define('DB_PASS', '');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // INFINITYFREE PRODUCTION SETTINGS
    define('DB_HOST', 'sql110.infinityfree.com');
    define('DB_NAME', 'if0_41768892_villa');
    define('DB_USER', 'if0_41768892');
    define('DB_PASS', 'GWfPCYKmLJzP');
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        die("Connection failed: " . $e->getMessage());
    }
    error_log('[DB ERROR] ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
