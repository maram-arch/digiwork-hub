<?php
/**
 * Dashboard module — intégré dans le layout backoffice original (index.php)
 * Ce fichier est inclus par view/back/dashboard.php (wrapper de compatibilité)
 * et directement par view/backoffice/index.php.
 *
 * Quand inclus via le wrapper, il doit afficher son propre layout complet.
 * Quand inclus depuis index.php, seul le contenu est nécessaire.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../controller/UserController.php';
require_once __DIR__ . '/../../../model/Abonnement.php';
require_once __DIR__ . '/../../../model/Pack.php';

// Auth guard — redirect to backoffice login if not admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /projectttttttt/view/backoffice/login.php');
    exit;
}

$loggedInUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $loggedInUser = (new UserController())->findUser((int) $_SESSION['user_id']);
    } catch (Throwable $e) {}
}

// ── Statistics ────────────────────────────────────────────────────────────────
$abonnementModel = new Abonnement();
$packModel        = new Pack();

$allAbonnements   = $abonnementModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
$totalAbonnements = count($allAbonnements);
$activeAbonnements = 0;
foreach ($allAbonnements as $abo) {
    if (($abo['status'] ?? '') === 'actif') {
        $activeAbonnements++;
    }
}
$totalPacks = count($packModel->getAll()->fetchAll());

$totalUsers   = 0;
$totalRevenue = 0;
$recentAbonnements = [];

try {
    $db = Config::getConnexion();

    $totalUsers = (int) $db->query("SELECT COUNT(*) FROM `user`")->fetchColumn();

    $rev = $db->query(
        "SELECT COALESCE(SUM(p.`prix`), 0) AS total
         FROM `abonnement` a
         JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
         JOIN `pack` p ON ap.`id-pack` = p.`id-pack`"
    )->fetch();
    $totalRevenue = $rev['total'] ?? 0;

    $stmt = $db->prepare(
        "SELECT a.`id-abonnement`, a.`date-deb`, a.`date-fin`, a.status,
                p.`nom-pack`, u.email
         FROM abonnement a
         LEFT JOIN `abon-pack` ap ON a.`id-abonnement` = ap.`id-abonnement`
         LEFT JOIN pack p ON ap.`id-pack` = p.`id-pack`
         LEFT JOIN `user` u ON a.`id-user` = u.id_user
         ORDER BY a.`date-deb` DESC LIMIT 5"
    );
    $stmt->execute();
    $recentAbonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

// ── Detect if called standalone (via wrapper) or embedded (via index.php) ────
$standalone = !defined('BACKOFFICE_LAYOUT_LOADED');
?>
<?php if ($standalone): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiWork Hub - Dashboard Administrateur</title>
    <link rel="stylesheet" href="/projectttttttt/view/backoffice/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/projectttttttt/view/backoffice/assets/vendors/chartjs/Chart.min.css">
    <link rel="stylesheet" href="/projectttttttt/view/backoffice/assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="/projectttttttt/view/backoffice/assets/css/app.css">
    <link rel="shortcut icon" href="/projectttttttt/view/backoffice/assets/images/favicon.svg" type="image/x-icon">
</head>
<body>
<div id="app">
    <?php
    $activePage = 'dashboard';
    require __DIR__ . '/../layouts/sidebar.php';
    ?>
    <div id="main">
        <nav class="navbar navbar-header navbar-expand navbar-light">
            <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="avatar mr-1">
                                <img src="/projectttttttt/view/backoffice/assets/images/avatar/avatar-s-1.png" alt="Admin">
                            </div>
                            <div class="d-none d-md-block d-lg-inline-block"><?= htmlspecialchars($loggedInUser['email'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="/projectttttttt/view/backoffice/index.php?logout=1"><i data-feather="log-out"></i> Déconnexion</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="main-content container-fluid">
<?php endif; ?>

<!-- ── Dashboard content (shared between standalone and embedded) ── -->
<div class="page-title">
    <h3>Tableau de bord administrateur</h3>
    <p class="text-subtitle text-muted">Bienvenue sur DigiWork Hub</p>
</div>

<!-- Stats cards -->
<div class="row mb-4">
    <div class="col-12 col-md-3">
        <div class="card card-statistic">
            <div class="card-body p-0">
                <div class="d-flex flex-column">
                    <div class="px-3 py-3 d-flex justify-content-between">
                        <h3 class="card-title">Utilisateurs</h3>
                        <div class="card-right d-flex align-items-center">
                            <p class="text-primary"><?= $totalUsers ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card card-statistic">
            <div class="card-body p-0">
                <div class="d-flex flex-column">
                    <div class="px-3 py-3 d-flex justify-content-between">
                        <h3 class="card-title">Abonnements</h3>
                        <div class="card-right d-flex align-items-center">
                            <p class="text-success"><?= $totalAbonnements ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card card-statistic">
            <div class="card-body p-0">
                <div class="d-flex flex-column">
                    <div class="px-3 py-3 d-flex justify-content-between">
                        <h3 class="card-title">Actifs</h3>
                        <div class="card-right d-flex align-items-center">
                            <p class="text-warning"><?= $activeAbonnements ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="card card-statistic">
            <div class="card-body p-0">
                <div class="d-flex flex-column">
                    <div class="px-3 py-3 d-flex justify-content-between">
                        <h3 class="card-title">Revenu Total</h3>
                        <div class="card-right d-flex align-items-center">
                            <p class="text-danger"><?= number_format((float)$totalRevenue, 2) ?> DT</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="card mb-4">
    <div class="card-header"><h4 class="card-title mb-0">Actions rapides</h4></div>
    <div class="card-body d-flex flex-wrap gap-2" style="gap:10px;">
        <a href="/projectttttttt/view/backoffice/users.php" class="btn btn-success">
            <i data-feather="users" width="16"></i> Gérer les utilisateurs
        </a>
        <a href="/projectttttttt/view/backoffice/modules/dashboard_packs.php" class="btn btn-primary">
            <i data-feather="package" width="16"></i> Gérer les packs
        </a>
        <a href="/projectttttttt/view/backoffice/modules/dashboard_abonnements.php" class="btn btn-info">
            <i data-feather="credit-card" width="16"></i> Abonnements
        </a>
        <a href="/projectttttttt/view/backoffice/modules/manageEvents.php" class="btn btn-warning">
            <i data-feather="calendar" width="16"></i> Événements
        </a>
        <a href="/projectttttttt/view/backoffice/modules/projectList.php" class="btn btn-secondary">
            <i data-feather="folder" width="16"></i> Projets
        </a>
        <a href="/projectttttttt/view/frontoffice/index.php" class="btn btn-outline-success" target="_blank">
            <i data-feather="globe" width="16"></i> Front Office
        </a>
    </div>
</div>

<!-- Recent activity -->
<div class="card mb-4">
    <div class="card-header"><h4 class="card-title mb-0">Activité récente — Abonnements</h4></div>
    <div class="card-body px-0 pb-0">
        <?php if (empty($recentAbonnements)): ?>
            <p class="text-muted text-center py-4">Aucune activité récente.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Pack</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAbonnements as $abo): ?>
                    <tr>
                        <td><?= htmlspecialchars($abo['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($abo['nom-pack'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($abo['date-deb'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($abo['date-fin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php $s = $abo['status'] ?? ''; ?>
                            <span class="badge bg-<?= $s === 'actif' ? 'success' : ($s === 'expiré' ? 'danger' : 'warning') ?>">
                                <?= htmlspecialchars(ucfirst($s), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<!-- ── End dashboard content ── -->

<?php if ($standalone): ?>
        </div><!-- .main-content -->
    </div><!-- #main -->
</div><!-- #app -->
<script src="/projectttttttt/view/backoffice/assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="/projectttttttt/view/backoffice/assets/js/app.js"></script>
<script src="/projectttttttt/view/backoffice/assets/js/main.js"></script>
<script src="/projectttttttt/view/backoffice/assets/js/feather-icons/feather.min.js"></script>
<script>if(typeof feather!=='undefined') feather.replace();</script>
</body>
</html>
<?php endif; ?>
