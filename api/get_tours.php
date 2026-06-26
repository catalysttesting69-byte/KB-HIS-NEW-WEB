<?php
/**
 * api/get_tours.php
 * Sends the dynamic tour list to your index.html page.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM tours WHERE active = 1 ORDER BY created_at DESC");
    $tours = $stmt->fetchAll();
    echo json_encode(['success' => true, 'tours' => $tours]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
