<?php
// backoffice/views/listCandidatures.php
// ★ Jointure : affiche titre et adresse de l'offre via INNER JOIN
 
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';
 
$controller   = new CandidatureController();
// ★ getAllCandidatures() utilise maintenant INNER JOIN offre
$candidatures = $controller->getAllCandidatures();
 
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
        /* Jointure info */
        .offre-cell .titre { font-weight:700; color:#1a202c; font-size:13px; }
        .offre-cell .type  { display:inline-block; background:#e6f1fb; color:#185fa5; border-radius:20px;
                             padding:2px 10px; font-size:11px; font-weight:600; margin-top:3px; }
        .offre-cell .addr  { font-size:11px; color:#6c757d; margin-top:2px; }
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
                                                        class="btn btn-success btn-sm">
                                                    ✔ Accepter
                                                </button>
                                            </form>
                                            <form method="POST" action="updateStatut.php">
                                                <input type="hidden" name="id_user"
                                                       value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer"
                                                       value="<?= (int)$c['id_offer'] ?>">
                                                <input type="hidden" name="statut" value="refuse">
                                                <button type="submit"
                                                        class="btn btn-danger btn-sm">
                                                    ✘ Refuser
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
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script>feather.replace();</script>
</body>
</html>
 