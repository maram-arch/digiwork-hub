<?php
require_once __DIR__ . '/../../config/config.php';
 
$db = Config::getconnexion();
 
// ── Message retour depuis updateOffre.php (redirect GET) ─────────────────────
$message     = "";
$messageType = "";
if (isset($_GET['status'], $_GET['msg'])) {
    $messageType = ($_GET['status'] === 'success') ? 'success' : 'danger';
    $message     = htmlspecialchars($_GET['msg']);
}
 
// ── Liste des offres ─────────────────────────────────────────────────────────
$sql    = "SELECT * FROM offre ORDER BY id_offer DESC";
$offres = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
    </style>
</head>
<body>
<div id="app">
 
    <!-- SIDEBAR -->
    <div id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header"><img src="assets/images/logo.png" style="width:250px;"></div>
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
                            <li><a href="offresExpirees.php">Offres expir&#233;es</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="users" width="20"></i><span>Gestion des utilisateurs</span></a>
                        <ul class="submenu">
                            <li><a href="listUsers.php">Tous les utilisateurs</a></li>
                            <li><a href="addUser.php">Ajouter un utilisateur</a></li>
                            <li><a href="rolesPermissions.php">R&#244;les &amp; permissions</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="message-square" width="20"></i><span>Gestion des forums</span></a>
                        <ul class="submenu">
                            <li><a href="listForums.php">Tous les forums</a></li>
                            <li><a href="categoriesForums.php">Cat&#233;gories</a></li>
                            <li><a href="signalements.php">Signalements</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-item has-sub">
                        <a href="#" class="sidebar-link"><i data-feather="folder" width="20"></i><span>Gestion des projets</span></a>
                        <ul class="submenu">
                            <li><a href="listProjets.php">Tous les projets</a></li>
                            <li><a href="addProjet.php">Ajouter un projet</a></li>
                            <li><a href="projetsArchives.php">Archiv&#233;s</a></li>
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
                            <a class="dropdown-item" href="#"><i data-feather="settings"></i> Param&#232;tres</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#"><i data-feather="log-out"></i> D&#233;connexion</a>
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
                        <p class="text-subtitle text-muted">G&#233;rer toutes les offres disponibles</p>
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
                    <?= $messageType === 'success' ? '&#9989;' : '&#10060;' ?> <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>
 
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
                                        <th>Comp&#233;tences</th><th>Date limite</th>
                                        <th>Adresse</th><th>Type</th><th>Entreprise</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($offres) > 0): ?>
                                    <?php foreach ($offres as $o): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($o['id_offer']) ?></td>
                                        <td><strong><?= htmlspecialchars($o['titre']) ?></strong></td>
                                        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($o['description']) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($o['competences']) ?></span></td>
                                        <td><?= htmlspecialchars($o['date_limite']) ?></td>
                                        <td><?= htmlspecialchars($o['adresse']) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($o['type']) ?></span></td>
                                        <td><?= htmlspecialchars($o['id_entreprise']) ?></td>
                                        <td style="white-space:nowrap">
                                            <!-- Bouton modifier -> ouvre modale via data-attributes -->
                                            <button class="btn-edit-orange btn-open-modal"
                                                data-id="<?= $o['id_offer'] ?>"
                                                data-titre="<?= htmlspecialchars($o['titre'], ENT_QUOTES) ?>"
                                                data-description="<?= htmlspecialchars($o['description'], ENT_QUOTES) ?>"
                                                data-competences="<?= htmlspecialchars($o['competences'], ENT_QUOTES) ?>"
                                                data-date="<?= htmlspecialchars($o['date_limite'], ENT_QUOTES) ?>"
                                                data-adresse="<?= htmlspecialchars($o['adresse'], ENT_QUOTES) ?>"
                                                data-type="<?= htmlspecialchars($o['type'], ENT_QUOTES) ?>"
                                                data-entreprise="<?= htmlspecialchars($o['id_entreprise'], ENT_QUOTES) ?>"
                                            ><i data-feather="edit-2" width="14"></i></button>
                                            <!-- Bouton supprimer -> ouvre modale confirmation -->
                                            <button class="btn btn-sm btn-danger ml-1 btn-open-delete"
                                                data-id="<?= $o['id_offer'] ?>"
                                                data-titre="<?= htmlspecialchars($o['titre'], ENT_QUOTES) ?>">
                                                <i data-feather="trash-2" width="14"></i>
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
 
