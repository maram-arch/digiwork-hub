<?php
session_start();

require_once __DIR__ . '/../../controller/UserController.php';

$action = $_GET['action'] ?? '';

$controller = new UserController();

if ($action === 'online-users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $onlineUsers = $controller->getOnlineUsers();
        echo json_encode([
            'success' => true,
            'count' => count($onlineUsers),
            'users' => $onlineUsers,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'count' => 0,
            'users' => [],
        ]);
    }
    exit;
}

$loggedInUser = null;
if (isset($_SESSION['user_id'])) {
    try {
        $loggedInUser = $controller->findUser((int) $_SESSION['user_id']);
    } catch (Throwable $e) {
        $loggedInUser = null;
    }
}

// Vérifier si l'utilisateur est connecté et a un rôle autorisé
$allowedDashboardRoles = ['admin', 'condidat', 'entreprise', 'sponsor'];
if (!$loggedInUser || !in_array($loggedInUser['role'] ?? '', $allowedDashboardRoles, true)) {
    header('Location: login.php');
    exit;
}

// Set session role from the logged-in user
$_SESSION['role'] = $loggedInUser['role'];
$userRole = $loggedInUser['role'];

// No front-only gate: allow admins to log in from front or backoffice.

try {
    $controller->touchUserSession((int) $loggedInUser['id_user']);
} catch (Throwable $e) {
}

// Gestion de la déconnexion
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    try {
        $controller->logoutUser((int) $loggedInUser['id_user']);
    } catch (Throwable $e) {
    }
    header('Location: login.php');
    exit;
}

// Récupérer la liste de tous les utilisateurs (admin only)
$allUsers = [];
if ($userRole === 'admin') {
    try {
        $allUsers = $controller->listUsers();
    } catch (Throwable $e) {
        $allUsers = [];
    }
}

$boPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiWork Hub - <?= $userRole === 'admin' ? 'Dashboard Administrateur' : 'Mon Espace' ?></title>

    <script>
        if (window.location.protocol === 'file:') {
            window.location.replace('http://localhost/projectttttttt/digiwork-hub/view/backoffice/index.php');
        }
    </script>
    
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/chartjs/Chart.min.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: #f5f5f5;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }
        .admin-badge {
            display: inline-block;
            background: #00A651;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div id="app">
        <?php
        define('BACKOFFICE_LAYOUT_LOADED', true);
        $activePage = $boPage;
        require __DIR__ . '/layouts/sidebar.php'; ?>
        
        <div id="main">
            <nav class="navbar navbar-header navbar-expand navbar-light">
                <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
                <button class="btn navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                        <li class="dropdown nav-icon">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <div class="d-lg-inline-block">
                                    <i data-feather="bell"></i>
                                    <span class="badge bg-danger notification-badge">3</span>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-large">
                                <h6 class='py-2 px-4'>Notifications</h6>
                                <ul class="list-group rounded-none">
                                    <li class="list-group-item border-0 align-items-start">
                                        <div class="avatar bg-success mr-3">
                                            <span class="avatar-content"><i data-feather="briefcase"></i></span>
                                        </div>
                                        <div>
                                            <h6 class='text-bold'>Nouvelle offre publiée</h6>
                                            <p class='text-xs'>Une nouvelle mission "Développeur IA" vient d'être publiée</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item border-0 align-items-start">
                                        <div class="avatar bg-warning mr-3">
                                            <span class="avatar-content"><i data-feather="alert-triangle"></i></span>
                                        </div>
                                        <div>
                                            <h6 class='text-bold'>Réclamation en attente</h6>
                                            <p class='text-xs'>Une nouvelle réclamation nécessite votre attention</p>
                                        </div>
                                    </li>
                                    <li class="list-group-item border-0 align-items-start">
                                        <div class="avatar bg-info mr-3">
                                            <span class="avatar-content"><i data-feather="message-circle"></i></span>
                                        </div>
                                        <div>
                                            <h6 class='text-bold'>Message signalé</h6>
                                            <p class='text-xs'>Un message a été signalé sur le forum</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <div class="avatar mr-1">
                                    <img src="assets/images/avatar/avatar-s-1.png" alt="Admin DigiWork Hub">
                                </div>
                                <div class="d-none d-md-block d-lg-inline-block"><?php echo htmlspecialchars($loggedInUser['email'] ?? 'Admin'); ?></div>
                                <span class="admin-badge">Connecte</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#"><i data-feather="user"></i> Mon profil</a>
                                <a class="dropdown-item" href="#"><i data-feather="settings"></i> Paramètres</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="?logout=1"><i data-feather="log-out"></i> Déconnexion</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="main-content container-fluid">
                <?php if ($boPage === 'packs'): ?>
                <!-- ===== Packs Management Page ===== -->
                <div class="page-title">
                    <h3>Gestion des Packs</h3>
                    <p class="text-subtitle text-muted">Créez, modifiez et supprimez les packs de la plateforme</p>
                </div>
                <?php
                require_once __DIR__ . '/../../model/Pack.php';
                $packModelBO = new Pack();
                try { $packsBO = $packModelBO->getAll()->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) { $packsBO = []; }
                ?>
                <div class="card mb-4">
                    <div class="card-header"><h4 class="card-title mb-0">Ajouter / Modifier un Pack</h4></div>
                    <div class="card-body">
                        <form id="packFormBO">
                            <input type="hidden" id="bo-action" name="action" value="add">
                            <input type="hidden" id="bo-id-pack" name="id-pack" value="">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Nom du Pack *</label>
                                    <input type="text" id="bo-nom" name="nom" class="form-control" maxlength="20" placeholder="ex: Premium">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Prix (dt) *</label>
                                    <input type="number" step="0.01" id="bo-prix" name="prix" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Durée (date début) *</label>
                                    <input type="date" id="bo-duree" name="duree" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Projets max *</label>
                                    <input type="number" id="bo-nb" name="nb" class="form-control" min="1" max="999" placeholder="10">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Support Prioritaire *</label>
                                    <select id="bo-support" name="support" class="form-control">
                                        <option value="">-- Sélectionnez --</option>
                                        <option value="oui">Oui</option>
                                        <option value="non">Non</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label>Description *</label>
                                    <textarea id="bo-description" name="description" class="form-control" rows="3" maxlength="500"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success" id="bo-submit-btn">Enregistrer</button>
                            <button type="button" class="btn btn-secondary ml-2" id="bo-cancel-btn" style="display:none;" onclick="boResetForm()">Annuler</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Liste des Packs <span id="bo-count-packs">(<?= count($packsBO) ?>)</span></h4>
                        <input type="text" id="bo-search" class="form-control" style="width:200px;" placeholder="Rechercher...">
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0" id="bo-packs-table">
                                <thead><tr><th>ID</th><th>Nom</th><th>Prix</th><th>Durée</th><th>Projets Max</th><th>Support</th><th>Actions</th></tr></thead>
                                <tbody id="bo-packs-tbody">
                                    <tr><td colspan="7" class="text-center">Chargement...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <script>
                var boAllPacks = [];
                function boEscape(s) { return String(s||'').replace(/[&<>]/g,function(m){return m==='&'?'&amp;':m==='<'?'&lt;':'&gt;';}); }
                function boRenderPacks(packs) {
                    var tbody = document.getElementById('bo-packs-tbody');
                    if (!packs.length) { tbody.innerHTML = '<tr><td colspan="7" class="text-center">Aucun pack</td></tr>'; return; }
                    tbody.innerHTML = packs.map(function(p) {
                        return '<tr id="bo-pack-'+p['id-pack']+'">' +
                            '<td>'+p['id-pack']+'</td>' +
                            '<td><strong>'+boEscape(p['nom-pack'])+'</strong></td>' +
                            '<td>'+p.prix+' dt</td>' +
                            '<td>'+boEscape(p.duree)+'</td>' +
                            '<td>'+p['nb-proj-max']+'</td>' +
                            '<td>'+boEscape(p['support-prioritaire'])+'</td>' +
                            '<td>' +
                              '<button class="btn btn-sm btn-primary mr-1" onclick=\'boEditPack('+JSON.stringify(p)+')\'>Modifier</button>' +
                              '<button class="btn btn-sm btn-danger" onclick="boDeletePack('+p['id-pack']+')">Supprimer</button>' +
                            '</td></tr>';
                    }).join('');
                }
                function boLoadPacks() {
                    fetch('/projectttttttt/controller/PackController.php?action=getAll')
                        .then(function(r){return r.json();})
                        .then(function(packs){ boAllPacks=packs; document.getElementById('bo-count-packs').textContent='('+packs.length+')'; boRenderPacks(packs); })
                        .catch(function(){});
                }
                function boEditPack(p) {
                    document.getElementById('bo-action').value='update';
                    document.getElementById('bo-id-pack').value=p['id-pack'];
                    document.getElementById('bo-nom').value=p['nom-pack'];
                    document.getElementById('bo-prix').value=p.prix;
                    document.getElementById('bo-duree').value=p.duree;
                    document.getElementById('bo-description').value=p.description;
                    document.getElementById('bo-nb').value=p['nb-proj-max'];
                    document.getElementById('bo-support').value=p['support-prioritaire'];
                    document.getElementById('bo-submit-btn').textContent='Mettre à jour';
                    document.getElementById('bo-cancel-btn').style.display='inline-block';
                    window.scrollTo({top:0,behavior:'smooth'});
                }
                function boResetForm() {
                    document.getElementById('packFormBO').reset();
                    document.getElementById('bo-action').value='add';
                    document.getElementById('bo-id-pack').value='';
                    document.getElementById('bo-submit-btn').textContent='Enregistrer';
                    document.getElementById('bo-cancel-btn').style.display='none';
                }
                async function boDeletePack(id) {
                    if (!confirm('Supprimer ce pack ?')) return;
                    var fd = new FormData(); fd.append('action','delete'); fd.append('id',id); fd.append('ajax','1');
                    var r = await fetch('/projectttttttt/controller/PackController.php',{method:'POST',body:fd});
                    var d = await r.json();
                    if (d.status==='success') { var row=document.getElementById('bo-pack-'+id); if(row)row.remove(); }
                    else alert(d.message||'Erreur');
                }
                document.getElementById('packFormBO').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    var fd = new FormData(this); fd.append('ajax','1');
                    var r = await fetch('/projectttttttt/controller/PackController.php',{method:'POST',body:fd});
                    var d = await r.json();
                    if (d.status==='success') { boResetForm(); boLoadPacks(); }
                    else alert(d.message||'Erreur');
                });
                document.getElementById('bo-search').addEventListener('input', function() {
                    var q = this.value.toLowerCase();
                    boRenderPacks(boAllPacks.filter(function(p){ return (p['nom-pack']||'').toLowerCase().includes(q)||(p.description||'').toLowerCase().includes(q); }));
                });
                document.addEventListener('DOMContentLoaded', boLoadPacks);
                </script>

                <?php elseif ($boPage === 'abonnements'): ?>
                <!-- ===== Abonnements Page ===== -->
                <div class="page-title">
                    <h3>Gestion des Abonnements</h3>
                    <p class="text-subtitle text-muted">Consultez et gérez les abonnements des utilisateurs</p>
                </div>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Abonnements <span id="bo-subs-count"></span></h4>
                        <input type="text" id="bo-subs-search" class="form-control" style="width:200px;" placeholder="Rechercher...">
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Utilisateur</th><th>Pack</th><th>Date début</th><th>Date fin</th><th>Statut</th><th>Actions</th></tr></thead>
                                <tbody id="bo-subs-tbody"><tr><td colspan="7" class="text-center">Chargement...</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <script>
                var boAllSubs = [];
                function boEscSub(s){return String(s||'').replace(/[&<>]/g,function(m){return m==='&'?'&amp;':m==='<'?'&lt;':'&gt;';});}
                function boRenderSubs(subs) {
                    var tbody = document.getElementById('bo-subs-tbody');
                    document.getElementById('bo-subs-count').textContent='('+subs.length+')';
                    if (!subs.length) { tbody.innerHTML='<tr><td colspan="7" class="text-center">Aucun abonnement</td></tr>'; return; }
                    tbody.innerHTML = subs.map(function(s) {
                        return '<tr>' +
                            '<td>'+boEscSub(s.id_abonnement||s.id||'')+'</td>' +
                            '<td>'+boEscSub(s.email||s.id_user||'')+'</td>' +
                            '<td>'+boEscSub(s['nom-pack']||s.id_pack||'')+'</td>' +
                            '<td>'+boEscSub(s.date_debut||'')+'</td>' +
                            '<td>'+boEscSub(s.date_fin||'')+'</td>' +
                            '<td><span class="badge '+(s.statut==='actif'?'bg-success':'bg-secondary')+'">'+boEscSub(s.statut||'')+'</span></td>' +
                            '<td><button class="btn btn-sm btn-danger" onclick="boDeleteSub('+boEscSub(s.id_abonnement||s.id||0)+')">Supprimer</button></td>' +
                            '</tr>';
                    }).join('');
                }
                async function boDeleteSub(id) {
                    if (!confirm('Supprimer cet abonnement ?')) return;
                    var fd = new FormData(); fd.append('action','delete'); fd.append('id',id); fd.append('ajax','1');
                    var r = await fetch('/projectttttttt/controller/AbonnementController.php',{method:'POST',body:fd});
                    var d = await r.json();
                    if (d.status==='success'||d.success) { boLoadSubs(); }
                    else alert(d.message||'Erreur');
                }
                function boLoadSubs() {
                    fetch('/projectttttttt/controller/AbonnementController.php?action=getAll')
                        .then(function(r){return r.json();})
                        .then(function(subs){ boAllSubs=subs; boRenderSubs(subs); })
                        .catch(function(){});
                }
                document.getElementById('bo-subs-search').addEventListener('input', function() {
                    var q = this.value.toLowerCase();
                    boRenderSubs(boAllSubs.filter(function(s){ return JSON.stringify(s).toLowerCase().includes(q); }));
                });
                document.addEventListener('DOMContentLoaded', boLoadSubs);
                </script>

                <?php elseif ($boPage === 'events'): ?>
                <!-- ===== Events Page ===== -->
                <div class="page-title">
                    <h3>Gestion des Événements</h3>
                    <p class="text-subtitle text-muted">Gérez les événements de la plateforme</p>
                </div>
                <?php
                require_once __DIR__ . '/../../controller/EventController.php';
                $boEventCtrl = new EventController();
                $boEvents = $boEventCtrl->listEvents();
                $boEventStats = $boEventCtrl->getEventStatistics();
                if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
                    echo '<div class="alert alert-success">Événement supprimé avec succès.</div>';
                }
                ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Événements (<?= count($boEvents) ?>)</h4>
                        <a href="/projectttttttt/view/backoffice/modules/ajouterEvent.php" class="btn btn-success btn-sm">+ Ajouter</a>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Titre</th><th>Date</th><th>Lieu</th><th>Capacité</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($boEvents as $ev): ?>
                                <tr>
                                    <td><?= (int)($ev['id_event']??$ev['id']??0) ?></td>
                                    <td><?= htmlspecialchars($ev['titre']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($ev['date']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($ev['lieu']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($ev['capacite']??''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="/projectttttttt/view/backoffice/modules/editEvent.php?id=<?= (int)($ev['id_event']??$ev['id']??0) ?>" class="btn btn-sm btn-primary">Modifier</a>
                                        <a href="/projectttttttt/view/backoffice/modules/handleDeleteEvent.php?id=<?= (int)($ev['id_event']??$ev['id']??0) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($boEvents)): ?><tr><td colspan="6" class="text-center text-muted">Aucun événement</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($boPage === 'inscriptions'): ?>
                <!-- ===== Inscriptions Page ===== -->
                <div class="page-title">
                    <h3>Gestion des Inscriptions</h3>
                    <p class="text-subtitle text-muted">Consultez les inscriptions aux événements</p>
                </div>
                <?php
                require_once __DIR__ . '/../../controller/InscriptionController.php';
                $boInscCtrl = new InscriptionController();
                $boInscriptions = $boInscCtrl->listInscriptions();
                ?>
                <div class="card mb-4">
                    <div class="card-header"><h4 class="card-title mb-0">Inscriptions (<?= count($boInscriptions) ?>)</h4></div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Nom</th><th>Poste</th><th>Invités</th><th>Utilisateur</th><th>Événement</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($boInscriptions as $ins): ?>
                                <tr>
                                    <td><?= (int)($ins['id_inscription']??0) ?></td>
                                    <td><?= htmlspecialchars($ins['nom']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($ins['post']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int)($ins['nber_invi']??0) ?></td>
                                    <td><?= (int)($ins['id_user']??0) ?></td>
                                    <td><?= (int)($ins['id_event']??0) ?></td>
                                    <td>
                                        <a href="/projectttttttt/view/backoffice/modules/editInscription.php?id=<?= (int)($ins['id_inscription']??0) ?>" class="btn btn-sm btn-primary">Modifier</a>
                                        <a href="/projectttttttt/view/backoffice/modules/handleDeleteInscription.php?id=<?= (int)($ins['id_inscription']??0) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($boInscriptions)): ?><tr><td colspan="7" class="text-center text-muted">Aucune inscription</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($boPage === 'mailing'): ?>
                <!-- ===== Mailing Page ===== -->
                <?php
                require_once __DIR__ . '/../../controller/MailingController.php';
                ?>
                <div class="page-title">
                    <h3>Système de Mailing</h3>
                    <p class="text-subtitle text-muted">Envoyez des emails groupés aux utilisateurs et abonnés</p>
                </div>

                <div class="row mb-4">
                  <!-- Recipients -->
                  <div class="col-md-4">
                    <div class="card h-100">
                      <div class="card-header"><h4 class="card-title mb-0">Sélection des Destinataires</h4></div>
                      <div class="card-body">
                        <div class="form-group mb-3">
                          <label>Type de destinataires</label>
                          <select id="recipientType" class="form-control">
                            <option value="">-- Sélectionnez --</option>
                            <option value="all">Tous les utilisateurs</option>
                            <option value="active">Abonnements actifs</option>
                            <option value="expiring">Abonnements expirant (7 jours)</option>
                          </select>
                        </div>
                        <button onclick="loadRecipients()" class="btn btn-primary w-100 mb-3">
                          <i data-feather="users" width="16"></i> Charger les destinataires
                        </button>
                        <div id="recipientsList" style="max-height:260px;overflow-y:auto;border:1px solid #eee;border-radius:6px;padding:8px;">
                          <p class="text-muted text-center mb-0">Aucun destinataire sélectionné</p>
                        </div>
                        <div class="mt-2 text-muted" style="font-size:13px;">
                          <strong>Total :</strong> <span id="recipientCount">0</span> destinataires
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Compose -->
                  <div class="col-md-8">
                    <div class="card h-100">
                      <div class="card-header"><h4 class="card-title mb-0">Rédiger l'Email</h4></div>
                      <div class="card-body">
                        <div class="form-group mb-3">
                          <label>Sujet</label>
                          <input type="text" id="emailSubject" class="form-control" placeholder="Sujet de l'email">
                        </div>
                        <div class="form-group mb-3">
                          <label>Message</label>
                          <textarea id="emailMessage" class="form-control" rows="8"
                            placeholder="Votre message... Utilisez {nom}, {email}, {pack}, {date_fin}"></textarea>
                        </div>
                        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
                          <strong>Variables :</strong>
                          {nom} — Téléphone client &nbsp;|&nbsp;
                          {email} — Email &nbsp;|&nbsp;
                          {pack} — Nom du pack &nbsp;|&nbsp;
                          {date_fin} — Date de fin
                        </div>
                        <div class="d-flex gap-2">
                          <button onclick="sendBulkEmail()" class="btn btn-success flex-grow-1">
                            <i data-feather="send" width="16"></i> Envoyer l'email
                          </button>
                          <button onclick="previewEmail()" class="btn btn-secondary">
                            <i data-feather="eye" width="16"></i> Aperçu
                          </button>
                        </div>
                        <div id="mailingAlert" style="display:none;margin-top:12px;padding:10px;border-radius:6px;font-size:13px;"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- History -->
                <div class="card mb-4">
                  <div class="card-header"><h4 class="card-title mb-0">Historique des Emails</h4></div>
                  <div class="card-body px-0 pb-0">
                    <div class="table-responsive">
                      <table class="table table-striped mb-0">
                        <thead><tr><th>Date</th><th>Sujet</th><th>Destinataires</th><th>Réussis</th><th>Échoués</th></tr></thead>
                        <tbody id="emailHistory"><tr><td colspan="5" class="text-center text-muted">Chargement...</td></tr></tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Preview Modal -->
                <div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
                  <div style="background:#fff;border-radius:12px;padding:24px;width:90%;max-width:600px;max-height:80vh;overflow-y:auto;">
                    <h4>Aperçu de l'Email</h4>
                    <div id="previewContent" style="border:1px solid #eee;border-radius:8px;padding:16px;margin:12px 0;"></div>
                    <button onclick="document.getElementById('previewModal').style.display='none'" class="btn btn-secondary">Fermer</button>
                  </div>
                </div>

                <script>
                var selectedRecipients = [];
                function mailingAlert(msg, ok) {
                  var el = document.getElementById('mailingAlert');
                  el.textContent = msg; el.style.display = 'block';
                  el.style.background = ok ? '#D1FAE5' : '#FEE2E2';
                  el.style.color = ok ? '#065F46' : '#991B1B';
                }
                function loadRecipients() {
                  var type = document.getElementById('recipientType').value;
                  if (!type) { mailingAlert('Sélectionnez un type de destinataires.', false); return; }
                  var actionMap = {all:'getUsers', active:'getActiveSubscribers', expiring:'getExpiringSubscribers'};
                  var fd = new FormData(); fd.append('action', actionMap[type]);
                  fetch('/projectttttttt/controller/MailingController.php', {method:'POST', body:fd})
                    .then(function(r){return r.json();})
                    .then(function(d){
                      if (d.status==='success') {
                        selectedRecipients = d.data;
                        document.getElementById('recipientCount').textContent = d.data.length;
                        var html = d.data.length === 0
                          ? '<p class="text-muted text-center mb-0">Aucun destinataire trouvé</p>'
                          : d.data.map(function(r){ return '<div style="padding:6px 0;border-bottom:1px solid #f0f0f0;font-size:13px;">'+r.email+'</div>'; }).join('');
                        document.getElementById('recipientsList').innerHTML = html;
                      } else { mailingAlert('Erreur chargement.', false); }
                    }).catch(function(){ mailingAlert('Erreur réseau.', false); });
                }
                function sendBulkEmail() {
                  var subject = document.getElementById('emailSubject').value.trim();
                  var message = document.getElementById('emailMessage').value.trim();
                  if (!selectedRecipients.length) { mailingAlert('Chargez des destinataires d\'abord.', false); return; }
                  if (!subject || !message) { mailingAlert('Remplissez le sujet et le message.', false); return; }
                  if (!confirm('Envoyer à ' + selectedRecipients.length + ' destinataires ?')) return;
                  var fd = new FormData();
                  fd.append('action','sendBulk');
                  fd.append('recipients', JSON.stringify(selectedRecipients));
                  fd.append('subject', subject);
                  fd.append('message', message);
                  fetch('/projectttttttt/controller/MailingController.php', {method:'POST', body:fd})
                    .then(function(r){return r.json();})
                    .then(function(d){
                      if (d.status==='success') {
                        mailingAlert('✅ Envoyé : '+d.data.success+' réussis, '+d.data.failed+' échoués.', true);
                        loadEmailHistory();
                      } else { mailingAlert('❌ Erreur : '+(d.message||'inconnue'), false); }
                    }).catch(function(){ mailingAlert('❌ Erreur réseau.', false); });
                }
                function previewEmail() {
                  var subject = document.getElementById('emailSubject').value;
                  var message = document.getElementById('emailMessage').value;
                  if (!subject || !message) { mailingAlert('Remplissez le sujet et le message.', false); return; }
                  document.getElementById('previewContent').innerHTML =
                    '<div style="background:linear-gradient(135deg,#00A651,#008040);color:#fff;padding:16px;border-radius:8px 8px 0 0;text-align:center;"><strong>DigiWork HUB</strong></div>' +
                    '<div style="padding:20px;background:#f9f9f9;border-radius:0 0 8px 8px;"><h3>'+subject+'</h3><p style="white-space:pre-wrap;">'+message+'</p></div>';
                  document.getElementById('previewModal').style.display = 'flex';
                }
                function loadEmailHistory() {
                  var fd = new FormData(); fd.append('action','getEmailHistory');
                  fetch('/projectttttttt/controller/MailingController.php', {method:'POST', body:fd})
                    .then(function(r){return r.json();})
                    .then(function(d){
                      var tbody = document.getElementById('emailHistory');
                      if (!d.data || !d.data.length) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun email envoyé</td></tr>';
                        return;
                      }
                      tbody.innerHTML = d.data.map(function(h){
                        return '<tr><td>'+h.sent_at+'</td><td>'+h.subject+'</td><td>'+h.recipient_count+'</td><td class="text-success">'+h.success_count+'</td><td class="text-danger">'+h.failed_count+'</td></tr>';
                      }).join('');
                    }).catch(function(){});
                }
                document.addEventListener('DOMContentLoaded', loadEmailHistory);
                </script>

                <?php elseif ($boPage === 'pack_events'): ?>
                <!-- ===== Pack-Events Relations Page ===== -->
                <?php
                require_once __DIR__ . '/../../model/PackEvent.php';
                require_once __DIR__ . '/../../model/Pack.php';
                require_once __DIR__ . '/../../model/Event.php';
                $relations = PackEvent::getAll();
                $pdo = Config::getConnexion();
                $allPacks  = $pdo->query("SELECT `id-pack` AS id_pack, `nom-pack` AS nom_pack FROM pack ORDER BY `nom-pack`")->fetchAll(PDO::FETCH_ASSOC);
                $allEvents = $pdo->query("SELECT id_event, titre FROM evente ORDER BY titre")->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="page-title">
                    <h3>Gestion des Relations Pack-Événement</h3>
                    <p class="text-subtitle text-muted">Associez des packs aux événements de la plateforme</p>
                </div>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Relations (<?= count($relations) ?>)</h4>
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addRelModal">+ Ajouter</button>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Pack</th><th>Événement</th><th>Statut</th><th>Date création</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($relations as $rel): ?>
                                <tr>
                                    <td><?= (int)($rel['id_pack_event']??0) ?></td>
                                    <td><?= htmlspecialchars($rel['nom_pack']??'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($rel['event_titre']??'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-<?= $rel['statut']==='actif'?'success':($rel['statut']==='inactif'?'danger':'warning') ?>"><?= htmlspecialchars(ucfirst($rel['statut']??''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><?= htmlspecialchars($rel['date_creation']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="/projectttttttt/controller/PackEventController.php?action=delete&id=<?= (int)($rel['id_pack_event']??0) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($relations)): ?><tr><td colspan="6" class="text-center text-muted">Aucune relation</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Add modal -->
                <div class="modal fade" id="addRelModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Ajouter une relation</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                            <div class="modal-body">
                                <form id="addRelForm">
                                    <div class="form-group mb-3">
                                        <label>Pack</label>
                                        <select class="form-control" id="relPackId" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php foreach ($allPacks as $p): ?>
                                            <option value="<?= (int)$p['id_pack'] ?>"><?= htmlspecialchars($p['nom_pack'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Événement</label>
                                        <select class="form-control" id="relEventId" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php foreach ($allEvents as $e): ?>
                                            <option value="<?= (int)$e['id_event'] ?>"><?= htmlspecialchars($e['titre'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Statut</label>
                                        <select class="form-control" id="relStatut">
                                            <option value="actif">Actif</option>
                                            <option value="inactif">Inactif</option>
                                            <option value="en_attente">En attente</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-success" id="saveRelBtn">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                document.getElementById('saveRelBtn').addEventListener('click', function() {
                    var packId  = document.getElementById('relPackId').value;
                    var eventId = document.getElementById('relEventId').value;
                    var statut  = document.getElementById('relStatut').value;
                    if (!packId || !eventId) { alert('Sélectionnez un pack et un événement.'); return; }
                    fetch('/projectttttttt/controller/PackEventController.php?action=create', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id_pack: parseInt(packId), id_event: parseInt(eventId), statut: statut})
                    }).then(r => r.json()).then(d => {
                        if (d.success) { location.reload(); } else { alert('Erreur: ' + (d.error||d.message||'inconnue')); }
                    }).catch(() => alert('Erreur réseau.'));
                });
                </script>

                <?php elseif ($boPage === 'projects'): ?>
                <!-- ===== Projects Page ===== -->
                <div class="page-title">
                    <h3>Gestion des Projets</h3>
                    <p class="text-subtitle text-muted">Consultez et gérez les projets de la plateforme</p>
                </div>
                <?php
                require_once __DIR__ . '/../../controller/projectController.php';
                $boProjCtrl = new ProjetC();
                $boProjects = $boProjCtrl->listProjets()->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Projets (<?= count($boProjects) ?>)</h4>
                        <a href="/projectttttttt/view/backoffice/modules/projectCRUD.php" class="btn btn-success btn-sm">+ Ajouter</a>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Titre</th><th>Description</th><th>Statut</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($boProjects as $proj): ?>
                                <tr>
                                    <td><?= (int)($proj['id_projet']??$proj['id']??0) ?></td>
                                    <td><?= htmlspecialchars($proj['titre']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(mb_substr($proj['description']??'',0,60), ENT_QUOTES, 'UTF-8') ?>...</td>
                                    <td><?= htmlspecialchars($proj['statut']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="/projectttttttt/view/backoffice/modules/projectCRUD.php?id=<?= (int)($proj['id_projet']??$proj['id']??0) ?>" class="btn btn-sm btn-primary">Modifier</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($boProjects)): ?><tr><td colspan="5" class="text-center text-muted">Aucun projet</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($boPage === 'mes_inscriptions'): ?>
                <!-- ===== Mes Inscriptions (candidat) ===== -->
                <div class="page-title">
                    <h3>Mes Inscriptions</h3>
                    <p class="text-subtitle text-muted">Vos inscriptions aux événements</p>
                </div>
                <?php
                require_once __DIR__ . '/../../controller/InscriptionController.php';
                $userId = (int)$_SESSION['user_id'];
                $myInscriptions = [];
                try {
                    $inscCtrl = new InscriptionController();
                    $allInsc = $inscCtrl->listInscriptions();
                    $myInscriptions = array_filter($allInsc, function($ins) use ($userId) {
                        return (int)($ins['id_user'] ?? 0) === $userId;
                    });
                    $myInscriptions = array_values($myInscriptions);
                } catch (Throwable $e) { $myInscriptions = []; }
                ?>
                <div class="card mb-4">
                    <div class="card-header"><h4 class="card-title mb-0">Mes Inscriptions (<?= count($myInscriptions) ?>)</h4></div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Nom</th><th>Poste</th><th>Invités</th><th>Événement</th></tr></thead>
                                <tbody>
                                <?php foreach ($myInscriptions as $ins): ?>
                                <tr>
                                    <td><?= (int)($ins['id_inscription']??0) ?></td>
                                    <td><?= htmlspecialchars($ins['nom']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($ins['post']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int)($ins['nber_invi']??0) ?></td>
                                    <td><?= (int)($ins['id_event']??0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($myInscriptions)): ?><tr><td colspan="5" class="text-center text-muted">Aucune inscription</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($boPage === 'mes_projets'): ?>
                <!-- ===== Mes Projets (candidat/entreprise/sponsor) ===== -->
                <div class="page-title">
                    <h3>Mes Projets</h3>
                    <p class="text-subtitle text-muted">Vos projets sur la plateforme</p>
                </div>
                <?php
                require_once __DIR__ . '/../../controller/projectController.php';
                $userId = (int)$_SESSION['user_id'];
                $myProjects = [];
                try {
                    $projCtrl = new ProjetC();
                    $allProj = $projCtrl->listProjets()->fetchAll(PDO::FETCH_ASSOC);
                    $myProjects = array_filter($allProj, function($p) use ($userId) {
                        return (int)($p['id-user'] ?? $p['id_user'] ?? 0) === $userId;
                    });
                    $myProjects = array_values($myProjects);
                } catch (Throwable $e) { $myProjects = []; }
                ?>
                <div class="card mb-4">
                    <div class="card-header"><h4 class="card-title mb-0">Mes Projets (<?= count($myProjects) ?>)</h4></div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Titre</th><th>Description</th><th>Statut</th></tr></thead>
                                <tbody>
                                <?php foreach ($myProjects as $proj): ?>
                                <tr>
                                    <td><?= (int)($proj['id_projet']??$proj['id']??0) ?></td>
                                    <td><?= htmlspecialchars($proj['titre']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(mb_substr($proj['description']??'',0,60), ENT_QUOTES, 'UTF-8') ?>...</td>
                                    <td><?= htmlspecialchars($proj['statut']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($myProjects)): ?><tr><td colspan="4" class="text-center text-muted">Aucun projet</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($boPage === 'mon_abonnement'): ?>
                <!-- ===== Mon Abonnement (non-admin) ===== -->
                <div class="page-title">
                    <h3>Mon Abonnement</h3>
                    <p class="text-subtitle text-muted">Votre abonnement actif sur la plateforme</p>
                </div>
                <?php
                require_once __DIR__ . '/../../config/config.php';
                $userId = (int)$_SESSION['user_id'];
                $myAbonnements = [];
                try {
                    $pdo = Config::getConnexion();
                    $stmt = $pdo->prepare("
                        SELECT a.*, p.`nom-pack` AS nom_pack
                        FROM abonnement a
                        LEFT JOIN pack p ON p.`id-pack` = a.`id-pack`
                        WHERE a.`id-user` = ?
                        ORDER BY a.id_abonnement DESC
                    ");
                    $stmt->execute([$userId]);
                    $myAbonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Throwable $e) { $myAbonnements = []; }
                ?>
                <div class="card mb-4">
                    <div class="card-header"><h4 class="card-title mb-0">Mon Abonnement (<?= count($myAbonnements) ?>)</h4></div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>ID</th><th>Pack</th><th>Date début</th><th>Date fin</th><th>Statut</th></tr></thead>
                                <tbody>
                                <?php foreach ($myAbonnements as $ab): ?>
                                <tr>
                                    <td><?= (int)($ab['id_abonnement']??0) ?></td>
                                    <td><?= htmlspecialchars($ab['nom_pack']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($ab['date_debut']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($ab['date_fin']??'', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge <?= ($ab['statut']??'')==='actif'?'bg-success':'bg-secondary' ?>"><?= htmlspecialchars($ab['statut']??'', ENT_QUOTES, 'UTF-8') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($myAbonnements)): ?><tr><td colspan="5" class="text-center text-muted">Aucun abonnement</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- ===== Dashboard (default) ===== -->
                <?php if ($userRole !== 'admin'): ?>
                <!-- Non-admin personalized dashboard -->
                <div class="page-title">
                    <h3>Mon Espace</h3>
                    <p class="text-subtitle text-muted">Bienvenue, <?= htmlspecialchars($loggedInUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?> — Rôle : <?= htmlspecialchars(ucfirst($userRole), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <!-- Quick links -->
                <div class="row mb-4">
                    <?php if ($userRole === 'condidat'): ?>
                    <div class="col-md-4 mb-3">
                        <a href="index.php?page=mes_inscriptions" class="card text-decoration-none" style="display:block;border-left:4px solid #1b4379;">
                            <div class="card-body">
                                <h5 style="color:#1b4379;"><i data-feather="calendar" width="20"></i> Mes Inscriptions</h5>
                                <p class="text-muted mb-0" style="font-size:13px;">Voir vos inscriptions aux événements</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4 mb-3">
                        <a href="index.php?page=mes_projets" class="card text-decoration-none" style="display:block;border-left:4px solid #2270c1;">
                            <div class="card-body">
                                <h5 style="color:#2270c1;"><i data-feather="folder" width="20"></i> Mes Projets</h5>
                                <p class="text-muted mb-0" style="font-size:13px;">Gérer vos projets</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="index.php?page=mon_abonnement" class="card text-decoration-none" style="display:block;border-left:4px solid #69b83b;">
                            <div class="card-body">
                                <h5 style="color:#69b83b;"><i data-feather="star" width="20"></i> Mon Abonnement</h5>
                                <p class="text-muted mb-0" style="font-size:13px;">Voir votre abonnement actif</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent activity summary -->
                <div class="row">
                    <?php
                    // Recent inscriptions (candidat only)
                    if ($userRole === 'condidat') {
                        require_once __DIR__ . '/../../controller/InscriptionController.php';
                        $userId = (int)$_SESSION['user_id'];
                        $recentInsc = [];
                        try {
                            $inscCtrl2 = new InscriptionController();
                            $allInsc2 = $inscCtrl2->listInscriptions();
                            $recentInsc = array_slice(array_values(array_filter($allInsc2, function($i) use ($userId) {
                                return (int)($i['id_user']??0) === $userId;
                            })), 0, 5);
                        } catch (Throwable $e) {}
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title mb-0">Dernières inscriptions</h4></div>
                            <div class="card-body px-0 pb-0">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Nom</th><th>Événement</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($recentInsc as $ri): ?>
                                    <tr><td><?= htmlspecialchars($ri['nom']??'', ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)($ri['id_event']??0) ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentInsc)): ?><tr><td colspan="2" class="text-muted text-center">Aucune inscription</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <!-- Recent projects -->
                    <?php
                    require_once __DIR__ . '/../../controller/projectController.php';
                    $userId2 = (int)$_SESSION['user_id'];
                    $recentProj = [];
                    try {
                        $projCtrl2 = new ProjetC();
                        $allProj2 = $projCtrl2->listProjets()->fetchAll(PDO::FETCH_ASSOC);
                        $recentProj = array_slice(array_values(array_filter($allProj2, function($p) use ($userId2) {
                            return (int)($p['id-user'] ?? $p['id_user'] ?? 0) === $userId2;
                        })), 0, 5);
                    } catch (Throwable $e) {}
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header"><h4 class="card-title mb-0">Derniers projets</h4></div>
                            <div class="card-body px-0 pb-0">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Titre</th><th>Statut</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($recentProj as $rp): ?>
                                    <tr><td><?= htmlspecialchars($rp['titre']??'', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($rp['statut']??'', ENT_QUOTES, 'UTF-8') ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentProj)): ?><tr><td colspan="2" class="text-muted text-center">Aucun projet</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Admin dashboard (unchanged) -->
                <div class="page-title">
                    <h3>Tableau de bord administrateur</h3>
                    <p class="text-subtitle text-muted">Bienvenue sur DigiWork Hub - Plateforme intelligente d'accompagnement des entrepreneurs digitaux</p>
                </div>
                    <p class="text-subtitle text-muted">Bienvenue sur DigiWork Hub - Plateforme intelligente d'accompagnement des entrepreneurs digitaux</p>
                </div>

                <?php
                    $onlineUsers = [];
                    try {
                        $onlineUsers = (new UserController())->getOnlineUsers();
                    } catch (Throwable $e) {
                        $onlineUsers = [];
                    }
                ?>

                <!-- Distribution des rôles - Pie Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Distribution des Rôles</h4>
                        <p style="font-size: 13px; color: #999; margin-top: 5px;">Utilisateurs du site web</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div id="chartPieDistribution" style="height: 300px;"></div>
                            </div>
                            <div class="col-md-4">
                                <div id="chartLegend" style="padding-top: 20px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contrôles de recherche et tri des utilisateurs -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rechercher par ID ou Email</label>
                                    <input type="text" id="searchInputDashboard" class="form-control" placeholder="Entrez ID ou Email...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trier par</label>
                                    <select id="sortSelectDashboard" class="form-control">
                                        <option value="">-- Sélectionnez un tri --</option>
                                        <option value="id-asc">ID (1-9)</option>
                                        <option value="id-desc">ID (9-1)</option>
                                        <option value="email-asc">Email (A-Z)</option>
                                        <option value="email-desc">Email (Z-A)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table des utilisateurs -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Tous les utilisateurs <span id="dashboardUserCount" style="font-weight: normal; color: #666;">(0)</span></h4>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0" id="dashboardUsersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Rôle</th>
                                    </tr>
                                </thead>
                                <tbody id="dashboardUsersTableBody">
                                    <?php foreach ($allUsers as $user): ?>
                                        <tr class="user-row-dashboard" data-id="<?= (int) $user['id_user'] ?>" data-email="<?= htmlspecialchars(strtolower($user['email']), ENT_QUOTES, 'UTF-8') ?>" data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>">
                                            <td class="user-id"><?= (int) $user['id_user'] ?></td>
                                            <td class="user-email"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) $user['tel'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge" style="background: <?php 
                                                    $role = strtolower(trim($user['role']));
                                                    if ($role === 'condidat') {
                                                        $role = 'candidat';
                                                    }
                                                    echo ($role === 'candidat') ? '#1e40af' : (($role === 'entreprise') ? '#f5576c' : (($role === 'sponsor') ? '#00f2fe' : '#fee140')); 
                                                ?>; color: white;">
                                                    <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($allUsers)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Aucun utilisateur</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Utilisateurs connectes</h4>
                        <span class="badge bg-success" id="onlineUsersCount"><?= count($onlineUsers) ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($onlineUsers)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Derniere activite</th>
                                        </tr>
                                    </thead>
                                    <tbody id="onlineUsersBody">
                                        <?php foreach ($onlineUsers as $onlineUser): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($onlineUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($onlineUser['role'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string) ($onlineUser['last_activity'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Aucun utilisateur n'est connecte pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <section class="section">
                    <!-- Statistiques principales -->
                    <div class="row mb-2">
                        <div class="col-12 col-md-3">
                            <div class="card card-statistic">
                                <div class="card-body p-0">
                                    <div class="d-flex flex-column">
                                        <div class='px-3 py-3 d-flex justify-content-between'>
                                            <h3 class='card-title'>Offres publiées</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p class="text-primary">156</p>
                                            </div>
                                        </div>
                                        <div class="chart-wrapper">
                                            <canvas id="canvas1" style="height:100px !important"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card card-statistic">
                                <div class="card-body p-0">
                                    <div class="d-flex flex-column">
                                        <div class='px-3 py-3 d-flex justify-content-between'>
                                            <h3 class='card-title'>Utilisateurs actifs</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p class="text-success">1 284</p>
                                            </div>
                                        </div>
                                        <div class="chart-wrapper">
                                            <canvas id="canvas2" style="height:100px !important"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card card-statistic">
                                <div class="card-body p-0">
                                    <div class="d-flex flex-column">
                                        <div class='px-3 py-3 d-flex justify-content-between'>
                                            <h3 class='card-title'>Projets en cours</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p class="text-warning">342</p>
                                            </div>
                                        </div>
                                        <div class="chart-wrapper">
                                            <canvas id="canvas3" style="height:100px !important"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card card-statistic">
                                <div class="card-body p-0">
                                    <div class="d-flex flex-column">
                                        <div class='px-3 py-3 d-flex justify-content-between'>
                                            <h3 class='card-title'>Réclamations</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p class="text-danger">12</p>
                                            </div>
                                        </div>
                                        <div class="chart-wrapper">
                                            <canvas id="canvas4" style="height:100px !important"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Graphique des revenus et activités -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class='card-heading p-1 pl-3'>Activité de la plateforme</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 col-12">
                                            <div class="pl-3">
                                                <h1 class='mt-5'>+23%</h1>
                                                <p class='text-xs'><span class="text-green"><i data-feather="bar-chart" width="15"></i> +23%</span> que le mois dernier</p>
                                                <div class="legends">
                                                    <div class="legend d-flex flex-row align-items-center">
                                                        <div class='w-3 h-3 rounded-full bg-info mr-2'></div>
                                                        <span class='text-xs'>Mois dernier</span>
                                                    </div>
                                                    <div class="legend d-flex flex-row align-items-center">
                                                        <div class='w-3 h-3 rounded-full bg-blue mr-2'></div>
                                                        <span class='text-xs'>Mois actuel</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-12">
                                            <canvas id="bar"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title">Dernières réclamations</h4>
                                    <div class="d-flex">
                                        <i data-feather="download"></i>
                                    </div>
                                </div>
                                <div class="card-body px-0 pb-0">
                                    <div class="table-responsive">
                                        <table class='table mb-0' id="table1">
                                            <thead>
                                                <tr>
                                                    <th>Utilisateur</th>
                                                    <th>Titre</th>
                                                    <th>Date</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Marie Laurent</td>
                                                    <td>Problème de paiement</td>
                                                    <td>15/03/2024</td>
                                                    <td><span class="badge bg-warning">En attente</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Thomas Dubois</td>
                                                    <td>Offre non conforme</td>
                                                    <td>14/03/2024</td>
                                                    <td><span class="badge bg-success">En cours</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Sophie Martin</td>
                                                    <td>Litige avec un client</td>
                                                    <td>12/03/2024</td>
                                                    <td><span class="badge bg-danger">Urgent</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Lucas Bernard</td>
                                                    <td>Compte bloqué</td>
                                                    <td>10/03/2024</td>
                                                    <td><span class="badge bg-success">Résolu</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Camille Petit</td>
                                                    <td>Problème technique</td>
                                                    <td>09/03/2024</td>
                                                    <td><span class="badge bg-success">Résolu</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Performances IA -->
                            <div class="card">
                                <div class="card-header">
                                    <h4>Matching IA</h4>
                                </div>
                                <div class="card-body">
                                    <div id="radialBars"></div>
                                    <div class="text-center mb-5">
                                        <h6>Taux de correspondance</h6>
                                        <h1 class='text-green'>+78%</h1>
                                        <p class="text-xs">Matching réussi entre offres et profils</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progression des modules -->
                            <div class="card widget-todo">
                                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                    <h4 class="card-title d-flex">
                                        <i class='bx bx-check font-medium-5 pl-25 pr-75'></i>Activité des modules
                                    </h4>
                                </div>
                                <div class="card-body px-0 py-1">
                                    <table class='table table-borderless'>
                                        <tr>
                                            <td class='col-3'>Offres</td>
                                            <td class='col-6'>
                                                <div class="progress progress-info">
                                                    <div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class='col-3 text-center'>156</td>
                                        </tr>
                                        <tr>
                                            <td class='col-3'>Utilisateurs</td>
                                            <td class='col-6'>
                                                <div class="progress progress-success">
                                                    <div class="progress-bar" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class='col-3 text-center'>1 284</td>
                                        </tr>
                                        <tr>
                                            <td class='col-3'>Projets</td>
                                            <td class='col-6'>
                                                <div class="progress progress-primary">
                                                    <div class="progress-bar" role="progressbar" style="width: 64%" aria-valuenow="64" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class='col-3 text-center'>342</td>
                                        </tr>
                                        <tr>
                                            <td class='col-3'>Forums</td>
                                            <td class='col-6'>
                                                <div class="progress progress-warning">
                                                    <div class="progress-bar" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class='col-3 text-center'>89 sujets</td>
                                        </tr>
                                        <tr>
                                            <td class='col-3'>Évaluations</td>
                                            <td class='col-6'>
                                                <div class="progress progress-secondary">
                                                    <div class="progress-bar" role="progressbar" style="width: 91%" aria-valuenow="91" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class='col-3 text-center'>4.8/5</td>
                                        </tr>
                                        <tr>
                                            <td class='col-3'>Réclamations</td>
                                            <td class='col-6'>
                                                <div class="progress progress-danger">
                                                    <div class="progress-bar" role="progressbar" style="width: 28%" aria-valuenow="28" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class='col-3 text-center'>12</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Impact durable -->
                            <div class="card">
                                <div class="card-header">
                                    <h4>Impact durable</h4>
                                </div>
                                <div class="card-body text-center">
                                    <i data-feather="leaf" width="48" height="48" class="text-success mb-3"></i>
                                    <h3 class="text-success">-156 kg</h3>
                                    <p>d'émissions CO₂ évitées<br>grâce au télétravail</p>
                                    <div class="progress mt-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 67%">Objectif 2024 : 67%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php endif; // end admin block ?>
            <?php endif; // end $boPage switch ?>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-left">
                        <p>2024 &copy; DigiWork Hub - Plateforme intelligente d'accompagnement des entrepreneurs digitaux</p>
                    </div>
                    <div class="float-right">
                        <p>Propulsé par l'IA et le développement durable</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <script src="assets/js/feather-icons/feather.min.js"></script>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/vendors/chartjs/Chart.min.js"></script>
    <script src="assets/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Calculate statistics from database
        <?php
        $stats = [
            'candidat' => 0,
            'entreprise' => 0,
            'sponsor' => 0,
            'admin' => 0,
            'total' => count($allUsers)
        ];
        
        foreach ($allUsers as $user) {
            $role = strtolower(trim($user['role']));
            if ($role === 'condidat') {
                $role = 'candidat';
            }
            if (isset($stats[$role])) {
                $stats[$role]++;
            }
        }
        ?>
        window.initialStats = <?= json_encode($stats) ?>;
    </script>
    <script src="assets/js/backoffice-dashboard.js"></script>
    <script>
        (function () {
            var body = document.getElementById('onlineUsersBody');
            var count = document.getElementById('onlineUsersCount');

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function render(users) {
                if (!body || !count) {
                    return;
                }

                count.textContent = String(users.length);

                if (!users.length) {
                    body.innerHTML = '<tr><td colspan="3" class="text-muted">Aucun utilisateur n\'est connecte pour le moment.</td></tr>';
                    return;
                }

                body.innerHTML = users.map(function (user) {
                    return '<tr>' +
                        '<td>' + escapeHtml(user.email || '') + '</td>' +
                        '<td>' + escapeHtml(user.role || '') + '</td>' +
                        '<td>' + escapeHtml(user.last_activity || '') + '</td>' +
                        '</tr>';
                }).join('');
            }

            function refreshOnlineUsers() {
                fetch('index.php?action=online-users', { credentials: 'same-origin' })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data && data.success) {
                            render(data.users || []);
                        }
                    })
                    .catch(function () {});
            }

            refreshOnlineUsers();
            window.setInterval(refreshOnlineUsers, 10000);
        })();
    </script>
</body>
</html>
