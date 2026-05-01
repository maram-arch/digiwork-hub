<?php
// backoffice/views/listCandidatures.php
// ★ Jointure : affiche titre et adresse de l'offre via INNER JOIN
 
require_once __DIR__. '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';
 
$controller   = new CandidatureController();
// ★ getAllCandidatures() utilise maintenant INNER JOIN offre
$candidatures = $controller->getAllCandidatures();
$allCandidatures = $candidatures;

$rechercheCandidat = trim($_GET['id_user'] ?? '');
$rechercheOffre    = trim($_GET['offre'] ?? '');
$rechercheStatut   = trim($_GET['statut'] ?? '');
$rechercheDate     = trim($_GET['date'] ?? '');
$triCandidature    = $_GET['tri'] ?? 'Date';
$ordreTri          = $_GET['ordre'] ?? 'desc';

$trisAutorises = [
    'Date' => 'Date',
    'id_user' => 'Candidat',
    'titre_offre' => 'Offre',
    'Statut' => 'Statut',
];

if (!array_key_exists($triCandidature, $trisAutorises)) {
    $triCandidature = 'Date';
}

if (!in_array($ordreTri, ['asc', 'desc'], true)) {
    $ordreTri = 'desc';
}

$statsStatut = [
    'en_attente' => 0,
    'accepte' => 0,
    'refuse' => 0,
];

foreach ($allCandidatures as $candStat) {
    $statut = $candStat['Statut'] ?? 'en_attente';
    $statsStatut[$statut] = ($statsStatut[$statut] ?? 0) + 1;
}

$totalCandidatures = count($allCandidatures);

$candidatures = array_values(array_filter($candidatures, function ($c) use ($rechercheCandidat, $rechercheOffre, $rechercheStatut, $rechercheDate) {
    $matchCandidat = ($rechercheCandidat === '' || (string)($c['id_user'] ?? '') === $rechercheCandidat);
    $matchOffre = ($rechercheOffre === '' || stripos($c['titre_offre'] ?? '', $rechercheOffre) !== false);
    $matchStatut = ($rechercheStatut === '' || ($c['Statut'] ?? 'en_attente') === $rechercheStatut);
    $matchDate = ($rechercheDate === '' || substr($c['Date'] ?? '', 0, 10) === $rechercheDate);
    return $matchCandidat && $matchOffre && $matchStatut && $matchDate;
}));

usort($candidatures, function ($a, $b) use ($triCandidature, $ordreTri) {
    if ($triCandidature === 'Date') {
        $result = (strtotime($a['Date'] ?? '') ?: 0) <=> (strtotime($b['Date'] ?? '') ?: 0);
    } elseif ($triCandidature === 'id_user') {
        $result = (int)($a['id_user'] ?? 0) <=> (int)($b['id_user'] ?? 0);
    } else {
        $result = strcmp(strtolower($a[$triCandidature] ?? ''), strtolower($b[$triCandidature] ?? ''));
    }

    return $ordreTri === 'desc' ? -$result : $result;
});
 
