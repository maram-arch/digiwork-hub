<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/OffreController.php';
 
$db = Config::getConnexion();
 
$nbOffres = 0;
try { $nbOffres = $db->query("SELECT COUNT(*) FROM offre")->fetchColumn(); } catch(Exception $e){}
 
$controller = new OffreController();
$offres     = $controller->listOffre()->fetchAll(PDO::FETCH_ASSOC);
 
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
    <title>Gestion des offres - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/chartjs/Chart.min.css">
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
        .icon-wrap{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .btn-close-x{background:#f5f6fa;border:none;border-radius:8px;width:32px;height:32px;font-size:18px;cursor:pointer;color:#888;display:flex;align-items:center;justify-content:center;transition:background .15s}
        .btn-close-x:hover{background:#ffe5e5;color:#e74c3c}
        .modal-body-inner{padding:20px 24px}
        .modal-foot{padding:14px 24px;border-top:1px solid #f0f1f5;display:flex;justify-content:flex-end;gap:10px;background:#fafbfc;border-radius:0 0 14px 14px}
        .btn-save-blue{background-color:#435ebe;border:none;color:#fff;border-radius:8px;padding:9px 22px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s}
        .btn-save-blue:hover{background-color:#3348a8}
        .btn-cancel-modal{background:#f5f6fa;border:none;color:#666;border-radius:8px;padding:9px 20px;font-size:14px;cursor:pointer;transition:background .15s}
        .btn-cancel-modal:hover{background:#e8e9ef}
        .field-label{font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;display:block}
        .form-control{font-size:14px;border-radius:8px}
        .form-control:focus{border-color:#435ebe;box-shadow:0 0 0 3px rgba(67,94,190,.12)}
        .section-divider{font-size:11px;font-weight:700;color:#adb5bd;text-transform:uppercase;letter-spacing:.08em;margin:4px 0 14px;display:flex;align-items:center;gap:8px}
        .section-divider::after{content:'';flex:1;height:1px;background:#f0f1f5}
 
        /* Cartes CRUD */
        .crud-card{border-radius:14px;cursor:pointer;transition:transform .18s,box-shadow .18s;border:none;user-select:none}
        .crud-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.14)!important}
        .crud-card:active{transform:translateY(-2px)}
        .crud-icon-circle{width:66px;height:66px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
        .crud-card .card-body{padding:32px 24px 22px}
        .crud-action-label{display:inline-flex;align-items:center;gap:5px;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:600;margin-top:12px}
 
        /* Picker rows */
        .picker-row{display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:9px;border:1px solid #f0f1f5;margin-bottom:8px;cursor:pointer;transition:background .15s,border-color .15s}
    </style>
</head>
<body>
<div id="app">
 
    <!-- SIDEBAR -->
    <aside id="sidebar" class='active'>
        <div class="sidebar-wrapper active">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" style="width:230px;">
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class='sidebar-title'>Menu Principal</li>
                    <li class="sidebar-item active">
                        <a href="index.php" class='sidebar-link'>
                            <i data-feather="tag" width="20"></i>
                            <span>Gestion des offres</span>
                        </a>
                    </li>
                    <li class='sidebar-title'>Gestion</li>
                    <li class="sidebar-item">
                        <a href="listUsers.php" class='sidebar-link'>
                            <i data-feather="users" width="20"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listForums.php" class='sidebar-link'>
                            <i data-feather="message-square" width="20"></i>
                            <span>Gestion des forums</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listProjets.php" class='sidebar-link'>
                            <i data-feather="folder" width="20"></i>
                            <span>Gestion des projets</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listPacks.php" class='sidebar-link'>
                            <i data-feather="package" width="20"></i>
                            <span>Gestion des packs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listEvents.php" class='sidebar-link'>
                            <i data-feather="calendar" width="20"></i>
                            <span>Gestion des Events</span>
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
                    <li class="dropdown nav-icon">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg">
                            <div class="d-lg-inline-block"><i data-feather="bell"></i></div>
                        </a>
                    </li>
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
 
        <section class="main-content container-fluid">
            <header class="page-title">
                <h3>Gestion des offres</h3>
                <p class="text-subtitle text-muted">Gestion des offres d'emploi</p>
            </header>
 
            <section class="section">
 
<!-- ══ CARTE AJOUTER ══ -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card crud-card shadow-sm text-center" style="border-top:5px solid #28a745"
                             onclick="openAddModal()">
                            <div class="card-body">
                                <div class="crud-icon-circle" style="background:#e8f5e9">
                                    <i data-feather="plus-circle" style="color:#28a745;width:32px;height:32px"></i>
                                </div>
                                <h4 style="font-weight:800;color:#28a745;margin:0;font-size:1.3rem">Ajouter</h4>
                                <p style="font-size:13px;font-weight:600;color:#6c757d;margin:6px 0 4px;text-transform:uppercase;letter-spacing:.05em">Nouvelle offre</p>
                                <p style="font-size:12px;color:#adb5bd;margin:0">Créer une nouvelle offre d'emploi</p>
                                <span class="crud-action-label" style="background:#e8f5e9;color:#28a745">
                                    <i data-feather="plus" width="12"></i> Cliquez pour ajouter
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
 
                <!-- Alerte message -->
                <?php if ($message !== ""): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>
 
                <!-- ══ CARTES DES OFFRES ══ -->
                <div class="row">
                <?php if (count($offres) > 0): ?>
                    <?php foreach ($offres as $o): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($o['titre']) ?></h5>
                                <p class="card-text flex-grow-1" style="max-height: 60px; overflow: hidden;"><?= htmlspecialchars(substr($o['description'], 0, 100)) ?>...</p>
                                <p class="mb-1"><strong>Compétences:</strong> <span class="badge bg-info"><?= htmlspecialchars($o['competences']) ?></span></p>
                                <p class="mb-1"><strong>Date limite:</strong> <?= htmlspecialchars($o['date_limite']) ?></p>
                                <p class="mb-1"><strong>Adresse:</strong> <?= htmlspecialchars($o['adresse']) ?></p>
                                <p class="mb-1"><strong>Type:</strong> <span class="badge bg-primary"><?= htmlspecialchars($o['type']) ?></span></p>
                                <p class="mb-3"><strong>Entreprise ID:</strong> <?= htmlspecialchars($o['id_entreprise']) ?></p>
                                <div class="d-flex justify-content-between mt-auto">
                                    <button class="btn btn-warning btn-sm" onclick="editOffer(
                                        '<?= $o['id_offer'] ?>',
                                        '<?= htmlspecialchars($o['titre'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($o['description'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($o['competences'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($o['date_limite'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($o['adresse'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($o['type'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($o['id_entreprise'], ENT_QUOTES) ?>'
                                    )">
                                        <i data-feather="edit" width="14"></i> Modifier
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteOffer('<?= $o['id_offer'] ?>','<?= htmlspecialchars($o['titre'], ENT_QUOTES) ?>')">
                                        <i data-feather="trash" width="14"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center text-muted py-5">
                                <i data-feather="inbox" width="32"></i>
                                <p class="mt-2">Aucune offre disponible.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
     MODALE : AJOUTER
════════════════════════════════ -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>
                <div class="icon-wrap" style="background:#e8f5e9">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                </div>
                Ajouter une offre
            </h5>
            <button class="btn-close-x" onclick="closeAddModal()">&#x2715;</button>
        </div>
        <form method="POST" action="addOffre.php">
            <div class="modal-body-inner">
                <div class="section-divider">Informations principales</div>
                <div class="form-group">
                    <label class="field-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="titre" placeholder="Ex : Développeur Full Stack" required>
                </div>
                <div class="form-group">
                    <label class="field-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Décrivez le poste..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="field-label">Compétences <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="competences" placeholder="PHP, MySQL..." required>
                </div>
                <div class="section-divider">Détails du contrat</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Date limite <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date_limite" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Type de contrat <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" required>
                                <option value="">-- Choisir --</option>
                                <option value="CDI">CDI</option>
                                <option value="CDD">CDD</option>
                                <option value="Stage">Stage</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Alternance">Alternance</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="field-label">Adresse <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="adresse" placeholder="Ville, Pays" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="field-label">ID Entreprise <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="id_entreprise" placeholder="Ex : 1" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closeAddModal()">Annuler</button>
                <button type="submit" class="btn-save-blue" style="background:#28a745"
                    onmouseover="this.style.background='#1e7e34'" onmouseout="this.style.background='#28a745'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer l'offre
                </button>
            </div>
        </form>
    </div>
</div>
 
 
<!-- ════════════════════════════════
     MODALE : FORMULAIRE MODIFIER
════════════════════════════════ -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>
                <div class="icon-wrap" style="background:#fff3e0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fd7e14" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                Modifier l'offre
            </h5>
            <button class="btn-close-x" onclick="closeEditModal()">&#x2715;</button>
        </div>
        <form method="POST" action="updateOffre.php">
            <input type="hidden" name="id_offer" id="modal_id">
            <div class="modal-body-inner">
                <div class="section-divider">Informations principales</div>
                <div class="form-group">
                    <label class="field-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="titre" id="modal_titre" required>
                </div>
                <div class="form-group">
                    <label class="field-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" id="modal_description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label class="field-label">Compétences <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="competences" id="modal_competences" required>
                </div>
                <div class="section-divider">Détails du contrat</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Date limite <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date_limite" id="modal_date_limite" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Type de contrat <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" id="modal_type" required>
                                <option value="CDI">CDI</option>
                                <option value="CDD">CDD</option>
                                <option value="Stage">Stage</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Alternance">Alternance</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="field-label">Adresse <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="adresse" id="modal_adresse" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="field-label">ID Entreprise <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="id_entreprise" id="modal_id_entreprise" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-save-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
 
 
<!-- ════════════════════════════════
     MODALE : CONFIRMER SUPPRESSION
════════════════════════════════ -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-top">
            <h5>
                <div class="icon-wrap" style="background:#ffe5e5">
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
            <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()" style="padding:10px 24px">Annuler</button>
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
 
</section>
 
</main>
 
<!-- SCRIPTS -->
<script src="assets/js/feather-icons/feather.min.js"></script>
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/vendors/chartjs/Chart.min.js"></script>
<script src="assets/vendors/apexcharts/apexcharts.min.js"></script>
<script src="assets/js/pages/dashboard.js"></script>
<script src="assets/js/main.js"></script>
<script>
feather.replace();
 
/* ── AJOUTER ─────────────────────────────────────── */
function openAddModal(){
    document.getElementById('addModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeAddModal(){
    document.getElementById('addModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('addModal').addEventListener('click', function(e){ if(e.target===this) closeAddModal(); });
 
/* ── MODIFIER ─────────────────────────────── */
function editOffer(id, titre, description, competences, date, adresse, type, id_entreprise){
    document.getElementById('modal_id').value            = id;
    document.getElementById('modal_titre').value         = titre;
    document.getElementById('modal_description').value   = description;
    document.getElementById('modal_competences').value   = competences;
    document.getElementById('modal_date_limite').value   = date;
    document.getElementById('modal_adresse').value       = adresse;
    document.getElementById('modal_id_entreprise').value = id_entreprise;
    var sel = document.getElementById('modal_type');
    for(var i = 0; i < sel.options.length; i++){
        if(sel.options[i].value === type){ sel.selectedIndex = i; break; }
    }
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

/* ── FORMULAIRE MODIFIER ─────────────────────────── */
function closeEditModal(){
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('editModal').addEventListener('click', function(e){ if(e.target===this) closeEditModal(); });

/* ── SUPPRIMER ────────────────────────────── */
function deleteOffer(id, titre){
    document.getElementById('delete-titre').textContent = titre;
    document.getElementById('delete-confirm-btn').href  = 'deleteoffre.php?id=' + id;
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

/* ── CONFIRMATION SUPPRESSION ────────────────────── */
function closeDeleteModal(){
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow = '';
}
document.getElementById('deleteModal').addEventListener('click', function(e){ if(e.target===this) closeDeleteModal(); });
 
/* ── ECHAP ───────────────────────────────────────── */
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        closeAddModal();
        closeEditModal();
        closeDeleteModal();
    }
});
</script>
</body>
</html>