<?php
/**
 * admin/dashboard.php
 * ─────────────────────────────────────────────────────────────
 * SIMPLIFIED VILLA DASHBOARD
 * ─────────────────────────────────────────────────────────────
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$totalBookings   = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$activeTours     = $pdo->query("SELECT COUNT(*) FROM tours WHERE active = 1")->fetchColumn();

// Latest 8 bookings
$recent = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 8")->fetchAll();

$pageTitle = 'Overview Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- Stat Grid -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-alt"></i></div>
        <div>
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value"><?= (int)$totalBookings ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold"><i class="fas fa-hourglass-half"></i></div>
        <div>
            <div class="stat-label">Pending Enquiries</div>
            <div class="stat-value"><?= (int)$pendingBookings ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green" style="background:rgba(26, 60, 46, 0.05); color:var(--green-light)">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <div>
            <div class="stat-label">Tours in Catalog</div>
            <div class="stat-value"><?= (int)$activeTours ?></div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3><i class="fas fa-history"></i> Recent Enquiries</h3>
        <a href="bookings.php" class="btn btn-gold btn-sm" style="padding: 6px 14px;">View All Bookings</a>
    </div>
    <div class="adm-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Client Name</th>
                    <th>Requested Tour</th>
                    <th>Status</th>
                    <th>Date Received</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $b): ?>
                <tr>
                    <td><strong>#<?= $b['id'] ?></strong></td>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td><?= htmlspecialchars($b['tour_name'] ?: 'Custom') ?></td>
                    <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted)">No bookings yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
