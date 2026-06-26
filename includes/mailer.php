<?php
/**
 * includes/mailer.php
 * ─────────────────────────────────────────────────────────────
 * Tool for sending elegant HTML booking emails.
 * ─────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/config.php';

/**
 * Sends a notification email to the site owner (Zanzibar Safari).
 */
function sendAdminNotification(string $subject, array $data): array {
    $to = "Hackingmno4@gmail.com"; // Your destination email
    
    // HTML Email Template
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Outfit', sans-serif; color: #1e1b4b; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #e0e7ff; border-radius: 12px; overflow: hidden; }
            .header { background: #1e1b4b; color: #ffffff; padding: 30px; text-align: center; }
            .content { padding: 30px; background: #ffffff; }
            .footer { background: #faf9f6; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
            .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
            .label { font-weight: bold; color: #d4af37; width: 30%; }
            .btn { display: inline-block; padding: 12px 24px; background: #d4af37; color: #1e1b4b; text-decoration: none; border-radius: 50px; font-weight: bold; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin:0; font-size: 24px;'>New Safari Discovery</h1>
            </div>
            <div class='content'>
                <p>Hello! You have received a new booking request from your website.</p>
                <table class='data-table'>
                    <tr><td class='label'>Customer</td><td>{$data['name']}</td></tr>
                    <tr><td class='label'>Email</td><td>{$data['email']}</td></tr>
                    <tr><td class='label'>Phone</td><td>{$data['phone']}</td></tr>
                    <tr><td class='label'>Tour</td><td><strong>{$data['tour_name']}</strong></td></tr>
                    <tr><td class='label'>Date</td><td>{$data['travel_date']}</td></tr>
                    <tr><td class='label'>Guests</td><td>{$data['num_people']}</td></tr>
                </table>
                <div style='margin-top:20px; padding:15px; background:#f8fafc; border-radius:8px;'>
                    <p style='margin:0; font-weight:bold; color:#64748b; font-size:12px; text-transform:uppercase;'>Special Requests:</p>
                    <p style='margin-top:5px;'>\"{$data['message']}\"</p>
                </div>
                <center><a href='https://zanzibarsafari.wuaze.com/admin/login.php' class='btn'>View in Dashboard</a></center>
            </div>
            <div class='footer'>
                &copy; 2026 Zanzibar Safari. Managed by VILLA System.
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Zanzibar Safari <webmaster@zanzibarsafari.wuaze.com>\r\n" .
               "Reply-To: {$data['email']}\r\n" .
               "X-Mailer: PHP/" . phpversion();

    if (@mail($to, $subject, $message, $headers)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'Mail failed'];
    }
}
