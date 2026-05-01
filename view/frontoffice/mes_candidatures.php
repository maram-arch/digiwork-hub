<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';
 
$dbTemp  = Config::getConnexion();
$id_user = (int)$dbTemp->query(
    "SELECT id_user FROM user ORDER BY id_user ASC LIMIT 1"
)->fetchColumn();
if ($id_user <= 0) $id_user = 14;
 
$controller   = new CandidatureController();
$candidatures = $controller->getCandidaturesByUser($id_user);

$dateRecherche   = trim($_GET['date'] ?? '');
$statutRecherche = trim($_GET['statut'] ?? '');
$triCandidature  = $_GET['tri'] ?? 'Date';
$ordreTri        = $_GET['ordre'] ?? 'desc';

$trisAutorises = [
    'Date' => 'Date',
    'Statut' => 'Statut',
];

if (!array_key_exists($triCandidature, $trisAutorises)) {
    $triCandidature = 'Date';
}

if (!in_array($ordreTri, ['asc', 'desc'], true)) {
    $ordreTri = 'desc';
}

$candidatures = array_values(array_filter($candidatures, function ($c) use ($dateRecherche, $statutRecherche) {
    $matchDate = ($dateRecherche === '' || ($c['Date'] ?? '') === $dateRecherche);
    $matchStatut = ($statutRecherche === '' || stripos($c['Statut'] ?? '', $statutRecherche) !== false);
    return $matchDate && $matchStatut;
}));

usort($candidatures, function ($a, $b) use ($triCandidature, $ordreTri) {
    if ($triCandidature === 'Date') {
        $valueA = strtotime($a['Date'] ?? '') ?: 0;
        $valueB = strtotime($b['Date'] ?? '') ?: 0;
        $result = $valueA <=> $valueB;
    } else {
        $result = strcmp(strtolower($a['Statut'] ?? ''), strtolower($b['Statut'] ?? ''));
    }

    return $ordreTri === 'desc' ? -$result : $result;
});
 
