<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/OffreController.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';
 
// ── Liste via Controller ─────────────────────────────────────
$controller = new OffreController();
$offres     = $controller->listOffre()->fetchAll(PDO::FETCH_ASSOC);

$titreRecherche = trim($_GET['titre'] ?? '');
$typeRecherche  = trim($_GET['type'] ?? '');
$dateRecherche  = trim($_GET['date_limite'] ?? '');
$triOffre       = $_GET['tri'] ?? 'id_offer';
$ordreTri       = $_GET['ordre'] ?? 'desc';

$trisAutorises = [
    'id_offer' => 'Plus recentes',
    'titre' => 'Titre',
    'type' => 'Type',
    'date_limite' => 'Date limite',
];

if (!array_key_exists($triOffre, $trisAutorises)) {
    $triOffre = 'id_offer';
}

if (!in_array($ordreTri, ['asc', 'desc'], true)) {
    $ordreTri = 'desc';
}

$offres = array_values(array_filter($offres, function ($offre) use ($titreRecherche, $typeRecherche, $dateRecherche) {
    $matchTitre = ($titreRecherche === '' || stripos($offre['titre'] ?? '', $titreRecherche) !== false);
    $matchType  = ($typeRecherche === '' || ($offre['type'] ?? '') === $typeRecherche);
    $matchDate  = ($dateRecherche === '' || ($offre['date_limite'] ?? '') === $dateRecherche);
    return $matchTitre && $matchType && $matchDate;
}));

usort($offres, function ($a, $b) use ($triOffre, $ordreTri) {
    if ($triOffre === 'date_limite') {
        $result = (strtotime($a['date_limite'] ?? '') ?: 0) <=> (strtotime($b['date_limite'] ?? '') ?: 0);
    } elseif ($triOffre === 'id_offer') {
        $result = (int)($a['id_offer'] ?? 0) <=> (int)($b['id_offer'] ?? 0);
    } else {
        $result = strcmp(strtolower($a[$triOffre] ?? ''), strtolower($b[$triOffre] ?? ''));
    }

    return $ordreTri === 'desc' ? -$result : $result;
});

// ── Toutes les candidatures groupées par id_offer ────────────
$candController  = new CandidatureController();
$allCandidatures = $candController->getAllCandidatures();
$candByOffer = [];
foreach ($allCandidatures as $c) {
    $candByOffer[(int)$c['id_offer']][] = $c;
}
 
