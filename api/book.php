<?php
/**
 * api/book.php
 * ─────────────────────────────────────────────────────────────
 * Captures tour enquiries from VILLA/index.html and stores them.
 * ─────────────────────────────────────────────────────────────
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed.']));
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

// Reading JSON body (for fetch calls)
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

// Fallback to $_POST
if (empty($data)) $data = $_POST;

// Sanitization
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

$name        = clean($data['name']     ?? '');
$email       = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone       = clean($data['phone']    ?? '');
$tour_name   = clean($data['tour']     ?? ''); // mapped from VILLA 'tour'
$travel_date = clean($data['date']     ?? ''); // mapped from VILLA 'date'
$num_people  = (int) ($data['guests']  ?? 1);  // mapped from VILLA 'guests'
$message     = clean($data['requests'] ?? ''); // mapped from VILLA 'requests'

if (empty($name) || empty($email)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Name and Email are required.']));
}

try {
    // 1. Save Booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (name, email, phone, tour_name, travel_date, num_people, message)
        VALUES (:name, :email, :phone, :tour_name, :travel_date, :num_people, :message)
    ");
    $stmt->execute([
        ':name'        => $name,
        ':email'       => $email,
        ':phone'       => $phone,
        ':tour_name'   => $tour_name,
        ':travel_date' => $travel_date ?: null,
        ':num_people'  => $num_people,
        ':message'     => $message,
    ]);
    
    $bookingId = $pdo->lastInsertId();

    // 2. Add to CRM (Clients Table)
    $stmtCRM = $pdo->prepare("
        INSERT INTO clients (name, email, phone, total_bookings)
        VALUES (:name, :email, :phone, 1)
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            total_bookings = total_bookings + 1,
            last_booking   = CURRENT_TIMESTAMP
    ");
    $stmtCRM->execute([':name' => $name, ':email' => $email, ':phone' => $phone]);

    // 3. Notify Admin with full details
    $subject = "Zanzibar Safari: New Discovery Request from {$name} (#{$bookingId})";
    $mailData = [
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
        'tour_name'   => $tour_name,
        'travel_date' => $travel_date ?: 'TBD',
        'num_people'  => $num_people,
        'message'     => $message ?: 'None'
    ];
    $mailStatus = sendAdminNotification($subject, $mailData);

    if ($mailStatus['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'Your paradise enquiry has been received. We will be in touch shortly.'
        ]);
    } else {
        // If DB saved but email failed, we still treat it as a failure so the user knows
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Booking saved to database, but notification email failed to send. Please contact us directly.'
        ]);
    }

} catch (PDOException $e) {
    error_log('[BOOKING ERROR] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save booking. Please try again.']);
}