$message = $messageType = "";
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
    <title>Gestion des Candidatures - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <style>
        .badge-accepte    { background:#D1E7DD; color:#0F5132; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-refuse     { background:#F8D7DA; color:#842029; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-en_attente { background:#FFF3CD; color:#856404; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .action-btns      { display:flex; gap:6px; flex-wrap:wrap; }
        .btn-circle-action{width:34px;height:34px;border-radius:50%;padding:0;display:inline-flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;line-height:1}
        /* Jointure info */
        .offre-cell .titre { font-weight:700; color:#1a202c; font-size:13px; }
        .offre-cell .type  { display:inline-block; background:#e6f1fb; color:#185fa5; border-radius:20px;
                             padding:2px 10px; font-size:11px; font-weight:600; margin-top:3px; }
        .offre-cell .addr  { font-size:11px; color:#6c757d; margin-top:2px; }
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px}
        .stat-card{background:#fff;border-radius:10px;border:1px solid #edf0f7;padding:18px;box-shadow:0 2px 10px rgba(67,94,190,.06)}
        .stat-label{font-size:12px;color:#6c757d;text-transform:uppercase;font-weight:700;letter-spacing:.04em}
        .stat-value{font-size:30px;font-weight:800;color:#435ebe;margin-top:6px;line-height:1}
        .chart-card{background:#fff;border-radius:10px;border:1px solid #edf0f7;padding:18px;margin-bottom:18px;box-shadow:0 2px 10px rgba(67,94,190,.06)}
        .chart-wrap{height:260px;position:relative}
        .tools-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}
        .tool-card{background:#fff;border-radius:10px;border:1px solid #edf0f7;padding:16px;box-shadow:0 2px 10px rgba(67,94,190,.06)}
        .tool-title{font-size:13px;font-weight:700;color:#2d3748;margin-bottom:12px}
        .tool-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end}
        .tool-row.tri{grid-template-columns:1fr 1fr auto}
        .field-label{font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;display:block}
        @media(max-width:992px){.stats-grid,.tools-grid{grid-template-columns:1fr}.tool-row,.tool-row.tri{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div id="app">
 
    <!-- SIDEBAR -->
    <aside id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" style="width:230px;">
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-title">Menu Principal</li>
                    <li class="sidebar-item has-sub active">
                        <a href="index.php" class="sidebar-link">
                            <i data-feather="tag" width="20"></i>
                            <span>Gestion des offres</span>
                        </a>
                        <ul class="submenu open">
                            <li><a href="index.php">Toutes les offres</a></li>
                            <li><a href="listCandidatures.php" class="active">Gestion des candidatures</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-title">Gestion</li>
                    <li class="sidebar-item">
                        <a href="listUsers.php" class="sidebar-link">
                            <i data-feather="users" width="20"></i><span>Gestion des utilisateurs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listForums.php" class="sidebar-link">
                            <i data-feather="message-square" width="20"></i><span>Gestion des forums</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listProjets.php" class="sidebar-link">
                            <i data-feather="folder" width="20"></i><span>Gestion des projets</span>
                        </a>
                    </li>
                </ul>
            </div>
            <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
        </div>
    </aside>
 
    <!-- MAIN -->
    <main id="main">
        <nav class="navbar navbar-header navbar-expand navbar-light">
            <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown"
                           class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="avatar mr-1">
                                <img src="assets/images/avatar/avatar-s-1.png" alt="">
                            </div>
                            <div class="d-none d-md-block d-lg-inline-block">Admin</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#"><i data-feather="log-out"></i> Déconnexion</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
 
        <div class="main-content container-fluid">
            <header class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Gestion des candidatures</h3>
                        <p class="text-subtitle text-muted">
                            Liste de toutes les candidatures (jointure avec les offres)
                        </p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb"
                             class="breadcrumb-header float-right float-lg-right">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="index.php">Offres</a></li>
                                <li class="breadcrumb-item active">Candidatures</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </header>
 
            <?php if ($message !== ""): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <?php include __DIR__ . '/statistique.php'; ?>

            <div class="tools-grid">
                <form class="tool-card" method="GET" action="recherchecondidature.php">
                    <div class="tool-title">Recherche</div>
                    <div class="tool-row">
                        <div>
                            <label class="field-label" for="id_user">Candidat</label>
                            <input type="number" class="form-control" id="id_user" name="id_user" value="<?= htmlspecialchars($rechercheCandidat) ?>">
                        </div>
                        <div>
                            <label class="field-label" for="offre">Offre</label>
                            <input type="text" class="form-control" id="offre" name="offre" value="<?= htmlspecialchars($rechercheOffre) ?>">
                        </div>
                        <div>
                            <label class="field-label" for="statut">Statut</label>
                            <select class="form-control" id="statut" name="statut">
                                <option value="" <?= $rechercheStatut === '' ? 'selected' : '' ?>>Tous</option>
                                <option value="en_attente" <?= $rechercheStatut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="accepte" <?= $rechercheStatut === 'accepte' ? 'selected' : '' ?>>Accepte</option>
                                <option value="refuse" <?= $rechercheStatut === 'refuse' ? 'selected' : '' ?>>Refuse</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="date">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($rechercheDate) ?>">
                        </div>
                        <input type="hidden" name="tri" value="<?= htmlspecialchars($triCandidature) ?>">
                        <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordreTri) ?>">
                        <button class="btn btn-primary" type="submit">Recherche</button>
                    </div>
                </form>

                <form class="tool-card" method="GET" action="triecondidature.php">
                    <div class="tool-title">Tri</div>
                    <div class="tool-row tri">
                        <div>
                            <label class="field-label" for="tri">Trier par</label>
                            <select class="form-control" id="tri" name="tri">
                                <?php foreach ($trisAutorises as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $triCandidature === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="ordre">Ordre</label>
                            <select class="form-control" id="ordre" name="ordre">
                                <option value="asc" <?= $ordreTri === 'asc' ? 'selected' : '' ?>>Croissant</option>
                                <option value="desc" <?= $ordreTri === 'desc' ? 'selected' : '' ?>>Decroissant</option>
                            </select>
                        </div>
                        <input type="hidden" name="id_user" value="<?= htmlspecialchars($rechercheCandidat) ?>">
                        <input type="hidden" name="offre" value="<?= htmlspecialchars($rechercheOffre) ?>">
                        <input type="hidden" name="statut" value="<?= htmlspecialchars($rechercheStatut) ?>">
                        <input type="hidden" name="date" value="<?= htmlspecialchars($rechercheDate) ?>">
                        <button class="btn btn-primary" type="submit">Tri</button>
                    </div>
                </form>
            </div>
 
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        Toutes les candidatures
                        <small class="text-muted" style="font-size:13px;font-weight:400">
                            — <?= count($candidatures) ?> résultat(s)
                        </small>
                    </h4>
                </div>
                <div class="card-body px-0 pb-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Candidat (ID)</th>
                                    <!-- ★ Colonne enrichie par la jointure -->
                                    <th>Offre (titre · type · adresse)</th>
                                    <th>CV</th>
                                    <th>Lettre</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($candidatures)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i data-feather="inbox" width="32"></i>
                                        <p class="mt-2">Aucune candidature pour le moment.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($candidatures as $c): ?>
                                <tr>
                                    <!-- Candidat -->
                                    <td><?= htmlspecialchars($c['id_user']) ?></td>
 
                                    <!-- ★ Infos offre issues de la jointure -->
                                    <td class="offre-cell">
                                        <div class="titre">
                                            <?= htmlspecialchars($c['titre_offre'] ?? '—') ?>
                                        </div>
                                        <span class="type">
                                            <?= htmlspecialchars($c['type_offre'] ?? '') ?>
                                        </span>
                                        <div class="addr">
                                            📍 <?= htmlspecialchars($c['adresse_offre'] ?? '—') ?>
                                        </div>
                                    </td>
 
                                    <!-- CV -->
                                    <td>
                                        <?php if (!empty($c['cv'])): ?>
                                            <a href="../../uploads/<?= htmlspecialchars($c['cv']) ?>"
                                               target="_blank"
                                               style="font-size:13px;color:#435ebe">
                                                📄 Voir CV
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">–</span>
                                        <?php endif; ?>
                                    </td>
 
                                    <!-- Lettre -->
                                    <td style="max-width:160px;overflow:hidden;
                                               text-overflow:ellipsis;white-space:nowrap">
                                        <?= htmlspecialchars($c['Lettre'] ?? '–') ?>
                                    </td>
 
                                    <!-- Date -->
                                    <td><?= htmlspecialchars($c['Date'] ?? '–') ?></td>
 
                                    <!-- Statut -->
                                    <td>
                                        <?php $s = $c['Statut'] ?? 'en_attente'; ?>
                                        <span class="badge-<?= htmlspecialchars($s) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                        </span>
                                    </td>
 
                                    <!-- Actions -->
                                    <td>
                                        <div class="action-btns">
                                            <form method="POST" action="updateStatut.php">
                                                <input type="hidden" name="id_user"
                                                       value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer"
                                                       value="<?= (int)$c['id_offer'] ?>">
                                                <input type="hidden" name="statut" value="accepte">
                                                <button type="submit"
                                                        class="btn btn-success btn-circle-action"
                                                        title="Accepter">
                                                    ✔
                                                </button>
                                            </form>
                                            <form method="POST" action="updateStatut.php">
                                                <input type="hidden" name="id_user"
                                                       value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer"
                                                       value="<?= (int)$c['id_offer'] ?>">
                                                <input type="hidden" name="statut" value="refuse">
                                                <button type="submit"
                                                        class="btn btn-danger btn-circle-action"
                                                        title="Refuser">
                                                    ✘
                                                </button>
                                            </form>
                                            <form method="POST" action="deleteCandidature.php"
                                                  onsubmit="return confirm(
                                                      'Supprimer cette candidature ?')">
                                                <input type="hidden" name="id_user"
                                                       value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer"
                                                       value="<?= (int)$c['id_offer'] ?>">
                                                <button type="submit"
                                                        class="btn btn-secondary btn-sm">
                                                    🗑 Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
 
<script src="assets/js/feather-icons/feather.min.js"></script>
<script src="assets/vendors/chartjs/Chart.bundle.min.js"></script>
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script>feather.replace();</script>
</body>
</html>
 
