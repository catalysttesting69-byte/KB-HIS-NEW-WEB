<?php
/**
 * admin/tours.php
 * ─────────────────────────────────────────────────────────────
 * CONSOLIDATED TOURS MANAGEMENT FOR VILLA
 * ─────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$message = '';
$msgType = '';
$editTour = null;

// ── Handle Form Actions ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $title       = htmlspecialchars(trim($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8');
    $category    = htmlspecialchars(trim($_POST['category'] ?? ''), ENT_QUOTES, 'UTF-8');
    $price       = htmlspecialchars(trim($_POST['price'] ?? ''), ENT_QUOTES, 'UTF-8');
    $duration    = htmlspecialchars(trim($_POST['duration'] ?? ''), ENT_QUOTES, 'UTF-8');
    $image_url   = htmlspecialchars(trim($_POST['image_url'] ?? ''), ENT_QUOTES, 'UTF-8');
    $description = trim($_POST['description'] ?? '');
    $active      = isset($_POST['active']) ? 1 : 0;
    $tourId      = (int)($_POST['tour_id'] ?? 0);

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO tours (title, category, price, duration, image_url, description, active) VALUES (:t, :c, :p, :d, :i, :desc, :a)");
        $stmt->execute([':t'=>$title, ':c'=>$category, ':p'=>$price, ':d'=>$duration, ':i'=>$image_url, ':desc'=>$description, ':a'=>$active]);
        $message = "New tour added successfully!"; $msgType = "success";
    }

    if ($action === 'update' && $tourId > 0) {
        $stmt = $pdo->prepare("UPDATE tours SET title=:t, category=:c, price=:p, duration=:d, image_url=:i, description=:desc, active=:a WHERE id=:id");
        $stmt->execute([':t'=>$title, ':c'=>$category, ':p'=>$price, ':d'=>$duration, ':i'=>$image_url, ':desc'=>$description, ':a'=>$active, ':id'=>$tourId]);
        $message = "Tour updated successfully!"; $msgType = "success";
    }

    if ($action === 'delete' && $tourId > 0) {
        $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
        $stmt->execute([$tourId]);
        $message = "Tour deleted."; $msgType = "danger";
    }

    if ($action === 'toggle' && $tourId > 0) {
        $newStat = (int)$_POST['current_active'] === 1 ? 0 : 1;
        $pdo->prepare("UPDATE tours SET active=? WHERE id=?")->execute([$newStat, $tourId]);
        $message = "Visibility updated."; $msgType = "success";
    }
}

// ── Edit Load ────────────────────────────────────────────────
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTour = $stmt->fetch();
}

$tours = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Tours Management';
$activePage = 'tours';
require_once __DIR__ . '/../includes/admin_layout.php';
?>

<?php if ($message): ?>
<div style="background:<?= $msgType==='success'?'#d1e7dd':'#f8d7da' ?>; color:<?= $msgType==='success'?'#0f5132':'#842029' ?>; padding:15px; border-radius:8px; margin-bottom:20px;">
    <?= $message ?>
</div>
<?php endif; ?>

<div class="adm-grid adm-grid-2col">
    <!-- List of Tours -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3><i class="fas fa-list"></i> Existing Tours (<?= count($tours) ?>)</h3>
        </div>
        <div class="adm-table-wrap">
            <table>
                <thead>
                    <tr><th>Thumb</th><th>Title / Category</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $t): ?>
                    <?php 
                        // PHP Mirror of the JS transformation for server-side thumbnail safety
                        $thumbUrl = $t['image_url'];
                        if (strpos($thumbUrl, 'drive.google.com') !== false) {
                            preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $thumbUrl, $matches);
                            if (!$matches) preg_match('/id=([a-zA-Z0-9_-]+)/', $thumbUrl, $matches);
                            if ($matches) $thumbUrl = "https://drive.google.com/uc?export=view&id=" . $matches[1];
                        } elseif (strpos($thumbUrl, 'dropbox.com') !== false) {
                            $thumbUrl = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $thumbUrl);
                            $thumbUrl = str_replace('?dl=0', '', $thumbUrl);
                        }
                        
                        // Handle relative vs absolute paths
                        $finalThumb = (strpos($thumbUrl, 'http') === 0) ? $thumbUrl : '../' . $thumbUrl;
                    ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($finalThumb) ?>" style="width:50px; height:40px; border-radius:4px; object-fit:cover;" onerror="this.src='https://placehold.co/100x80/1e1b4b/ffffff?text=Tour';"></td>
                        <td>
                            <strong><?= htmlspecialchars($t['title']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($t['category']) ?></small>
                        </td>
                        <td>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="tour_id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="current_active" value="<?= $t['active'] ?>">
                                <button type="submit" class="badge" style="background:<?= $t['active']?'rgba(56,161,105,0.1)':'rgba(229,62,62,0.1)' ?>; color:<?= $t['active']?'#2f855a':'#e53e3e' ?>; border:none; cursor:pointer; font-family:inherit; font-weight:700;">
                                    <?= $t['active'] ? 'Visible' : 'Hidden' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div style="display:flex; gap:8px">
                                <a href="tours.php?edit=<?= $t['id'] ?>" class="btn btn-primary" style="padding:6px 10px; font-size:11px"><i class="fas fa-edit"></i> Edit</a>
                                <form method="POST" onsubmit="return confirm('Permanently delete this tour?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="tour_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:6px 10px; font-size:11px"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Form -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3><i class="fas <?= $editTour?'fa-edit':'fa-plus-circle' ?>"></i> <?= $editTour ? 'Edit Tour' : 'Add New Tour' ?></h3>
        </div>
        <form method="POST" style="padding:22px">
            <input type="hidden" name="action" value="<?= $editTour ? 'update' : 'add' ?>">
            <?php if ($editTour): ?><input type="hidden" name="tour_id" value="<?= $editTour['id'] ?>"><?php endif; ?>

            <div class="adm-form-group">
                <label>Tour Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($editTour['title'] ?? '') ?>" required placeholder="e.g. Stone Town Journey">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                <div class="adm-form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($editTour['category'] ?? '') ?>" placeholder="e.g. Water Journey">
                </div>
                <div class="adm-form-group">
                    <label>Price</label>
                    <input type="text" name="price" value="<?= htmlspecialchars($editTour['price'] ?? '') ?>" placeholder="From $35">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                <div class="adm-form-group">
                    <label>Duration</label>
                    <input type="text" name="duration" value="<?= htmlspecialchars($editTour['duration'] ?? '') ?>" placeholder="Half Day / 1 Day">
                </div>
                <div class="adm-form-group">
                    <label>Image URL (Supports GDrive/Dropbox links)</label>
                    <input type="text" name="image_url" id="tour_image_url" value="<?= htmlspecialchars($editTour['image_url'] ?? '') ?>" placeholder="pictures/example.jpg" onchange="this.value = transformImageURL(this.value)">
                </div>
            </div>

            <div class="adm-form-group">
                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($editTour['description'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="active" <?= ($editTour['active'] ?? 1) ? 'checked' : '' ?>>
                    Active (Show on Website)
                </label>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; height:46px; font-size:15px">
                <?= $editTour ? 'Save Changes' : 'Create Tour' ?>
            </button>
            <?php if ($editTour): ?>
                <a href="tours.php" style="display:block; text-align:center; margin-top:12px; color:var(--text-muted); font-size:13px">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
