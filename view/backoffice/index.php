<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DigiWork Hub</title>

    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/vendors/chartjs/Chart.min.css">
    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
</head>
<body>
    <div id="app">
        <div id="sidebar" class='active'>
            <div class="sidebar-wrapper active">
<div class="sidebar-header">
    <img src="assets/images/logo.png" style="width:230px;">
</div>

                <div class="sidebar-menu">
                    <ul class="menu">

                        <li class='sidebar-title'>Menu Principal</li>

                        <li class="sidebar-item active">
                            <a href="index.php" class='sidebar-link'>
                                <i data-feather="home" width="20"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class='sidebar-title'>Gestion</li>

                        <!-- Gestion des offres -->
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i data-feather="tag" width="20"></i>
                                <span>Gestion des offres</span>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a href="listOffres.php">Toutes les offres</a>
                                </li>
                                <li>
                                    <a href="addOffre.php">Ajouter une offre</a>
                                </li>
                                <li>
                                    <a href="offresExpirees.php">Offres expirées</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Gestion des utilisateurs -->
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i data-feather="users" width="20"></i>
                                <span>Gestion des utilisateurs</span>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a href="listUsers.php">Tous les utilisateurs</a>
                                </li>
                                <li>
                                    <a href="addUser.php">Ajouter un utilisateur</a>
                                </li>
                                <li>
                                    <a href="rolesPermissions.php">Rôles &amp; permissions</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Gestion des forums -->
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i data-feather="message-square" width="20"></i>
                                <span>Gestion des forums</span>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a href="listForums.php">Tous les forums</a>
                                </li>
                                <li>
                                    <a href="categoriesForums.php">Catégories</a>
                                </li>
                                <li>
                                    <a href="signalements.php">Signalements</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Gestion des projets -->
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i data-feather="folder" width="20"></i>
                                <span>Gestion des projets</span>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a href="listProjets.php">Tous les projets</a>
                                </li>
                                <li>
                                    <a href="addProjet.php">Ajouter un projet</a>
                                </li>
                                <li>
                                    <a href="projetsArchives.php">Archivés</a>
                                </li>
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
                <button class="btn navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav d-flex align-items-center navbar-light ml-auto">
                        <li class="dropdown nav-icon">
                            <a href="#" data-toggle="dropdown"
                                class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <div class="d-lg-inline-block">
                                    <i data-feather="bell"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-large">
                                <h6 class='py-2 px-4'>Notifications</h6>
                                <ul class="list-group rounded-none">
                                    <li class="list-group-item border-0 align-items-start">
                                        <div class="avatar bg-success mr-3">
                                            <span class="avatar-content"><i data-feather="shopping-cart"></i></span>
                                        </div>
                                        <div>
                                            <h6 class='text-bold'>Nouvelle offre</h6>
                                            <p class='text-xs'>Une nouvelle offre a été publiée</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="dropdown nav-icon mr-2">
                            <a href="#" data-toggle="dropdown"
                                class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <div class="d-lg-inline-block">
                                    <i data-feather="mail"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#"><i data-feather="user"></i> Compte</a>
                                <a class="dropdown-item active" href="#"><i data-feather="mail"></i> Messages</a>
                                <a class="dropdown-item" href="#"><i data-feather="settings"></i> Paramètres</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#"><i data-feather="log-out"></i> Déconnexion</a>
                            </div>
                        </li>
                        <li class="dropdown">
                            <a href="#" data-toggle="dropdown"
                                class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <div class="avatar mr-1">
                                    <img src="assets/images/avatar/avatar-s-1.png" alt="" srcset="">
                                </div>
                                <div class="d-none d-md-block d-lg-inline-block">Admin</div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#"><i data-feather="user"></i> Compte</a>
                                <a class="dropdown-item active" href="#"><i data-feather="mail"></i> Messages</a>
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
                    <h3>Dashboard</h3>
                    <p class="text-subtitle text-muted">Bienvenue sur le panneau d'administration DigiWork Hub</p>
                </div>

                <section class="section">
                    <div class="row mb-2">
                        <div class="col-12 col-md-3">
                            <div class="card card-statistic">
                                <div class="card-body p-0">
                                    <div class="d-flex flex-column">
                                        <div class='px-3 py-3 d-flex justify-content-between'>
                                            <h3 class='card-title'>OFFRES</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p>124</p>
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
                                            <h3 class='card-title'>UTILISATEURS</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p>532</p>
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
                                            <h3 class='card-title'>FORUMS</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p>48</p>
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
                                            <h3 class='card-title'>PROJETS</h3>
                                            <div class="card-right d-flex align-items-center">
                                                <p>87</p>
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
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-left">
                        <p>2024 &copy; DigiWork Hub</p>
                    </div>
                    <div class="float-right">
                        <p>Panneau d'administration</p>
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
</body>
</html>