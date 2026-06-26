<?php
/**
 * includes/admin_layout.php
 * ─────────────────────────────────────────────────────────────
 * FINAL PREMIUM UI — EXACTLY matching Tanzania Safari project.
 * ─────────────────────────────────────────────────────────────
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> — Zanzibar Safari VILLA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* EXACT CSS FROM TANZANIA SAFARI PROJECT */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --sidebar-w:   260px;
            --green-dark:  #0b2218;
            --green:       #1a3c2e;
            --green-light: #2d6a4f;
            --gold:        #c8a96e;
            --gold-light:  #e2c185;
            --white:       #ffffff;
            --bg:          #f4f7f6;
            --card-bg:     #ffffff;
            --text:        #0b2218;
            --text-muted:  #718096;
            --border:      #e2e8f0;
            --danger:      #e53e3e;
            --success:     #38a169;
        }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* SIDEBAR (Original Forest Green) */
        .adm-sidebar { width: var(--sidebar-w); background: var(--green-dark); position: fixed; top: 0; left: 0; height: 100vh; display: flex; flex-direction: column; z-index: 1000; box-shadow: 4px 0 15px rgba(0,0,0,0.1); }
        .adm-brand { padding: 32px 24px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .adm-brand h2 { color: var(--gold); font-size: 18px; font-weight: 700; letter-spacing: -0.5px; }
        .adm-nav { padding: 24px 16px; flex: 1; }
        .adm-nav-label { color: rgba(255,255,255,0.25); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin: 24px 12px 12px; display: block; }
        .adm-nav a { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.65); text-decoration: none; font-size: 14px; font-weight: 500; padding: 12px 16px; border-radius: 12px; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .adm-nav a:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(4px); }
        .adm-nav a.active { background: var(--gold); color: var(--green-dark); font-weight: 700; box-shadow: 0 4px 12px rgba(200, 169, 110, 0.3); }
        
        /* MAIN CONTENT */
        .adm-main { margin-left: var(--sidebar-w); flex: 1; padding: 40px; width: calc(100% - var(--sidebar-w)); }
        .adm-topbar { background: transparent; padding: 0 0 32px 0; display: flex; justify-content: space-between; align-items: center; }
        .adm-topbar h1 { font-size: 24px; font-weight: 700; color: var(--green); }
        
        /* CARDS & TABLES */
        .adm-card { background: var(--card-bg); border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01); border: 1px solid var(--border); overflow: hidden; margin-bottom: 32px; transition: transform 0.3s; }
        .adm-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); }
        .adm-card-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc; }
        .adm-card-header h3 { font-size: 16px; font-weight: 700; color: var(--green); display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #fafbfc; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 16px 24px; text-align: left; font-weight: 700; }
        td { padding: 20px 24px; border-top: 1px solid var(--border); vertical-align: middle; font-size: 14px; }
        
        /* BUTTONS */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; border-radius: 12px; font-size: 13px; font-weight: 600; padding: 10px 20px; cursor: pointer; text-decoration: none; transition: 0.3s; }
        .btn-primary { background: var(--green); color: #fff; }
        .btn-gold { background: var(--gold); color: var(--green-dark); }
        .btn-gold:hover { background: var(--gold-light); transform: translateY(-1px); }
        .btn-danger { background: rgba(229, 62, 62, 0.1); color: var(--danger); }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        
        /* BADGES */
        .badge { padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
        .badge-pending { background: rgba(226, 161, 69, 0.1); color: #b7791f; }
        .badge-confirmed { background: rgba(56, 161, 105, 0.1); color: #2f855a; }
        
        /* FORMS */
        .adm-form-group { margin-bottom: 24px; }
        .adm-form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--green); margin-bottom: 8px; }
        .adm-form-group input, .adm-form-group select, .adm-form-group textarea { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 14px; outline: none; transition: 0.3s; }
        .adm-form-group input:focus { border-color: var(--gold); box-shadow: 0 0 0 4px rgba(200, 169, 110, 0.1); }
        
        /* GRID */
        .adm-grid-2col { display: grid; grid-template-columns: 1fr 400px; gap: 32px; }
        
        /* STAT CARDS */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 24px; border-radius: 20px; border: 1px solid var(--border); display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-icon.green { background: rgba(26, 60, 46, 0.1); color: var(--green); }
        .stat-icon.gold { background: rgba(200, 169, 110, 0.1); color: var(--gold); }
        .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; }
        .stat-value { font-size: 24px; font-weight: 700; color: var(--green); }

        /* RESPONSIVE REFINEMENTS */
        @media (max-width: 1024px) {
            :root { --sidebar-w: 80px; }
            body { position: relative; }
            .adm-brand h2, .adm-nav-label, .span-text, .adm-user div:first-child { display: none; }
            .adm-nav a { justify-content: center; padding: 14px; }
            .adm-nav i { font-size: 20px; margin: 0; }
            .adm-topbar h1 { font-size: 20px; }
        }

        @media (max-width: 768px) {
            .adm-main { padding: 24px 15px; }
            .adm-topbar { flex-direction: column; align-items: flex-start; gap: 15px; }
            .stat-grid { grid-template-columns: 1fr; }
            .adm-grid-2col { grid-template-columns: 1fr; }
            
            /* Table Responsiveness */
            .adm-table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            table { min-width: 600px; }
        }

        /* Helper for text visibility in sidebar */
        .adm-nav a span { display: inline-block; }
        @media (max-width: 1024px) {
            .adm-nav a span { display: none; }
        }
    </style>
</head>
<body>
<aside class="adm-sidebar">
    <div class="adm-brand" style="text-align:center; padding: 20px 10px;">
        <a href="dashboard.php">
            <img src="../pictures/zanzibarSafarilogo.png" alt="Zanzibar Safari" style="max-width: 160px; height: auto; filter: brightness(0) invert(1);">
        </a>
    </div>
    <nav class="adm-nav">
        <span class="adm-nav-label">Main</span>
        <a href="dashboard.php" title="Dashboard" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
        <a href="bookings.php" title="Bookings" class="<?= ($activePage ?? '') === 'bookings' ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> <span>Bookings</span></a>
        
        <span class="adm-nav-label">Management</span>
        <a href="tours.php" title="Tours" class="<?= ($activePage ?? '') === 'tours' ? 'active' : '' ?>"><i class="fas fa-map-marked-alt"></i> <span>Tours Catalog</span></a>
        
        <span class="adm-nav-label">Live Site</span>
        <a href="../index.html" title="View Site" target="_blank"><i class="fas fa-external-link-alt"></i> <span>View Website</span></a>
        
        <div style="margin-top: auto; padding: 20px 12px;">
            <a href="logout.php" title="Logout" style="color:#f87171;"><i class="fas fa-power-off"></i> <span>Sign Out</span></a>
        </div>
    </nav>
</aside>
<div class="adm-main">
    <div class="adm-topbar">
        <h1><?= htmlspecialchars($pageTitle ?? 'Overview') ?></h1>
        <div class="adm-user" style="display:flex; align-items:center; gap:12px;">
            <div style="text-align:right">
                <div style="font-weight:700; font-size:14px; color:var(--green)"><?= getAdminName() ?></div>
                <div style="font-size:11px; color:var(--text-muted)">Project Administrator</div>
            </div>
            <div style="width:40px; height:40px; background:var(--gold); border-radius:12px; display:flex; align-items:center; justify-content:center; color:var(--green-dark); font-weight:700; box-shadow: 0 4px 10px rgba(200, 169, 110, 0.2);">Z</div>
        </div>
    </div>
