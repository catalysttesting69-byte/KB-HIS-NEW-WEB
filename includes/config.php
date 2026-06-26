<?php
/**
 * includes/config.php
 * ─────────────────────────────────────────────────────────────
 * Auto-detects the project's base URL so all redirects and links
 * work whether the site is in a subfolder or root.
 * ─────────────────────────────────────────────────────────────
 */

if (!defined('BASE_URL')) {
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
        // Local XAMPP in htdocs/VILLA
        define('BASE_URL', '/VILLA');
    } else {
        // Production: Site is in the root of the domain on InfinityFree
        define('BASE_URL', ''); 
    }
}

// --- Session Security & Timeout ---
const SESSION_TIMEOUT = 1800; // 30 minutes in seconds

if (session_status() === PHP_SESSION_NONE) {
    // Relying on default hosting environment session handlers
    // Removed strict cookie_secure and httponly flags to prevent InfinityFree proxy conflicts
    session_start();
}

// Check for session timeout if admin is logged in
if (isset($_SESSION['admin_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/admin/login.php?error=session_expired');
        exit;
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}
