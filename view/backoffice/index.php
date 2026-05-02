<?php
// view/backoffice/index.php
// Dashboard de gestion des publications

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/PublicationController.php';

$db = Config::getConnexion();

// Compteur de publications
$nbPublications = 0;
try {
    $nbPublications = $db->query("SELECT COUNT(*) FROM forums")->fetchColumn();
} catch (Exception $e) {
}

$controller = new PublicationController();
// Au lieu de listPublication()->fetchAll() qui peut planter
$publications = $controller->listPublication();

// Si c'est un PDOStatement, on le fetch
if ($publications instanceof PDOStatement) {
    $publications = $publications->fetchAll(PDO::FETCH_ASSOC);
}

$message = $messageType = "";
if (isset($_GET['status'], $_GET['msg'])) {
    $messageType = ($_GET['status'] === 'success') ? 'success' : 'danger';
    $message = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des publications - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <style>
        /* styles identiques à l'original, adaptés pour les publications */
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
        .btn-save-green{background-color:#28a745;border:none;color:#fff;border-radius:8px;padding:9px 22px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s}
        .btn-save-green:hover{background-color:#1e7e34}
        .btn-save-blue{background-color:#435ebe;border:none;color:#fff;border-radius:8px;padding:9px 22px;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s}
        .btn-save-blue:hover{background-color:#3348a8}
        .btn-cancel-modal{background:#f5f6fa;border:none;color:#666;border-radius:8px;padding:9px 20px;font-size:14px;cursor:pointer;transition:background .15s}
        .btn-cancel-modal:hover{background:#e8e9ef}
        .field-label{font-size:12px;font-weight:600;color:#6c757d;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;display:block}
        .form-control{font-size:14px;border-radius:8px}
        .form-control:focus{border-color:#435ebe;box-shadow:0 0 0 3px rgba(67,94,190,.12)}
        .section-divider{font-size:11px;font-weight:700;color:#adb5bd;text-transform:uppercase;letter-spacing:.08em;margin:4px 0 14px;display:flex;align-items:center;gap:8px}
        .section-divider::after{content:'';flex:1;height:1px;background:#f0f1f5}
        .form-control.is-invalid{border-color:#dc3545!important;background-color:#fff8f8}
        .error-message{font-size:12px;color:#dc3545;margin-top:4px;display:none;font-weight:500}
        .error-message.show{display:block}
        .crud-card{border-radius:14px;cursor:pointer;transition:transform .18s,box-shadow .18s;border:none;user-select:none}
        .crud-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.14)!important}
        .crud-icon-circle{width:66px;height:66px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
        .crud-action-label{display:inline-flex;align-items:center;gap:5px;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:600;margin-top:12px}
        .badge-cat{background:#e9ecef;color:#495057;padding:4px 8px;border-radius:20px;font-size:11px;font-weight:500}
        .event-badge{background:#fff3cd;color:#856404;padding:2px 6px;border-radius:20px;font-size:10px}
        .image-preview{max-width:60px;max-height:40px;border-radius:6px;object-fit:cover}
    </style>
</head>
<body>
<div id="app">

    <!-- SIDEBAR (entièrement réécrite pour la gestion forum) -->
    <aside id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" style="width:230px;">
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    <li class="sidebar-title">Menu Principal</li>

                    <!-- Gestion forum (menu principal avec sous‑menus) -->
                    <li class="sidebar-item has-sub active">
    <a href="#" class="sidebar-link">
        <i data-feather="message-square"></i>
        <span>Gestion Forum</span>
    </a>
    <ul class="submenu open">
        <li><a href="index.php">Toutes les publications</a></li>
        <li><a href="listCommentaires.php">Gestion des commentaires</a></li>
        <li><a href="addPublication.php">Ajouter une publication</a></li>
    </ul>
</li>

                    <li class="sidebar-title">Autres modules</li>

                    <li class="sidebar-item">
                        <a href="listUsers.php" class="sidebar-link">
                            <i data-feather="users" width="20"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listProjets.php" class="sidebar-link">
                            <i data-feather="folder" width="20"></i>
                            <span>Gestion des projets</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listPacks.php" class="sidebar-link">
                            <i data-feather="package" width="20"></i>
                            <span>Gestion des packs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="listEvents.php" class="sidebar-link">
                            <i data-feather="calendar" width="20"></i>
                            <span>Gestion des Events</span>
                        </a>
                    </li>
                </ul>
            </div>
            <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
        </div>
    </aside>

    <!-- MAIN (identique, mais adaptée pour les publications) -->
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

        <div class="main-content container-fluid">
            <header class="page-title">
                <h3>Gestion des publications du forum</h3>
                <p class="text-subtitle text-muted">Gérer toutes les publications et commentaires</p>
            </header>

            <section class="section">

                <!-- CARTE AJOUTER -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card crud-card shadow-sm text-center" style="border-top:5px solid #28a745"
                             onclick="openAddModal()">
                            <div class="card-body">
                                <div class="crud-icon-circle" style="background:#e8f5e9">
                                    <i data-feather="plus-circle" style="color:#28a745;width:32px;height:32px"></i>
                                </div>
                                <h4 style="font-weight:800;color:#28a745;margin:0;font-size:1.3rem">Ajouter</h4>
                                <p style="font-size:13px;font-weight:600;color:#6c757d;margin:6px 0 4px;text-transform:uppercase;letter-spacing:.05em">Nouvelle publication</p>
                                <p style="font-size:12px;color:#adb5bd;margin:0">Créer un sujet sur le forum</p>
                                <span class="crud-action-label" style="background:#e8f5e9;color:#28a745">
                                    <i data-feather="plus" width="12"></i> Cliquez pour ajouter
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($message !== ""): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>

                <!-- TABLEAU DES PUBLICATIONS -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Toutes les publications (<?= $nbPublications ?>)</h4>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Titre</th>
                                        <th>Contenu</th>
                                        <th>Catégorie</th>
                                        <th>Auteur</th>
                                        <th>Date</th>
                                        <th>Vues/Likes</th>
                                        <th>Événement</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($publications) > 0): ?>
                                    <?php foreach ($publications as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['id_publication']) ?></td>
                                        <td><strong><?= htmlspecialchars($p['titre']) ?></strong></td>
                                        <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                            <?= htmlspecialchars(substr($p['contenu'], 0, 80)) ?>...
                                        </td>
                                        <td><span class="badge-cat"><?= htmlspecialchars($p['categorie']) ?></span></td>
                                        <td><?= htmlspecialchars($p['prenom'] ?? '') . ' ' . htmlspecialchars($p['nom'] ?? '') ?></td>
                                        <td><?= date('d/m/Y', strtotime($p['date_publication'])) ?></td>
                                        <td><?= $p['nb_vues'] ?> / <?= $p['nb_likes'] ?></td>
                                        <td>
                                            <?php if ($p['is_event']): ?>
                                                <span class="event-badge" title="<?= htmlspecialchars($p['event_date'] ?? '') . ' - ' . htmlspecialchars($p['event_lieu'] ?? '') ?>">🎉 Événement</span>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                        <td style="white-space:nowrap">
                                            <button class="btn btn-warning btn-sm btn-edit-pub"
                                                data-id="<?= (int)$p['id_publication'] ?>"
                                                data-titre="<?= htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-contenu="<?= htmlspecialchars($p['contenu'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-categorie="<?= htmlspecialchars($p['categorie'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-is_event="<?= $p['is_event'] ?>"
                                                data-event_date="<?= htmlspecialchars($p['event_date'] ?? '', ENT_QUOTES) ?>"
                                                data-event_lieu="<?= htmlspecialchars($p['event_lieu'] ?? '', ENT_QUOTES) ?>"
                                                data-image="<?= htmlspecialchars($p['image'] ?? '', ENT_QUOTES) ?>">
                                                <i data-feather="edit" width="14"></i> Modifier
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-delete-pub ml-1"
                                                data-id="<?= (int)$p['id_publication'] ?>"
                                                data-titre="<?= htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i data-feather="trash" width="14"></i> Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i data-feather="inbox" width="32"></i>
                                            <p class="mt-2">Aucune publication disponible.</p>
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
    </main>
</div>

<!-- MODALE : AJOUTER PUBLICATION -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-top">
            <h5>
                <div class="icon-wrap" style="background:#e8f5e9">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                </div>
                Ajouter une publication
            </h5>
            <button class="btn-close-x" onclick="closeAddModal()">&#x2715;</button>
        </div>
        <form method="POST" action="addPublication.php" id="addForm" onsubmit="return validateAddForm(event)" enctype="multipart/form-data">
            <div class="modal-body-inner">
                <div class="section-divider">Contenu principal</div>
                <div class="form-group">
                    <label class="field-label">Titre <span class="text-danger">*</span> (min 3 caractères)</label>
                    <input type="text" class="form-control" name="titre" placeholder="Titre de la publication">
                    <div class="error-message" data-field="titre"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Contenu <span class="text-danger">*</span> (min 10 caractères)</label>
                    <textarea class="form-control" name="contenu" rows="4" placeholder="Votre message..."></textarea>
                    <div class="error-message" data-field="contenu"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Catégorie <span class="text-danger">*</span></label>
                    <select class="form-control" name="categorie">
                        <option value="general">Général</option>
                        <option value="stage">Stage</option>
                        <option value="job">Job</option>
                        <option value="question">Question</option>
                        <option value="evenement">Événement</option>
                    </select>
                    <div class="error-message" data-field="categorie"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Image (optionnelle)</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="text-muted">JPG, PNG max 2Mo</small>
                </div>
                <div class="section-divider">Options événementielles</div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="is_event" id="add_is_event" value="1">
                    <label class="form-check-label" for="add_is_event">C'est un événement</label>
                </div>
                <div class="row event-fields-add" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Date de l'événement</label>
                            <input type="date" class="form-control" name="event_date">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Lieu</label>
                            <input type="text" class="form-control" name="event_lieu" placeholder="Lieu de l'événement">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closeAddModal()">Annuler</button>
                <button type="submit" class="btn-save-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Publier
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODALE : MODIFIER PUBLICATION -->
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
                Modifier la publication
            </h5>
            <button class="btn-close-x" onclick="closeEditModal()">&#x2715;</button>
        </div>
        <form method="POST" action="updatePublication.php" id="editForm" onsubmit="return validateEditForm(event)" enctype="multipart/form-data">
            <input type="hidden" name="id_publication" id="edit_id">
            <div class="modal-body-inner">
                <div class="section-divider">Contenu principal</div>
                <div class="form-group">
                    <label class="field-label">Titre <span class="text-danger">*</span> (min 3)</label>
                    <input type="text" class="form-control" name="titre" id="edit_titre">
                    <div class="error-message" data-field="titre"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Contenu <span class="text-danger">*</span> (min 10)</label>
                    <textarea class="form-control" name="contenu" id="edit_contenu" rows="4"></textarea>
                    <div class="error-message" data-field="contenu"></div>
                </div>
                <div class="form-group">
                    <label class="field-label">Catégorie</label>
                    <select class="form-control" name="categorie" id="edit_categorie">
                        <option value="general">Général</option>
                        <option value="stage">Stage</option>
                        <option value="job">Job</option>
                        <option value="question">Question</option>
                        <option value="evenement">Événement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="field-label">Nouvelle image (laisser vide pour conserver)</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <div id="currentImagePreview"></div>
                </div>
                <div class="section-divider">Événement</div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="is_event" id="edit_is_event" value="1">
                    <label class="form-check-label" for="edit_is_event">C'est un événement</label>
                </div>
                <div class="row event-fields-edit" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Date événement</label>
                            <input type="date" class="form-control" name="event_date" id="edit_event_date">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="field-label">Lieu</label>
                            <input type="text" class="form-control" name="event_lieu" id="edit_event_lieu">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-save-blue">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODALE : SUPPRIMER -->
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
            <p style="color:#6c757d;font-size:14px;margin-bottom:4px">Vous allez supprimer la publication :</p>
            <p id="delete-titre" style="font-weight:600;color:#dc3545;font-size:14px;margin-bottom:0"></p>
            <p style="color:#adb5bd;font-size:12px;margin-top:8px">Tous les commentaires et likes seront également supprimés.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;gap:12px">
            <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()" style="padding:10px 24px">Annuler</button>
            <a id="delete-confirm-btn" href="#" style="background:#dc3545;color:#fff;border-radius:8px;padding:10px 28px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:6px;text-decoration:none;transition:background .15s"
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

/* ==================== VALIDATION ==================== */
function clearFieldErrors(form) {
    form.querySelectorAll('.form-control').forEach(i => i.classList.remove('is-invalid'));
    form.querySelectorAll('.error-message').forEach(m => { m.classList.remove('show'); m.textContent = ''; });
}
function setFieldError(form, field, msg) {
    let input = form.querySelector(`[name="${field}"]`);
    if (input) {
        input.classList.add('is-invalid');
        let group = input.closest('.form-group');
        let err = group ? group.querySelector('.error-message') : null;
        if (err) { err.textContent = msg; err.classList.add('show'); }
    }
}
function validateAddForm(e) {
    e.preventDefault();
    let form = document.getElementById('addForm');
    clearFieldErrors(form);
    let titre = form.querySelector('[name="titre"]').value.trim();
    let contenu = form.querySelector('[name="contenu"]').value.trim();
    let categorie = form.querySelector('[name="categorie"]').value;
    let ok = true;
    if (titre.length < 3) { setFieldError(form, 'titre', 'Titre minimum 3 caractères'); ok = false; }
    if (contenu.length < 10) { setFieldError(form, 'contenu', 'Contenu minimum 10 caractères'); ok = false; }
    if (!categorie) { setFieldError(form, 'categorie', 'Choisissez une catégorie'); ok = false; }
    if (ok) form.submit();
    return false;
}
function validateEditForm(e) {
    e.preventDefault();
    let form = document.getElementById('editForm');
    clearFieldErrors(form);
    let titre = form.querySelector('[name="titre"]').value.trim();
    let contenu = form.querySelector('[name="contenu"]').value.trim();
    let ok = true;
    if (titre.length < 3) { setFieldError(form, 'titre', 'Titre minimum 3 caractères'); ok = false; }
    if (contenu.length < 10) { setFieldError(form, 'contenu', 'Contenu minimum 10 caractères'); ok = false; }
    if (ok) form.submit();
    return false;
}

/* Affichage champs événement */
document.getElementById('add_is_event')?.addEventListener('change', function() {
    let f = document.querySelector('.event-fields-add');
    if (f) f.style.display = this.checked ? 'flex' : 'none';
});
document.getElementById('edit_is_event')?.addEventListener('change', function() {
    let f = document.querySelector('.event-fields-edit');
    if (f) f.style.display = this.checked ? 'flex' : 'none';
});

/* Modale Ajouter */
function openAddModal() {
    let form = document.getElementById('addForm');
    form.reset();
    clearFieldErrors(form);
    document.querySelector('.event-fields-add').style.display = 'none';
    document.getElementById('add_is_event').checked = false;
    document.getElementById('addModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeAddModal() { document.getElementById('addModal').classList.remove('show'); document.body.style.overflow = ''; }
document.getElementById('addModal')?.addEventListener('click', function(e) { if (e.target === this) closeAddModal(); });

/* Modale Modifier */
document.addEventListener('click', function(e) {
    let btn = e.target.closest('.btn-edit-pub');
    if (!btn) return;
    let d = btn.dataset;
    document.getElementById('edit_id').value = d.id;
    document.getElementById('edit_titre').value = d.titre;
    document.getElementById('edit_contenu').value = d.contenu;
    document.getElementById('edit_categorie').value = d.categorie;
    let chk = document.getElementById('edit_is_event');
    let isEvent = (d.is_event == 1 || d.is_event === '1');
    chk.checked = isEvent;
    let eventDiv = document.querySelector('.event-fields-edit');
    if (eventDiv) eventDiv.style.display = isEvent ? 'flex' : 'none';
    document.getElementById('edit_event_date').value = d.event_date || '';
    document.getElementById('edit_event_lieu').value = d.event_lieu || '';
    let previewDiv = document.getElementById('currentImagePreview');
    if (previewDiv) {
        if (d.image && d.image !== 'null') {
            previewDiv.innerHTML = `<img src="../../${d.image}" class="image-preview" alt="Image actuelle"><br><small>Image actuelle</small>`;
        } else {
            previewDiv.innerHTML = '';
        }
    }
    clearFieldErrors(document.getElementById('editForm'));
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow = 'hidden';
});
function closeEditModal() { document.getElementById('editModal').classList.remove('show'); document.body.style.overflow = ''; }
document.getElementById('editModal')?.addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });

/* Modale Supprimer */
document.addEventListener('click', function(e) {
    let btn = e.target.closest('.btn-delete-pub');
    if (!btn) return;
    document.getElementById('delete-titre').textContent = btn.dataset.titre;
    document.getElementById('delete-confirm-btn').href = 'deletePublication.php?id=' + btn.dataset.id;
    document.getElementById('deleteModal').classList.add('show');
    document.body.style.overflow = 'hidden';
});
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); document.body.style.overflow = ''; }
document.getElementById('deleteModal')?.addEventListener('click', function(e) { if (e.target === this) closeDeleteModal(); });

/* Touche Echap */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeAddModal(); closeEditModal(); closeDeleteModal(); }
});
</script>
</body>
</html>