<!-- MODALE MODIFICATION -->
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
            <button class="btn-close-x" onclick="closeModal()">&#x2715;</button>
        </div>
 
        <!-- ACTION POINTE VERS updateOffre.php -->
        <form method="POST" action="updateOffre.php">
            <input type="hidden" name="id_offer" id="modal_id">
            <div class="modal-body-inner">
 
                <div class="section-divider">Informations principales</div>
 
                <div class="form-group">
                    <label class="field-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="titre" id="modal_titre" placeholder="Titre du poste" required>
                </div>
                <div class="form-group">
                    <label class="field-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" id="modal_description" rows="3" placeholder="Description du poste..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="field-label">Comp&#233;tences <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="competences" id="modal_competences" placeholder="PHP, MySQL..." required>
                </div>
 
                <div class="section-divider">D&#233;tails du contrat</div>
 
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
                            <input type="text" class="form-control" name="adresse" id="modal_adresse" placeholder="Ville, Pays" required>
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
                <button type="button" class="btn-cancel-modal" onclick="closeModal()">Annuler</button>
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
 
<script src="assets/js/feather-icons/feather.min.js"></script>
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/main.js"></script>
<script>
function openModal(id,titre,description,competences,date_limite,adresse,type,id_entreprise){
    document.getElementById('modal_id').value           = id;
    document.getElementById('modal_titre').value        = titre;
    document.getElementById('modal_description').value  = description;
    document.getElementById('modal_competences').value  = competences;
    document.getElementById('modal_date_limite').value  = date_limite;
    document.getElementById('modal_adresse').value      = adresse;
    document.getElementById('modal_id_entreprise').value= id_entreprise;
    var sel = document.getElementById('modal_type');
    for(var i=0;i<sel.options.length;i++){
        if(sel.options[i].value===type){sel.selectedIndex=i;break;}
    }
    document.getElementById('editModal').classList.add('show');
    document.body.style.overflow='hidden';
}
function closeModal(){
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow='';
}
// Event delegation suppression
document.addEventListener('click', function(e){
    var btnDel = e.target.closest('.btn-open-delete');
    if(btnDel){
        var id    = btnDel.dataset.id;
        var titre = btnDel.dataset.titre;
        document.getElementById('delete-titre').textContent = titre;
        document.getElementById('delete-confirm-btn').href  = 'deleteOffre.php?id=' + id;
        document.getElementById('deleteModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }
});
function closeDeleteModal(){
    document.getElementById('deleteModal').classList.remove('show');
    document.body.style.overflow='';
}
document.getElementById('deleteModal').addEventListener('click',function(e){if(e.target===this)closeDeleteModal();});
 
// Event delegation sur tous les boutons .btn-open-modal
document.addEventListener('click', function(e){
    var btn = e.target.closest('.btn-open-modal');
    if(btn){
        openModal(
            btn.dataset.id,
            btn.dataset.titre,
            btn.dataset.description,
            btn.dataset.competences,
            btn.dataset.date,
            btn.dataset.adresse,
            btn.dataset.type,
            btn.dataset.entreprise
        );
    }
});
document.getElementById('editModal').addEventListener('click',function(e){if(e.target===this)closeModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeModal();});
feather.replace();
</script>
 
<!-- MODALE CONFIRMATION SUPPRESSION -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-top">
            <h5>
                <div class="icon-edit-wrap" style="background:#ffe5e5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                        fill="none" stroke="#dc3545" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                    fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <h6 style="font-size:16px;font-weight:600;color:#2d3748;margin-bottom:8px">&#202;tes-vous s&#251;r ?</h6>
            <p style="color:#6c757d;font-size:14px;margin-bottom:4px">Vous allez supprimer l'offre :</p>
            <p id="delete-titre" style="font-weight:600;color:#dc3545;font-size:14px;margin-bottom:0"></p>
            <p style="color:#adb5bd;font-size:12px;margin-top:8px">Cette action est irr&#233;versible.</p>
        </div>
        <div class="modal-foot" style="justify-content:center;gap:12px">
            <button type="button" class="btn-cancel-modal" onclick="closeDeleteModal()" style="padding:10px 28px">
                Annuler
            </button>
            <a id="delete-confirm-btn" href="#"
               style="background:#dc3545;border:none;color:#fff;border-radius:8px;padding:10px 28px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:6px;text-decoration:none;transition:background .15s"
               onmouseover="this.style.background='#b02a37'" onmouseout="this.style.background='#dc3545'">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
                Oui, supprimer
            </a>
        </div>
    </div>
</div>
 
</body>
</html>   