// ── Message retour ───────────────────────────────────────────
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
    <title>Liste des offres - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <style>
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:9999;justify-content:center;align-items:center}
        .modal-overlay.show{display:flex}
        .modal-box{background:#fff;border-radius:14px;width:100%;max-width:660px;max-height:92vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.25);animation:modalIn .22s ease}
        @keyframes modalIn{from{transform:translateY(-24px);opacity:0}to{transform:translateY(0);opacity:1}}
        .modal-top{display:flex;justify-content:space-between;align-items:center;padding:18px 24px 14px;border-bottom:1px solid #f0f1f5}
        .modal-top h5{margin:0;font-size:16px;font-weight:600;color:#2d3748;display:flex;align-items:center;gap:10px}
        .icon-edit-wrap{width:34px;height:34px;background:#fff3e0;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .btn-close-x{background:#f5f6fa;border:none;border-radius:8px;width:32px;height:32px;font-size:18px;cursor:pointer;color:#888;display:flex;align-items:center;justify-content:center;transition:background .15s}
        .btn-close-x:hover{background:#ffe5e5;color:#e74c3c}
        .modal-body-inner{padding:20px 24px}
        .modal-foot{padding:14px 24px;border-top:1px solid #f0f1f5;display:flex;justify-content:flex-end;gap:10px;background:#fafbfc;border-radius:0 0 14px 14px}
        .btn-edit-orange{background-color:#fd7e14;border:none;color:#fff;border-radius:6px;padding:5px 9px;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center}
        .btn-edit-orange:hover{background-color:#e86c00}
        .btn-save-blue{background-color:#435ebe;border:none;color:#fff;border-radius:8px;padding:9px 22px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s}
        .btn-save-blue:hover{background-color:#3348a8}
        .btn-cancel-modal{background:#f5f6fa;border:none;color:#666;border-radius:8px;padding:9px 20px;font-size:14px;cursor:pointer;transition:background .15s}
        .btn-cancel-modal:hover{background:#e8e9ef}
        .field-label{font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;display:block}
        .form-control{font-size:14px;border-radius:8px}
        .form-control:focus{border-color:#435ebe;box-shadow:0 0 0 3px rgba(67,94,190,.12)}
        .section-divider{font-size:11px;font-weight:700;color:#adb5bd;text-transform:uppercase;letter-spacing:.08em;margin:4px 0 14px;display:flex;align-items:center;gap:8px}
        .section-divider::after{content:'';flex:1;height:1px;background:#f0f1f5}
        /* Validation */
        .form-control.is-invalid{border-color:#dc3545!important;background-color:#fff8f8}
        .form-control.is-invalid:focus{border-color:#dc3545!important;box-shadow:0 0 0 3px rgba(220,53,69,.12)!important}
        .error-message{font-size:12px;color:#dc3545;margin-top:4px;display:none;font-weight:500}
        .error-message.show{display:block}
        .tools-grid{display:grid;grid-template-columns:1fr 1fr auto;gap:16px;margin-bottom:18px}
        .tool-card{background:#fff;border-radius:10px;border:1px solid #edf0f7;padding:16px;box-shadow:0 2px 10px rgba(67,94,190,.06)}
        .tool-title{font-size:13px;font-weight:700;color:#2d3748;margin-bottom:12px}
        .tool-row{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end}
        .tool-row.tri{grid-template-columns:1fr 1fr auto}
        .btn-stat{height:100%;min-height:74px;display:flex;align-items:center;justify-content:center;font-weight:700}
        @media(max-width:992px){.tools-grid{grid-template-columns:1fr}.tool-row,.tool-row.tri{grid-template-columns:1fr}}

        /* ── Bouton Voir Candidatures ── */
        .btn-view-cand{background:#e8f4fd;border:1.5px solid #b3d9f5;color:#185fa5;border-radius:6px;
                       padding:5px 10px;font-size:12px;font-weight:600;cursor:pointer;
                       display:inline-flex;align-items:center;gap:5px;transition:all .15s;white-space:nowrap}
        .btn-view-cand:hover{background:#c5e2f8;border-color:#7bbfe8;color:#0d3f73}

        /* ── Modale Candidatures ── */
        #candModal .modal-box{max-width:820px}
        .cand-header{background:linear-gradient(135deg,#435ebe 0%,#2a3f9e 100%);
                     padding:20px 24px;border-radius:14px 14px 0 0;color:#fff}
        .cand-header h5{margin:0;font-size:17px;font-weight:700;display:flex;align-items:center;gap:10px}
        .cand-header .offre-meta{font-size:12px;opacity:.8;margin-top:4px}
        .cand-header .btn-close-x{background:rgba(255,255,255,.18);color:#fff}
        .cand-header .btn-close-x:hover{background:rgba(255,255,255,.32);color:#fff}
        .cand-table th{background:#f7f8fc;font-size:11px;font-weight:700;text-transform:uppercase;
                       letter-spacing:.05em;color:#6c757d;border-bottom:2px solid #e9ecef;padding:10px 12px}
        .cand-table td{font-size:13px;padding:10px 12px;vertical-align:middle;border-bottom:1px solid #f0f1f5}
        .cand-table tr:last-child td{border-bottom:none}
        .badge-accepte   {background:#D1E7DD;color:#0F5132;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-refuse    {background:#F8D7DA;color:#842029;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-en_attente{background:#FFF3CD;color:#856404;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .cand-empty{text-align:center;padding:40px;color:#adb5bd}
        .cand-empty svg{opacity:.35;margin-bottom:10px}
        .cand-count-badge{background:rgba(255,255,255,.25);border-radius:20px;
                          padding:2px 10px;font-size:12px;font-weight:700;margin-left:8px}
        #cand-loading{text-align:center;padding:40px;color:#6c757d}
    </style>
</head>
<body>
<div id="app">
 
    <!-- SIDEBAR -->
    <div id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header"><img src="assets/images/logo.png" style="width:230px;"></div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-title">Menu Principal</li>
                    <li class="sidebar-item">
                        <a href="index.php" class="sidebar-link"><i data-feather="home" width="20"></i><span>Dashboard</span></a>
                    </li>
                    <li class="sidebar-title">Gestion</li>
                    <li class="sidebar-item active has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="tag" width="20"></i><span>Gestion des offres</span></a>
                        <ul class="submenu open">
                            <li><a href="listOffres.php" class="active">Toutes les offres</a></li>
                            <li><a href="addOffre.php">Ajouter une offre</a></li>
                            <li><a href="offresExpirees.php">Offres expirées</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="users" width="20"></i><span>Gestion des utilisateurs</span></a>
                        <ul class="submenu">
                            <li><a href="listUsers.php">Tous les utilisateurs</a></li>
                            <li><a href="addUser.php">Ajouter un utilisateur</a></li>
                            <li><a href="rolesPermissions.php">Rôles &amp; permissions</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="message-square" width="20"></i><span>Gestion des forums</span></a>
                        <ul class="submenu">
                            <li><a href="listForums.php">Tous les forums</a></li>
                            <li><a href="categoriesForums.php">Catégories</a></li>
                            <li><a href="signalements.php">Signalements</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="folder" width="20"></i><span>Gestion des projets</span></a>
                        <ul class="submenu">
                            <li><a href="listProjets.php">Tous les projets</a></li>
                            <li><a href="addProjet.php">Ajouter un projet</a></li>
                            <li><a href="projetsArchives.php">Archivés</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
        </div>
    </div>
 
    <!-- MAIN -->
    <div id="main">
        <nav class="navbar navbar-header navbar-expand navbar-light">
            <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="avatar mr-1"><img src="assets/images/avatar/avatar-s-1.png" alt=""></div>
                            <div class="d-none d-md-block d-lg-inline-block">Admin</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#"><i data-feather="user"></i> Compte</a>
                            <a class="dropdown-item" href="#"><i data-feather="settings"></i> Paramètres</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#"><i data-feather="log-out"></i> Déconnexion</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
 
        <div class="main-content container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Liste des offres</h3>
                        <p class="text-subtitle text-muted">Gérer toutes les offres disponibles</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-right float-lg-right">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Liste des offres</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
 
            <section class="section">
 
                <?php if ($message !== ""): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>

                <div class="tools-grid">
                    <form class="tool-card" method="GET" action="rechercheOffre.php">
                        <div class="tool-title">Recherche</div>
                        <div class="tool-row">
                            <div>
                                <label class="field-label" for="titre">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($titreRecherche) ?>">
                            </div>
                            <div>
                                <label class="field-label" for="type">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="" <?= $typeRecherche === '' ? 'selected' : '' ?>>Tous</option>
                                    <?php foreach (['CDI','CDD','Stage','Freelance','Alternance'] as $typeOption): ?>
                                    <option value="<?= $typeOption ?>" <?= $typeRecherche === $typeOption ? 'selected' : '' ?>><?= $typeOption ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="field-label" for="date_limite">Date limite</label>
                                <input type="date" class="form-control" id="date_limite" name="date_limite" value="<?= htmlspecialchars($dateRecherche) ?>">
                            </div>
                            <input type="hidden" name="tri" value="<?= htmlspecialchars($triOffre) ?>">
                            <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordreTri) ?>">
                            <button class="btn btn-primary" type="submit">Recherche</button>
                        </div>
                    </form>

                    <form class="tool-card" method="GET" action="triOffre.php">
                        <div class="tool-title">Tri</div>
                        <div class="tool-row tri">
                            <div>
                                <label class="field-label" for="tri">Trier par</label>
                                <select class="form-control" id="tri" name="tri">
                                    <?php foreach ($trisAutorises as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= $triOffre === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
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
                            <input type="hidden" name="titre" value="<?= htmlspecialchars($titreRecherche) ?>">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($typeRecherche) ?>">
                            <input type="hidden" name="date_limite" value="<?= htmlspecialchars($dateRecherche) ?>">
                            <button class="btn btn-primary" type="submit">Tri</button>
                        </div>
                    </form>

                    <a href="statistiqueOffre.php" class="btn btn-success btn-stat">Statistique</a>
                </div>
 
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Toutes les offres</h4>
                        <a href="addOffre.php" class="btn btn-primary">
                            <i data-feather="plus" width="16"></i> Ajouter une offre
                        </a>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Titre</th><th>Description</th>
                                        <th>Compétences</th><th>Date limite</th>
                                        <th>Adresse</th><th>Type</th><th>Entreprise</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($offres) > 0): ?>
                                    <?php foreach ($offres as $o): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($o['id_offer']) ?></td>
                                        <td><strong><?= htmlspecialchars($o['titre']) ?></strong></td>
                                        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                            <?= htmlspecialchars($o['description']) ?>
                                        </td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($o['competences']) ?></span></td>
                                        <td><?= htmlspecialchars($o['date_limite']) ?></td>
                                        <td><?= htmlspecialchars($o['adresse']) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($o['type']) ?></span></td>
                                        <td><?= htmlspecialchars($o['id_entreprise']) ?></td>
                                        <td style="white-space:nowrap">
                                            <!-- BOUTON MODIFIER -->
                                            <button class="btn-edit-orange"
                                                onclick='editOffer(
                                                    <?= $o["id_offer"] ?>,
                                                    <?= json_encode($o["titre"]) ?>,
                                                    <?= json_encode($o["description"]) ?>,
                                                    <?= json_encode($o["competences"]) ?>,
                                                    <?= json_encode($o["date_limite"]) ?>,
                                                    <?= json_encode($o["adresse"]) ?>,
                                                    <?= json_encode($o["type"]) ?>,
                                                    <?= json_encode($o["id_entreprise"]) ?>
                                                )'>
                                                <i data-feather="edit-2" width="14"></i>
                                            </button>
                                            <!-- BOUTON SUPPRIMER -->
                                            <button class="btn btn-sm btn-danger ml-1"
                                                onclick='deleteOffer(<?= $o["id_offer"] ?>, <?= json_encode($o["titre"]) ?>)'>
                                                <i data-feather="trash-2" width="14"></i>
                                            </button>
                                            <!-- ★ BOUTON VOIR CANDIDATURES -->
                                            <button class="btn-view-cand ml-1"
                                                onclick='openCandModal(
                                                    <?= (int)$o["id_offer"] ?>,
                                                    <?= json_encode($o["titre"]) ?>,
                                                    <?= json_encode($o["type"]) ?>
                                                )'>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                    <circle cx="9" cy="7" r="4"/>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                                </svg>
                                                Voir
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i data-feather="inbox" width="32"></i>
                                            <p class="mt-2">Aucune offre disponible.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
 
        <footer>
            <div class="footer clearfix mb-0 text-muted">
                <div class="float-left"><p>2024 &copy; DigiWork Hub</p></div>
                <div class="float-right"><p>Panneau d'administration</p></div>
            </div>
        </footer>
    </div>
</div>
 
<!-- ════════════════════════════════
     MODALE : CANDIDATURES (jointure)
════════════════════════════════ -->
<div class="modal-overlay" id="candModal">
    <div class="modal-box" style="max-width:820px">
        <div class="cand-header">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <div>
                    <h5>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Candidatures
                        <span class="cand-count-badge" id="cand-count">0</span>
                    </h5>
                    <div class="offre-meta" id="cand-offre-meta">—</div>
                </div>
                <button class="btn-close-x" onclick="closeCandModal()">&#x2715;</button>
            </div>
        </div>
        <div style="padding:0 0 4px">
            <div id="cand-loading" style="display:none;text-align:center;padding:40px;color:#6c757d">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                     fill="none" stroke="#435ebe" stroke-width="2" stroke-linecap="round"
                     style="animation:spin 1s linear infinite">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
                <p style="margin-top:10px;font-size:13px">Chargement…</p>
            </div>
            <div id="cand-table-wrap" style="overflow-x:auto;max-height:420px;overflow-y:auto"></div>
        </div>
        <div class="modal-foot">
            <button class="btn-cancel-modal" onclick="closeCandModal()">Fermer</button>
        </div>
    </div>
</div>
<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>

<!-- ════════════════════════════════
     MODALE : MODIFIER
════════════════════════════════ -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>
                <div class="icon-edit-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fd7e14" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                Modifier l'offre
            </h5>
            <button class="btn-close-x" onclick="closeEditModal()">&#x2715;</button>
        </div>
        <form method="POST" action="updateOffre.php" id="editForm" onsubmit="return validateEditForm(event)">
            <input type="hidden" name="id_offer" id="modal_id">
            <div class="modal-body-inner">
                <div class="section-divider">Informations principales</div>
                <div class="form-group">
                    <label class="field-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="titre" id="modal_titre" required>
                    <div class="error-message" data-field="titre"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" id="modal_description" rows="3" required></textarea>
                    <div class="error-message" data-field="description"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Compétences <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="competences" id="modal_competences" required>
                    <div class="error-message" data-field="competences"></div>
                </div>
                <div class="section-divider">Détails du contrat</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Date limite <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date_limite" id="modal_date_limite">
                            <div class="error-message" data-field="date_limite"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Type de contrat <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" id="modal_type" required>
                                <option value="">-- Choisir --</option>
                                <option value="CDI">CDI</option>
                                <option value="CDD">CDD</option>
                                <option value="Stage">Stage</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Alternance">Alternance</option>
                            </select>
                            <div class="error-message" data-field="type"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="field-label">Adresse <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="adresse" id="modal_adresse" required>
                            <div class="error-message" data-field="adresse"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="field-label">ID Entreprise <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="id_entreprise" id="modal_id_entreprise"
                                   placeholder="12345678" maxlength="8" inputmode="numeric" autocomplete="off" required>
                            <div class="error-message" data-field="id_entreprise"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-save-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
 
<!-- ════════════════════════════════
     MODALE : SUPPRIMER
════════════════════════════════ -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-top">
            <h5>
                <div class="icon-edit-wrap" style="background:#ffe5e5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </div>
                Confirmer la suppression
            </h5>
            <button class="btn-close-x" onclick="closeDeleteModal()">&#x2715;</button>
        </div>
        <div class="modal-body-inner" style="text-align:center;padding:28px 24px">
            <div style="width:64px;height:64px;background:#ffe5e5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <h6 style="font-size:16px;font-weight:600;color:#2d3748;margin-bottom:8px">Êtes-vous sûr ?</h6>
            <p style="color:#6c757d;font-size:14px;margin-bottom:4px">Vous allez supprimer l'offre :</p>
            <p id="delete-titre" style="font-weight:600;color:#dc3545;font-size:14px;margin-bottom:0"></p>
            <p style="color:#adb5bd;font-size:12px;margin-top:8px">Cette action est irréversible.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;gap:12px">
            <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()" style="padding:10px 28px">Annuler</button>
            <a id="delete-confirm-btn" href="#"
               style="background:#dc3545;color:#fff;border-radius:8px;padding:10px 28px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:6px;text-decoration:none;transition:background .15s"
               onmouseover="this.style.background='#b02a37'" onmouseout="this.style.background='#dc3545'">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
                Oui, supprimer
            </a>
        </div>
    </div>
</div>
 
<script src="assets/js/feather-icons/feather.min.js"></script>
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/main.js"></script>
<script>
feather.replace();

/* ══════════════════════════════════════════════
   MODALE CANDIDATURES
══════════════════════════════════════════════ */
// ★ Candidatures embarquées depuis PHP (pas de fichier AJAX séparé)
var allCandidaturesData = <?= json_encode($candByOffer, JSON_UNESCAPED_UNICODE) ?>;

function openCandModal(idOffer, titre, type) {
    document.getElementById('cand-offre-meta').textContent =
        'Offre · ' + titre + (type ? ' · ' + type : '');
    document.getElementById('cand-count').textContent = '…';
    document.getElementById('cand-table-wrap').innerHTML = '';
    document.getElementById('cand-loading').style.display = 'none';
    document.getElementById('candModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    var data = allCandidaturesData[idOffer] || [];
    renderCandTable(data);
}

function renderCandTable(candidatures) {
    document.getElementById('cand-count').textContent = candidatures.length;
    if (candidatures.length === 0) {
        document.getElementById('cand-table-wrap').innerHTML =
            '<div style="text-align:center;padding:40px;color:#adb5bd">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"' +
            ' fill="none" stroke="#ced4da" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5' +
            'a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>' +
            '<p style="margin-top:10px;font-size:14px">Aucune candidature pour cette offre.</p></div>';
        return;
    }
    var rows = candidatures.map(function(c) {
        var statut = c.Statut || 'en_attente';
        var label  = statut.charAt(0).toUpperCase() + statut.slice(1).replace('_',' ');
        var cv = c.cv
            ? '<a href="../../uploads/' + escHtml(c.cv) + '" target="_blank" style="color:#435ebe;font-size:12px">📄 Voir CV</a>'
            : '<span style="color:#ccc">–</span>';
        var lettre = c.Lettre
            ? '<span title="' + escHtml(c.Lettre) + '" style="font-size:12px">' +
              escHtml(c.Lettre.substring(0,40)) + (c.Lettre.length>40?'…':'') + '</span>'
            : '<span style="color:#ccc">–</span>';
        return '<tr>' +
            '<td><strong style="color:#2d3748">' + escHtml(String(c.id_user)) + '</strong></td>' +
            '<td>' + cv + '</td>' +
            '<td style="max-width:170px">' + lettre + '</td>' +
            '<td style="font-size:12px;color:#6c757d">' + escHtml(c.Date||'–') + '</td>' +
            '<td><span class="badge-' + escHtml(statut) + '">' + escHtml(label) + '</span></td>' +
            '<td style="white-space:nowrap">' +
              '<form method="POST" action="updateStatut.php" style="display:inline">' +
                '<input type="hidden" name="id_user" value="' + parseInt(c.id_user) + '">' +
                '<input type="hidden" name="id_offer" value="' + parseInt(c.id_offer) + '">' +
                '<input type="hidden" name="statut" value="accepte">' +
                '<button type="submit" class="btn btn-success btn-sm" style="font-size:11px;padding:3px 8px">✔</button>' +
              '</form> ' +
              '<form method="POST" action="updateStatut.php" style="display:inline">' +
                '<input type="hidden" name="id_user" value="' + parseInt(c.id_user) + '">' +
                '<input type="hidden" name="id_offer" value="' + parseInt(c.id_offer) + '">' +
                '<input type="hidden" name="statut" value="refuse">' +
                '<button type="submit" class="btn btn-danger btn-sm" style="font-size:11px;padding:3px 8px">✘</button>' +
              '</form>' +
            '</td></tr>';
    }).join('');
    document.getElementById('cand-table-wrap').innerHTML =
        '<table class="table cand-table mb-0"><thead><tr>' +
        '<th>Candidat (ID)</th><th>CV</th><th>Lettre</th><th>Date</th><th>Statut</th><th>Actions</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table>';
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function closeCandModal() {
    document.getElementById('candModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.getElementById('candModal').addEventListener('click', function(e) {
    if (e.target === this) closeCandModal();
});

/* ══════════════════════════════════════════════
   VALIDATION HELPERS
══════════════════════════════════════════════ */
function clearFieldErrors(form) {
    form.querySelectorAll('.form-control').forEach(function(input) {
        input.classList.remove('is-invalid');
    });
    form.querySelectorAll('.error-message').forEach(function(msg) {
        msg.classList.remove('show');
        msg.textContent = '';
    });
}
 
function setFieldError(form, fieldName, message) {
    var input = form.querySelector('[name="' + fieldName + '"]');
    if (input) {
        input.classList.add('is-invalid');
        var group = input.closest('.form-group');
        var errorMsg = group ? group.querySelector('.error-message') : null;
        if (errorMsg) {
            errorMsg.textContent = message;
            errorMsg.classList.add('show');
        }
    }
}
 
function isValidDate(dateString) {
    var regex = /^\d{4}-\d{2}-\d{2}$/;
    if (!regex.test(dateString)) return false;
    var date = new Date(dateString + 'T00:00:00');
    return date instanceof Date && !isNaN(date);
}
 
function isFutureOrTodayDate(dateString) {
    if (!isValidDate(dateString)) return false;
    var date = new Date(dateString + 'T00:00:00');
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    return date >= today;
}
 
function isValidEntrepriseId(value) {
    return /^\d{1,8}$/.test(value);
}
 
/* ══════════════════════════════════════════════
   VALIDATION FORMULAIRE MODIFIER
══════════════════════════════════════════════ */
function validateEditForm(event) {
    event.preventDefault();
    var form = event.target;
    clearFieldErrors(form);
 
    var titre        = form.querySelector('[name="titre"]').value.trim();
    var description  = form.querySelector('[name="description"]').value.trim();
    var competences  = form.querySelector('[name="competences"]').value.trim();
    var date_limite  = form.querySelector('[name="date_limite"]').value.trim();
    var adresse      = form.querySelector('[name="adresse"]').value.trim();
    var type         = form.querySelector('[name="type"]').value.trim();
    var id_entreprise = form.querySelector('[name="id_entreprise"]').value.trim();
 
    var isValid = true;
 
    if (!titre) {
        setFieldError(form, 'titre', 'Le titre est obligatoire');
        isValid = false;
    }
    if (!description) {
        setFieldError(form, 'description', 'La description est obligatoire');
        isValid = false;
    }
    if (!competences) {
        setFieldError(form, 'competences', 'Les compétences sont obligatoires');
        isValid = false;
    }
    if (!date_limite) {
        setFieldError(form, 'date_limite', 'La date limite est obligatoire');
        isValid = false;
    } else if (!isValidDate(date_limite)) {
        setFieldError(form, 'date_limite', 'Le format doit être YYYY-MM-DD');
        isValid = false;
    } else if (!isFutureOrTodayDate(date_limite)) {
        setFieldError(form, 'date_limite', "La date doit être aujourd'hui ou dans le futur");
        isValid = false;
    }
    if (!adresse) {
        setFieldError(form, 'adresse', "L'adresse est obligatoire");
        isValid = false;
    }
    if (!type) {
        setFieldError(form, 'type', 'Le type de contrat est obligatoire');
        isValid = false;
    }
    if (!id_entreprise) {
        setFieldError(form, 'id_entreprise', "L'ID entreprise est obligatoire");
        isValid = false;
    } else if (!isValidEntrepriseId(id_entreprise)) {
        setFieldError(form, 'id_entreprise', "L'ID entreprise doit contenir uniquement des chiffres (max 8 caractères)");
        isValid = false;
    }
 
    if (isValid) {
        form.submit();
    }
    return false;
}
 
/* ══════════════════════════════════════════════
   MODALE MODIFIER
══════════════════════════════════════════════ */
function editOffer(id, titre, description, competences, date, adresse, type, id_entreprise) {
    document.getElementById('modal_id').value            = id;
    document.getElementById('modal_titre').value         = titre;
    document.getElementById('modal_description').value   = description;
    document.getElementById('modal_competences').value   = competences;
    document.getElementById('modal_date_limite').value   = date;
    document.getElementById('modal_adresse').value       = adresse;
    document.getElementById('modal_id_entreprise').value = id_entreprise;
 
    var sel = document.getElementById('modal_type');
    for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === type) {
            sel.selectedIndex = i;
            break;
        }
    }
 
    clearFieldErrors(document.getElementById('editForm'));
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
 
function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow = '';
}
 
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
 
/* ══════════════════════════════════════════════
   MODALE SUPPRIMER
══════════════════════════════════════════════ */
function deleteOffer(id, titre) {
    document.getElementById('delete-titre').textContent = titre;
    document.getElementById('delete-confirm-btn').href  = 'deleteOffre.php?id=' + id;
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
 
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow = '';
}
 
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
 
/* ══════════════════════════════════════════════
   NUMERIC ID ENTREPRISE
══════════════════════════════════════════════ */
document.querySelectorAll('input[name="id_entreprise"]').forEach(function(input) {
    input.addEventListener('input', function() {
        var sanitized = this.value.replace(/\D/g, '').slice(0, 8);
        if (this.value !== sanitized) this.value = sanitized;
    });
});
 
/* ══════════════════════════════════════════════
   TOUCHE ECHAP
══════════════════════════════════════════════ */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
        closeDeleteModal();
    }
});
</script>
</body>
</html>
