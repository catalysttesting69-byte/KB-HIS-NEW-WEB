<?php
/**
 * admin/reset.php
 * ─────────────────────────────────────────────────────────────
 * RUN THIS IN YOUR BROWSER TO FORCE RESET YOUR ADMIN ACCOUNT.
 * URL: http://localhost/VILLA/admin/reset.php
 * ─────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/../includes/db.php';

$email = 'admin@zanzibar.com';
$pass  = 'Admin@Zanzibar2026';
$hash  = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // 1. Delete existing admin
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    
    // 2. Insert fresh admin
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['Admin User', $email, $hash]);
    
    echo "<h1>Admin Account Reset Successfully!</h1>";
    echo "<li><strong>Email:</strong> admin@zanzibar.com</li>";
    echo "<li><strong>Password:</strong> Admin@Zanzibar2026</li>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
} catch (Exception $e) {
    echo "<h1>Error Reseting:</h1> " . $e->getMessage();
}
