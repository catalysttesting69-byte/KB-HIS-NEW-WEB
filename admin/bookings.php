<?php
/**
 * admin/bookings.php
 * ─────────────────────────────────────────────────────────────
 * MANAGES ALL VILLA BOOKING ENQUIRIES
 * ─────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$message = '';
$msgType = '';

// ── Update Booking Status ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['booking_id'] ?? 0);

    if ($action === 'update_status' && $id > 0) {
        $status = $_POST['status'] ?? 'pending';
        $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $id]);
        $message = "Status updated successfully."; $msgType = "success";
    }

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$id]);
        $message = "Booking deleted."; $msgType = "danger";
    }
}

$bookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Bookings Management';
$activePage = 'bookings';
require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
    .booking-details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; padding: 10px; background: #f9f9f9; border-radius: 8px; font-size: 13px; line-height: 1.6; }
    .booking-details-grid b { color: var(--green); display: block; margin-bottom: 2px; }
    .msg-box { background: #fff; padding: 12px; border: 1px solid #eee; border-radius: 6px; margin-top: 10px; font-style: italic; }
</style>

<?php foreach ($bookings as $b): ?>
<div class="adm-card" style="margin-bottom:20px;">
    <div class="adm-card-header" style="background:rgba(0,0,0,0.02)">
        <div>
            <span style="font-weight:700; color:var(--gold)">#<?= $b['id'] ?> — <?= htmlspecialchars($b['name']) ?></span>
            <span class="badge badge-<?= $b['status'] ?>" style="margin-left:10px"><?= ucfirst($b['status']) ?></span>
        </div>
        <div style="font-size:12px; color:var(--text-muted)"><?= date('d M Y, H:i', strtotime($b['created_at'])) ?></div>
    </div>
    
    <div style="padding:20px">
        <div class="booking-details-grid">
            <div><b>Email</b> <?= htmlspecialchars($b['email']) ?></div>
            <div><b>Phone</b> <?= htmlspecialchars($b['phone'] ?: 'N/A') ?></div>
            <div><b>Selected Tour</b> <?= htmlspecialchars($b['tour_name'] ?: 'Custom Journey') ?></div>
            <div><b>Travel Date</b> <?= $b['travel_date'] ? date('d M Y', strtotime($b['travel_date'])) : 'Any' ?></div>
            <div><b>Travelers</b> <?= (int)$b['num_people'] ?> People</div>
        </div>

        <?php if ($b['message']): ?>
        <div class="msg-box">
            <b><i class="fas fa-comment-dots"></i> Message:</b><br>
            <?= nl2br(htmlspecialchars($b['message'])) ?>
        </div>
        <?php endif; ?>

        <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:flex-end;">
            <form method="POST" style="display:flex; align-items:center; gap:10px">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <label style="font-size:12px; font-weight:600">Update Status:</label>
                <select name="status" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px">
                    <option value="pending" <?= $b['status']==='pending'?'selected':'' ?>>Pending</option>
                    <option value="confirmed" <?= $b['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                    <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
            </form>

            <form method="POST" onsubmit="return confirm('Delete this record?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <button type="submit" style="background:none; border:none; color:#dc3545; cursor:pointer; font-size:13px"><i class="fas fa-trash"></i> Permanently Delete</button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($bookings)): ?>
<div class="adm-card" style="padding:40px; text-align:center; color:var(--text-muted)">
    <i class="fas fa-folder-open" style="font-size:40px; margin-bottom:15px; opacity:0.3"></i>
    <p>No booking enquiries found yet.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