$message     = "";
$messageType = "";
if (isset($_GET['status'], $_GET['msg'])) {
    $messageType = ($_GET['status'] === 'success') ? 'success' : 'danger';
    $message     = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes candidatures - DigiWork Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
 
        :root {
            --brand:     #435ebe;
            --brand-lt:  #eef1fb;
            --brand-dk:  #2f44a0;
            --success:   #10b981;
            --success-lt:#ecfdf5;
            --danger:    #ef4444;
            --danger-lt: #fff1f1;
            --warn:      #f59e0b;
            --warn-lt:   #fffbeb;
            --bg:        #f0f2f9;
            --surface:   #ffffff;
            --text:      #1e2535;
            --muted:     #7c8db0;
            --border:    #e8ecf6;
            --radius:    16px;
            --shadow:    0 4px 24px rgba(67,94,190,.10);
            --shadow-hov:0 8px 36px rgba(67,94,190,.18);
        }
 
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
 
        /* NAVBAR */
        .navbar { background:var(--surface); padding:0 36px; height:64px; display:flex; align-items:center; gap:24px; justify-content:space-between; box-shadow:0 1px 0 var(--border); position:sticky; top:0; z-index:100; }
        .navbar-left { display:flex; align-items:center; gap:24px; }
        .navbar-right { display:flex; align-items:center; gap:24px; }
        .navbar img { height:38px; flex-shrink:0; }
        .nav-links { display:flex; gap:4px; align-items:center; }
        .nav-link { color:var(--muted); text-decoration:none; font-size:14px; font-weight:500; padding:8px 14px; border-radius:10px; transition:all .2s; }
        .nav-link:hover { background:var(--brand-lt); color:var(--brand); }
        .nav-link.active { background:var(--brand-lt); color:var(--brand); font-weight:700; }
        .btn-postuler { background:var(--brand); color:#fff; text-decoration:none; font-size:13px; font-weight:700; padding:9px 20px; border-radius:10px; transition:background .2s; flex-shrink:0; }
        .btn-postuler:hover { background:var(--brand-dk); }
 
        /* CONTAINER */
        .container { max-width:1200px; margin:0 auto; padding:40px 24px; }
 
        /* PAGE HEADER */
        .page-header { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px; }
        .page-header h1 { font-size:28px; font-weight:800; letter-spacing:-.5px; margin-bottom:4px; }
        .page-header p  { font-size:14px; color:var(--muted); }
        .count-pill { background:var(--brand-lt); color:var(--brand); font-size:13px; font-weight:700; padding:6px 16px; border-radius:20px; }
 
        /* ALERT */
        .alert { padding:14px 18px; border-radius:12px; margin-bottom:28px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
        .alert-success { background:var(--success-lt); color:#065f46; border-left:4px solid var(--success); }
        .alert-danger  { background:var(--danger-lt);  color:#991b1b; border-left:4px solid var(--danger); }

        /* TOOLS */
        .tools-grid { display:grid; grid-template-columns:1fr 1fr auto; gap:16px; align-items:stretch; margin-bottom:28px; }
        .tool-box { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:18px; }
        .tool-title { font-size:13px; font-weight:800; color:var(--text); margin-bottom:12px; }
        .tool-row { display:grid; grid-template-columns:1fr 1fr auto; gap:12px; align-items:end; }
        .tool-row.two { grid-template-columns:1fr 1fr auto; }
        .tool-control { border:1.5px solid var(--border); border-radius:10px; padding:10px 12px; width:100%; font-size:13px; font-family:inherit; background:#fafbff; }
        .tool-control:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(67,94,190,.12); }
        .btn-tool { background:var(--brand); color:#fff; border:none; border-radius:10px; padding:11px 20px; font-size:13px; font-weight:800; font-family:inherit; cursor:pointer; text-decoration:none; display:inline-flex; justify-content:center; align-items:center; white-space:nowrap; }
        .btn-tool:hover { background:var(--brand-dk); }
        .btn-pdf { height:100%; min-height:78px; background:var(--success); }
        .btn-pdf:hover { background:#059669; }
 
        /* CARDS GRID */
        .cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:20px; }
 
        /* CARD */
        .cand-card {
            background:var(--surface);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            border:1px solid var(--border);
            overflow:hidden;
            display:flex;
            flex-direction:column;
            transition:transform .2s,box-shadow .2s;
            animation:fadeUp .35s ease both;
        }
        .cand-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-hov); }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .cand-card:nth-child(1){animation-delay:.05s}
        .cand-card:nth-child(2){animation-delay:.10s}
        .cand-card:nth-child(3){animation-delay:.15s}
        .cand-card:nth-child(4){animation-delay:.20s}
        .cand-card:nth-child(5){animation-delay:.25s}
        .cand-card:nth-child(6){animation-delay:.30s}
 
        /* accent bar top */
        .cand-card::before { content:''; display:block; height:4px; background:var(--accent,var(--brand)); }
        .cand-card.accepte { --accent:var(--success); }
        .cand-card.refuse  { --accent:var(--danger); }
        .cand-card.attente { --accent:var(--warn); }
 
        /* CARD HEAD */
        .card-head { padding:20px 20px 14px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
        .type-pill { display:inline-block; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; background:var(--brand-lt); color:var(--brand); margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
        .offre-titre { font-size:16px; font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .offre-addr  { font-size:12px; color:var(--muted); margin-top:4px; display:flex; align-items:center; gap:4px; }
 
        .status-badge { flex-shrink:0; display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; white-space:nowrap; }
        .status-badge.accepte { background:var(--success-lt); color:#065f46; }
        .status-badge.refuse  { background:var(--danger-lt);  color:#991b1b; }
        .status-badge.attente { background:var(--warn-lt);    color:#92400e; }
 
        /* CARD BODY */
        .card-body { padding:0 20px 16px; flex:1; display:flex; flex-direction:column; gap:12px; }
        .divider { height:1px; background:var(--border); margin:0 -20px; }
        .info-row { display:flex; gap:8px; flex-wrap:wrap; }
        .chip { display:inline-flex; align-items:center; gap:5px; background:#f5f7ff; border:1px solid var(--border); border-radius:8px; padding:5px 10px; font-size:12px; color:var(--muted); font-weight:500; }
        .lettre-prev { background:#f8faff; border-radius:10px; padding:12px 14px; font-size:13px; color:var(--muted); line-height:1.6; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; border-left:3px solid var(--brand-lt); }
        .cv-link { display:inline-flex; align-items:center; gap:6px; background:var(--brand-lt); color:var(--brand); border-radius:8px; padding:6px 12px; font-size:12px; font-weight:600; text-decoration:none; transition:background .15s; width:fit-content; }
        .cv-link:hover { background:#d6ddf8; }
 
        /* CARD FOOT */
        .card-foot { padding:14px 20px; border-top:1px solid var(--border); background:#fafbff; display:flex; gap:8px; justify-content:flex-end; align-items:center; }
        .btn-a { display:inline-flex; align-items:center; gap:6px; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:all .15s; }
        .btn-edit { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
        .btn-edit:hover { background:#ffedd5; }
        .btn-del  { background:var(--danger-lt); color:var(--danger); border:1px solid #fecaca; }
        .btn-del:hover { background:#ffe4e4; }
        .treated-txt { font-size:12px; color:var(--muted); font-style:italic; }
 
        /* EMPTY */
        .empty-wrap { grid-column:1/-1; text-align:center; padding:80px 24px; background:var(--surface); border-radius:var(--radius); border:2px dashed var(--border); }
        .empty-ic { width:80px; height:80px; background:var(--brand-lt); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px; }
        .empty-wrap h3 { font-size:20px; font-weight:800; margin-bottom:8px; }
        .empty-wrap p  { color:var(--muted); font-size:14px; margin-bottom:24px; }
        .btn-voir { display:inline-block; background:var(--brand); color:#fff; text-decoration:none; padding:12px 28px; border-radius:10px; font-weight:700; font-size:14px; transition:background .2s; }
        .btn-voir:hover { background:var(--brand-dk); }
 
        /* MODALES */
        .modal-ov { display:none; position:fixed; inset:0; background:rgba(15,20,50,.55); backdrop-filter:blur(3px); z-index:9999; justify-content:center; align-items:center; padding:16px; }
        .modal-ov.show { display:flex; }
        .modal-box { background:var(--surface); border-radius:20px; width:100%; max-width:520px; max-height:92vh; overflow-y:auto; box-shadow:0 32px 80px rgba(0,0,0,.28); animation:mIn .22s cubic-bezier(.34,1.56,.64,1); }
        @keyframes mIn { from{transform:translateY(-24px) scale(.97);opacity:0} to{transform:translateY(0) scale(1);opacity:1} }
        .modal-top { display:flex; justify-content:space-between; align-items:center; padding:20px 24px 16px; border-bottom:1px solid var(--border); }
        .modal-top h5 { font-size:16px; font-weight:700; }
        .btn-x { background:#f1f3f9; border:none; border-radius:8px; width:32px; height:32px; font-size:16px; cursor:pointer; color:var(--muted); display:flex; align-items:center; justify-content:center; transition:all .15s; }
        .btn-x:hover { background:var(--danger-lt); color:var(--danger); }
        .modal-body { padding:22px 24px; }
        .modal-foot { padding:16px 24px; border-top:1px solid var(--border); background:#f8f9ff; border-radius:0 0 20px 20px; display:flex; justify-content:flex-end; gap:10px; }
        .field-lbl { font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; display:block; }
        .m-in { font-size:14px; font-family:inherit; border-radius:10px; border:1.5px solid var(--border); width:100%; padding:10px 14px; resize:vertical; transition:border-color .2s; background:#fafbff; }
        .m-in:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(67,94,190,.12); }
        .char-c { font-size:12px; color:var(--muted); margin-top:4px; }
        .err { font-size:12px; color:var(--danger); margin-top:4px; display:none; }
        .err.show { display:block; }
        .btn-cancel { background:#f1f3f9; border:none; color:var(--muted); border-radius:10px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit; }
        .btn-cancel:hover { background:var(--border); }
        .btn-save { background:var(--brand); border:none; color:#fff; border-radius:10px; padding:10px 24px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; }
        .btn-save:hover { background:var(--brand-dk); }
        .del-ic { width:68px; height:68px; background:var(--danger-lt); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:28px; }
        .btn-del-c { background:var(--danger); color:#fff; border:none; border-radius:10px; padding:10px 28px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        .btn-del-c:hover { background:#dc2626; }
 
        @media(max-width:640px){
            .container{padding:24px 16px;}
            .cards-grid{grid-template-columns:1fr;}
            .tools-grid{grid-template-columns:1fr;}
            .tool-row,.tool-row.two{grid-template-columns:1fr;}
            .navbar{padding:0 16px;}
            .nav-links{display:none;}
            .page-header{flex-direction:column;align-items:flex-start;}
        }
    </style>
</head>
<body>
 
<nav class="navbar">
    <!-- Logo et bouton Postuler à gauche -->
    <div class="navbar-left">
        <img src="assets/img/logo/logo.png" style="width:230px;">
        <a href="offres.php" class="btn-postuler">+ Postuler</a>
    </div>
    <!-- Liens de navigation à droite -->
    <div class="navbar-right">
        <div class="nav-links">
            <a href="index.php"  class="nav-link">Accueil</a>
            <a href="offres.php" class="nav-link">Offres</a>
            <a href="mes_candidatures.php" class="nav-link active">Mes candidatures</a>
        </div>
    </div>
</nav>
 
<div class="container">
 
    <div class="page-header">
        <div>
            <h1>Mes candidatures</h1>
            <p>Suivez l'état de toutes vos candidatures en temps réel</p>
        </div>
        <span class="count-pill"><?= count($candidatures) ?> candidature<?= count($candidatures) > 1 ? 's' : '' ?></span>
    </div>
 
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
    </div>
    <?php endif; ?>
 
    <div class="tools-grid">
        <form class="tool-box" method="GET" action="recherchecondidature.php">
            <div class="tool-title">Recherche candidature</div>
            <div class="tool-row">
                <div>
                    <label class="field-lbl" for="date">Date</label>
                    <input class="tool-control" type="date" id="date" name="date" value="<?= htmlspecialchars($dateRecherche) ?>">
                </div>
                <div>
                    <label class="field-lbl" for="statut">Statut</label>
                    <select class="tool-control" id="statut" name="statut">
                        <option value="" <?= $statutRecherche === '' ? 'selected' : '' ?>>Tous</option>
                        <option value="en attente" <?= $statutRecherche === 'en attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="accept" <?= $statutRecherche === 'accept' ? 'selected' : '' ?>>Accepte</option>
                        <option value="refus" <?= $statutRecherche === 'refus' ? 'selected' : '' ?>>Refuse</option>
                    </select>
                </div>
                <input type="hidden" name="tri" value="<?= htmlspecialchars($triCandidature) ?>">
                <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordreTri) ?>">
                <button class="btn-tool" type="submit">Recherche</button>
            </div>
        </form>

        <form class="tool-box" method="GET" action="triecondidature.php">
            <div class="tool-title">Tri candidature</div>
            <div class="tool-row two">
                <div>
                    <label class="field-lbl" for="tri">Trier par</label>
                    <select class="tool-control" id="tri" name="tri">
                        <?php foreach ($trisAutorises as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $triCandidature === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="field-lbl" for="ordre">Ordre</label>
                    <select class="tool-control" id="ordre" name="ordre">
                        <option value="asc" <?= $ordreTri === 'asc' ? 'selected' : '' ?>>Croissant</option>
                        <option value="desc" <?= $ordreTri === 'desc' ? 'selected' : '' ?>>Decroissant</option>
                    </select>
                </div>
                <input type="hidden" name="date" value="<?= htmlspecialchars($dateRecherche) ?>">
                <input type="hidden" name="statut" value="<?= htmlspecialchars($statutRecherche) ?>">
                <button class="btn-tool" type="submit">Trie</button>
            </div>
        </form>

        <a class="btn-tool btn-pdf"
           href="pdf.php?date=<?= urlencode($dateRecherche) ?>&statut=<?= urlencode($statutRecherche) ?>&tri=<?= urlencode($triCandidature) ?>&ordre=<?= urlencode($ordreTri) ?>">
            Telecharger mes candidatures
        </a>
    </div>

    <div class="cards-grid">
 
    <?php if (count($candidatures) > 0): ?>
        <?php foreach ($candidatures as $c):
            $s = $c['Statut'] ?? 'en attente';
            $sc = (strpos($s,'accept')!==false) ? 'accepte'
                : ((strpos($s,'refus')!==false)  ? 'refuse' : 'attente');
            $sl = $sc==='accepte' ? '✔ Accepté' : ($sc==='refuse' ? '✘ Refusé' : '⏳ En attente');
        ?>
        <div class="cand-card <?= $sc ?>">
 
            <div class="card-head">
                <div style="flex:1;min-width:0">
                    <div class="type-pill"><?= htmlspecialchars($c['type_offre'] ?? 'Offre') ?></div>
                    <div class="offre-titre" title="<?= htmlspecialchars($c['titre_offre'] ?? '') ?>">
                        <?= htmlspecialchars($c['titre_offre'] ?? '—') ?>
                    </div>
                    <?php if (!empty($c['adresse'])): ?>
                    <div class="offre-addr">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= htmlspecialchars($c['adresse']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <span class="status-badge <?= $sc ?>"><?= $sl ?></span>
            </div>
 
            <div class="card-body">
                <div class="divider"></div>
                <div class="info-row">
                    <span class="chip">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Postulé le <?= htmlspecialchars($c['Date'] ?? '—') ?>
                    </span>
                    <?php if (!empty($c['date_limite'])): ?>
                    <span class="chip">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Limite : <?= htmlspecialchars($c['date_limite']) ?>
                    </span>
                    <?php endif; ?>
                </div>
 
                <?php if (!empty($c['Lettre'])): ?>
                <div class="lettre-prev"><?= htmlspecialchars($c['Lettre']) ?></div>
                <?php endif; ?>
 
                <?php if (!empty($c['cv'])): ?>
                <a href="assets/uploads/cv/<?= htmlspecialchars($c['cv']) ?>"
                   target="_blank" class="cv-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Voir mon CV
                </a>
                <?php endif; ?>
            </div>
 
            <div class="card-foot">
                <?php if ($sc === 'attente'): ?>
                <button class="btn-a btn-edit"
                    data-id_user="<?= (int)$c['id_user'] ?>"
                    data-id_offer="<?= (int)$c['id_offer'] ?>"
                    data-lettre="<?= htmlspecialchars($c['Lettre'] ?? '', ENT_QUOTES) ?>"
                    onclick="openEditModal(this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Modifier
                </button>
                <button class="btn-a btn-del"
                    data-id_user="<?= (int)$c['id_user'] ?>"
                    data-id_offer="<?= (int)$c['id_offer'] ?>"
                    data-titre="<?= htmlspecialchars($c['titre_offre'] ?? '', ENT_QUOTES) ?>"
                    onclick="openDeleteModal(this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    Retirer
                </button>
                <?php else: ?>
                <span class="treated-txt">Candidature traitée — aucune modification possible</span>
                <?php endif; ?>
            </div>
 
        </div>
        <?php endforeach; ?>
 
    <?php else: ?>
        <div class="empty-wrap">
            <div class="empty-ic">📭</div>
            <h3>Aucune candidature</h3>
            <p>Vous n'avez pas encore postulé à une offre.</p>
            <a href="offres.php" class="btn-voir">Découvrir les offres</a>
        </div>
    <?php endif; ?>
 
    </div>
</div>
 
<!-- MODALE MODIFIER -->
<div class="modal-ov" id="editModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>✏️ Modifier ma candidature</h5>
            <button class="btn-x" onclick="closeEditModal()">✕</button>
        </div>
        <form method="POST" action="modifierCandidature.php" id="editForm"
              enctype="multipart/form-data" onsubmit="return validateEdit(event)">
            <input type="hidden" name="id_user"  id="edit_id_user">
            <input type="hidden" name="id_offer" id="edit_id_offer">
            <div class="modal-body">
                <div style="margin-bottom:18px">
                    <label class="field-lbl">📎 Nouveau CV (laisser vide pour garder l'ancien)</label>
                    <input type="file" class="m-in" name="cv" id="edit_cv"
                           accept=".pdf,.doc,.docx" style="padding:8px">
                    <div class="char-c">PDF, DOC, DOCX — max 2 Mo</div>
                    <div class="err" id="err_cv"></div>
                </div>
                <div>
                    <label class="field-lbl">📝 Lettre de motivation *</label>
                    <textarea class="m-in" name="lettre_motivation" id="edit_lettre"
                              rows="6" oninput="updateEditCount()"
                              placeholder="Décrivez votre motivation..."></textarea>
                    <div class="char-c" id="edit_count">0 / 2000 caractères</div>
                    <div class="err" id="err_lettre"></div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-save">💾 Enregistrer</button>
            </div>
        </form>
    </div>
</div>
 
<!-- MODALE SUPPRIMER -->
<div class="modal-ov" id="deleteModal">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-top">
            <h5>Supprimer la candidature</h5>
            <button class="btn-x" onclick="closeDeleteModal()">✕</button>
        </div>
        <div class="modal-body" style="text-align:center;padding:32px 24px">
            <div class="del-ic">🗑️</div>
            <h6 style="font-size:17px;font-weight:800;margin-bottom:8px">Êtes-vous sûr ?</h6>
            <p style="color:var(--muted);font-size:14px;margin-bottom:6px">Vous allez retirer votre candidature pour :</p>
            <p id="delete-titre" style="font-weight:700;color:var(--danger);font-size:15px;margin-bottom:6px"></p>
            <p style="color:var(--muted);font-size:12px">Cette action est irréversible.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;gap:12px">
            <button class="btn-cancel" onclick="closeDeleteModal()" style="padding:10px 28px">Annuler</button>
            <a id="delete-confirm" href="#" class="btn-del-c">🗑️ Oui, supprimer</a>
        </div>
    </div>
</div>
 
<script>
function openEditModal(btn) {
    document.getElementById('edit_id_user').value  = btn.dataset.id_user;
    document.getElementById('edit_id_offer').value = btn.dataset.id_offer;
    document.getElementById('edit_lettre').value   = btn.dataset.lettre;
    document.getElementById('edit_count').textContent = btn.dataset.lettre.length + ' / 2000 caractères';
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow = '';
}
function updateEditCount() {
    document.getElementById('edit_count').textContent =
        document.getElementById('edit_lettre').value.length + ' / 2000 caractères';
}
function validateEdit(e) {
    e.preventDefault();
    var lettre = document.getElementById('edit_lettre').value.trim();
    var err    = document.getElementById('err_lettre');
    if (lettre.length < 10) {
        err.textContent = 'Minimum 10 caractères requis';
        err.classList.add('show');
        return false;
    }
    err.classList.remove('show');
    document.getElementById('editForm').submit();
    return false;
}
function openDeleteModal(btn) {
    document.getElementById('delete-titre').textContent = btn.dataset.titre;
    document.getElementById('delete-confirm').href =
        'supprimerCandidature.php?id_user=' + btn.dataset.id_user +
        '&id_offer=' + btn.dataset.id_offer;
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('editModal').addEventListener('click', function(e){ if(e.target===this) closeEditModal(); });
document.getElementById('deleteModal').addEventListener('click', function(e){ if(e.target===this) closeDeleteModal(); });
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeEditModal(); closeDeleteModal(); } });
</script>
<?php include __DIR__ . '/chatbot.php'; ?>
</body>
</html>
