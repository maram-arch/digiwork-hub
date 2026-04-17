<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

$controller   = new CandidatureController();
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
    <title>Gestion des Candidatures - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <style>
        .badge-accepte    { background:#D1E7DD; color:#0F5132; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-refuse     { background:#F8D7DA; color:#842029; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-en_attente { background:#FFF3CD; color:#856404; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .action-btns { display:flex; gap:6px; flex-wrap:wrap; }
    </style>
</head>
<body>
<div id="app">

    <!-- SIDEBAR (réutiliser celle de index.php) -->
    <aside id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" style="width:230px;">
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-title">Menu Principal</li>
                    <li class="sidebar-item">
                        <a href="index.php" class="sidebar-link">
                            <i data-feather="tag" width="20"></i><span>Gestion des offres</span>
                        </a>
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
                    <li class="sidebar-item">
                        <a href="listPacks.php" class="sidebar-link">
                            <i data-feather="package" width="20"></i><span>Gestion des packs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listEvents.php" class="sidebar-link">
                            <i data-feather="calendar" width="20"></i><span>Gestion des Events</span>
                        </a>
                    </li>
                    <li class="sidebar-item active">
                        <a href="listCandidatures.php" class="sidebar-link">
                            <i data-feather="file-text" width="20"></i><span>Gestion des candidatures</span>
                        </a>
                    </li>
                </ul>
            </div>
            <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
        </div>
    </aside>

    <main id="main">
        <nav class="navbar navbar-header navbar-expand navbar-light">
            <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
        </nav>

        <div class="main-content container-fluid">
            <header class="page-title">
                <h3>Gestion des candidatures</h3>
                <p class="text-subtitle text-muted">Liste de toutes les candidatures reçues</p>
            </header>

            <?php if ($message !== ""): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Toutes les candidatures</h4>
                </div>
                <div class="card-body px-0 pb-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Candidat</th>
                                    <th>Offre</th>
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
                                    <td><?= htmlspecialchars($c['nom_user'] ?? $c['id_user']) ?></td>
                                    <td><?= htmlspecialchars($c['titre_offre'] ?? $c['id_offer']) ?></td>
                                    <td>
                                        <?php if (!empty($c['cv'])): ?>
                                            <a href="../../uploads/<?= htmlspecialchars($c['cv']) ?>"
                                               target="_blank" style="font-size:13px;color:#435ebe">
                                                📄 Voir CV
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">–</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                        <?= htmlspecialchars($c['Lettre'] ?? '–') ?>
                                    </td>
                                    <td><?= htmlspecialchars($c['Date'] ?? '–') ?></td>
                                    <td>
                                        <?php $s = $c['Statut'] ?? 'en_attente'; ?>
                                        <span class="badge-<?= htmlspecialchars($s) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <form method="POST" action="updateStatut.php">
                                                <input type="hidden" name="id_user"  value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer" value="<?= (int)$c['id_offer'] ?>">
                                                <input type="hidden" name="statut"   value="accepte">
                                                <button type="submit" class="btn btn-success btn-sm">✔ Accepter</button>
                                            </form>
                                            <form method="POST" action="updateStatut.php">
                                                <input type="hidden" name="id_user"  value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer" value="<?= (int)$c['id_offer'] ?>">
                                                <input type="hidden" name="statut"   value="refuse">
                                                <button type="submit" class="btn btn-danger btn-sm">✘ Refuser</button>
                                            </form>
                                            <form method="POST" action="deleteCandidature.php"
                                                  onsubmit="return confirm('Supprimer cette candidature ?')">
                                                <input type="hidden" name="id_user"  value="<?= (int)$c['id_user'] ?>">
                                                <input type="hidden" name="id_offer" value="<?= (int)$c['id_offer'] ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm">🗑 Supprimer</button>
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