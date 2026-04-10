<?php
require_once __DIR__ . '/../../controller/OffreController.php';
require_once __DIR__ . '/../../model/Offre.php';
 
$message = "";
$messageType = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        !empty($_POST["titre"]) &&
        !empty($_POST["description"]) &&
        !empty($_POST["competences"]) &&
        !empty($_POST["date_limite"]) &&
        !empty($_POST["adresse"]) &&
        !empty($_POST["type"]) &&
        !empty($_POST["id_entreprise"])
    ) {
        $offre = new Offre(
            $_POST["titre"],
            $_POST["description"],
            $_POST["competences"],
            $_POST["date_limite"],
            $_POST["adresse"],
            $_POST["type"],
            $_POST["id_entreprise"]
        );
 
        $controller = new OffreController();
        $controller->addOffre($offre);
 
        $message = "Offre ajoutée avec succès !";
        $messageType = "success";
        $_POST = [];
    } else {
        $message = "Tous les champs sont obligatoires.";
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une offre - DigiWork Hub</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>
<body>
<div id="app">
    <div id="sidebar" class='active'>
        <div class="sidebar-wrapper active">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" style="width:250px;">
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
 
                    <li class='sidebar-title'>Menu Principal</li>
 
                    <li class="sidebar-item">
                        <a href="index.php" class='sidebar-link'>
                            <i data-feather="home" width="20"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
 
                    <li class='sidebar-title'>Gestion</li>
 
                    <li class="sidebar-item active has-sub">
                        <a href="#" class='sidebar-link'>
                            <i data-feather="tag" width="20"></i>
                            <span>Gestion des offres</span>
                        </a>
                        <ul class="submenu open">
                            <li><a href="listOffres.php">Toutes les offres</a></li>
                            <li><a href="addOffre.php" class="active">Ajouter une offre</a></li>
                            <li><a href="offresExpirees.php">Offres expirées</a></li>
                        </ul>
                    </li>
 
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i data-feather="users" width="20"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="listUsers.php">Tous les utilisateurs</a></li>
                            <li><a href="addUser.php">Ajouter un utilisateur</a></li>
                            <li><a href="rolesPermissions.php">Rôles &amp; permissions</a></li>
                        </ul>
                    </li>
 
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i data-feather="message-square" width="20"></i>
                            <span>Gestion des forums</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="listForums.php">Tous les forums</a></li>
                            <li><a href="categoriesForums.php">Catégories</a></li>
                            <li><a href="signalements.php">Signalements</a></li>
                        </ul>
                    </li>
 
                    <li class="sidebar-item has-sub">
                        <a href="#" class='sidebar-link'>
                            <i data-feather="folder" width="20"></i>
                            <span>Gestion des projets</span>
                        </a>
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
 
    <div id="main">
        <nav class="navbar navbar-header navbar-expand navbar-light">
            <a class="sidebar-toggler" href="#"><span class="navbar-toggler-icon"></span></a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                    <li class="dropdown nav-icon">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="d-lg-inline-block"><i data-feather="bell"></i></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-large">
                            <h6 class='py-2 px-4'>Notifications</h6>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="avatar mr-1">
                                <img src="assets/images/avatar/avatar-s-1.png" alt="" srcset="">
                            </div>
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
                        <h3>Ajouter une offre</h3>
                        <p class="text-subtitle text-muted">Remplissez le formulaire pour publier une nouvelle offre</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class='breadcrumb-header float-right float-lg-right'>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="listOffres.php">Offres</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Ajouter</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
 
            <section class="section">
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Nouvelle offre</h4>
                            </div>
                            <div class="card-body">
 
                                <?php if ($message != ""): ?>
                                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                                        <?php if ($messageType == "success"): ?>
                                            <i data-feather="check-circle" width="16"></i>
                                        <?php else: ?>
                                            <i data-feather="alert-circle" width="16"></i>
                                        <?php endif; ?>
                                        <?= $message ?>
                                        <?php if ($messageType == "success"): ?>
                                            — <a href="listOffres.php" class="alert-link">Voir la liste</a>
                                        <?php endif; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>
 
                                <form method="POST" action="addOffre.php">
                                    <div class="form-group">
                                        <label for="titre">Titre <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="titre"
                                            name="titre"
                                            placeholder="Ex : Développeur Full Stack"
                                            value="<?= isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : '' ?>"
                                            required>
                                    </div>
 
                                    <div class="form-group">
                                        <label for="description">Description <span class="text-danger">*</span></label>
                                        <textarea
                                            class="form-control"
                                            id="description"
                                            name="description"
                                            rows="4"
                                            placeholder="Décrivez le poste, les missions..."
                                            required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                                    </div>
 
                                    <div class="form-group">
                                        <label for="competences">Compétences requises <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="competences"
                                            name="competences"
                                            placeholder="Ex : PHP, MySQL, JavaScript"
                                            value="<?= isset($_POST['competences']) ? htmlspecialchars($_POST['competences']) : '' ?>"
                                            required>
                                    </div>
 
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_limite">Date limite <span class="text-danger">*</span></label>
                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    id="date_limite"
                                                    name="date_limite"
                                                    value="<?= isset($_POST['date_limite']) ? htmlspecialchars($_POST['date_limite']) : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type">Type de contrat <span class="text-danger">*</span></label>
                                                <select class="form-control" id="type" name="type" required>
                                                    <option value="">-- Choisir --</option>
                                                    <option value="CDI" <?= (isset($_POST['type']) && $_POST['type'] == 'CDI') ? 'selected' : '' ?>>CDI</option>
                                                    <option value="CDD" <?= (isset($_POST['type']) && $_POST['type'] == 'CDD') ? 'selected' : '' ?>>CDD</option>
                                                    <option value="Stage" <?= (isset($_POST['type']) && $_POST['type'] == 'Stage') ? 'selected' : '' ?>>Stage</option>
                                                    <option value="Freelance" <?= (isset($_POST['type']) && $_POST['type'] == 'Freelance') ? 'selected' : '' ?>>Freelance</option>
                                                    <option value="Alternance" <?= (isset($_POST['type']) && $_POST['type'] == 'Alternance') ? 'selected' : '' ?>>Alternance</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
 
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="adresse">Adresse <span class="text-danger">*</span></label>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="adresse"
                                                    name="adresse"
                                                    placeholder="Ex : Tunis, Tunisie"
                                                    value="<?= isset($_POST['adresse']) ? htmlspecialchars($_POST['adresse']) : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="id_entreprise">ID Entreprise <span class="text-danger">*</span></label>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    id="id_entreprise"
                                                    name="id_entreprise"
                                                    placeholder="Ex : 1"
                                                    value="<?= isset($_POST['id_entreprise']) ? htmlspecialchars($_POST['id_entreprise']) : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
 
                                    <div class="d-flex justify-content-between mt-3">
                                        <a href="listOffres.php" class="btn btn-secondary">
                                            <i data-feather="arrow-left" width="16"></i> Retour
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i data-feather="save" width="16"></i> Enregistrer l'offre
                                        </button>
                                    </div>
                                </form>
 
                            </div>
                        </div>
                    </div>
 
                    <!-- Carte info à droite -->
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i data-feather="info" width="16"></i> Aide
                                </h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted" style="font-size:13px;">
                                    Remplissez tous les champs marqués <span class="text-danger">*</span> pour publier l'offre.
                                </p>
                                <hr>
                                <ul class="text-muted" style="font-size:13px; padding-left:16px;">
                                    <li class="mb-1">Le <strong>titre</strong> doit être clair et concis.</li>
                                    <li class="mb-1">Les <strong>compétences</strong> peuvent être séparées par des virgules.</li>
                                    <li class="mb-1">La <strong>date limite</strong> ne peut pas être dans le passé.</li>
                                    <li class="mb-1">L'<strong>ID entreprise</strong> doit correspondre à une entreprise existante.</li>
                                </ul>
                                <hr>
                                <a href="listOffres.php" class="btn btn-light btn-block">
                                    <i data-feather="list" width="14"></i> Voir toutes les offres
                                </a>
                            </div>
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
 
<script src="assets/js/feather-icons/feather.min.js"></script>
<script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>