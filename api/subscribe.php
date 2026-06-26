<?php
/**
 * api/subscribe.php
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed.']));
}

require_once __DIR__ . '/../includes/db.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (empty($data)) {
    $data = $_POST;
}

$email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Valid email is required.']));
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
    echo json_encode(['success' => true, 'message' => 'Thank you for journeying with us!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong.']);
}
