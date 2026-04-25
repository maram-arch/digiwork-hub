<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Backoffice — DigiWork Hub</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: Arial, sans-serif;
    background: #F0F4F8;
    display: flex;
    min-height: 100vh;
}

/* ── SIDEBAR ── */
.sidebar {
    width: 230px;
    background: #111827;
    color: #fff;
    min-height: 100vh;
    padding: 0;
    position: fixed;
    top: 0; left: 0;
    display: flex;
    flex-direction: column;
}
.sidebar-logo {
    padding: 22px 20px 16px;
    border-bottom: 1px solid #1F2937;
}
.sidebar-logo span {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    letter-spacing: .3px;
}
.sidebar-logo small {
    display: block;
    font-size: 11px;
    color: #6B7280;
    margin-top: 2px;
}
.sidebar nav { padding: 14px 0; flex: 1; }
.nav-label {
    font-size: 10px;
    font-weight: 700;
    color: #4B5563;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 10px 20px 6px;
}
.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 20px;
    color: #9CA3AF;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all .15s;
}
.sidebar a:hover, .sidebar a.active {
    background: #1F2937;
    color: #fff;
    border-left-color: #2F80ED;
}
.sidebar-footer {
    padding: 14px 20px;
    border-top: 1px solid #1F2937;
    font-size: 11px;
    color: #4B5563;
}

/* ── MAIN CONTENT ── */
.main-wrap {
    margin-left: 230px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.topbar {
    background: #fff;
    padding: 14px 28px;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky; top: 0; z-index: 50;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.topbar h1 {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}
.topbar-right { display: flex; align-items: center; gap: 14px; }
.topbar-badge {
    font-size: 12px;
    color: #6B7280;
    background: #F3F4F6;
    padding: 4px 12px;
    border-radius: 20px;
}

.content-area { padding: 24px 28px; }

/* ── CARD ── */
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    margin-bottom: 22px;
    overflow: hidden;
}
.card-header {
    padding: 18px 22px;
    border-bottom: 1px solid #F3F4F6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.card-header h2 {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}
.card-body { padding: 22px; }

/* ── STATS GRID ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px 22px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    border-left: 4px solid var(--accent, #2F80ED);
}
.stat-card .stat-label { font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
.stat-card .stat-val   { font-size: 28px; font-weight: 800; color: #111827; }
.stat-card .stat-icon  { font-size: 22px; float: right; margin-top: -4px; }

/* ── TABLE ── */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table th {
    background: #F9FAFB;
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid #E5E7EB;
}
.data-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #F3F4F6;
    color: #374151;
    vertical-align: middle;
}
.data-table tr:hover td { background: #FAFAFA; }
.data-table tr:last-child td { border-bottom: none; }

/* ── BADGES ── */
.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.badge-blue   { background: #EFF6FF; color: #1D4ED8; }
.badge-green  { background: #ECFDF5; color: #065F46; }
.badge-red    { background: #FEF2F2; color: #991B1B; }
.badge-purple { background: #F5F3FF; color: #5B21B6; }

/* ── BUTTONS ── */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; font-family: inherit; transition: all .15s; }
.btn-primary { background: #2F80ED; color: #fff; }
.btn-primary:hover { background: #1a6fd4; }
.btn-danger  { background: #EF4444; color: #fff; }
.btn-danger:hover  { background: #DC2626; }
.btn-outline { background: #fff; color: #374151; border: 1px solid #D1D5DB; }
.btn-outline:hover { border-color: #2F80ED; color: #2F80ED; }
.btn-sm { padding: 4px 10px; font-size: 12px; }

/* ── EMPTY ── */
.empty-row td { text-align: center; padding: 40px; color: #9CA3AF; font-style: italic; }

/* ── FLASH ── */
.flash { padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; font-weight: 500; }
.flash-success { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
.flash-error   { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <span>⚙️ DigiWork Hub</span>
        <small>Administration</small>
    </div>
    <nav>
        <div class="nav-label">Tableau de bord</div>
        <a href="index.php?action=dashboard">📊 Dashboard</a>
        <div class="nav-label">Contenu</div>
        <a href="index.php?action=listPublications">📝 Publications</a>
        <a href="index.php?action=listCommentaires">💬 Commentaires</a>
        <div class="nav-label">Navigation</div>
        <a href="index.php?action=front">🏠 Voir le Forum</a>
    </nav>
    <div class="sidebar-footer">DigiWork Hub © 2025</div>
</div>

<!-- MAIN -->
<div class="main-wrap">
    <div class="topbar">
        <h1>Backoffice</h1>
        <div class="topbar-right">
            <span class="topbar-badge">👤 Admin</span>
            <a href="index.php?action=front" class="btn btn-outline btn-sm">🏠 Frontoffice</a>
        </div>
    </div>
    <div class="content-area">