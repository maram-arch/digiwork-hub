<?php
$activePage = $activePage ?? '';
$loggedInUser = $loggedInUser ?? [];
?>
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <img src="assets/images/logo.png" alt="DigiWork Hub Logo" style="height: 90px; width: auto; margin-bottom: 10px;" srcset="">
            <h4 class="mt-2" style="color:#00A651;">DigiWork Hub</h4>
            <div class="user-info">
                <span><?= htmlspecialchars($loggedInUser['email'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="admin-badge">ADMIN</span>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu Principal</li>
                <li class="sidebar-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <a href="index.php" class="sidebar-link">
                        <i data-feather="home" width="20"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>

                <li class="sidebar-title">Gestion</li>

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="briefcase" width="20"></i>
                        <span>Gestion des offres</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="#">Toutes les offres</a></li>
                        <li><a href="#">Ajouter une offre</a></li>
                        <li><a href="#">Categories</a></li>
                    </ul>
                </li>

                <li class="sidebar-item <?= $activePage === 'users' ? 'active' : '' ?> has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="users" width="20"></i>
                        <span>Gestion des utilisateurs</span>
                    </a>
                    <ul class="submenu <?= $activePage === 'users' ? 'active' : '' ?>">
                        <li><a href="users.php">Utilisateurs (CRUD)</a></li>
                        <li><a href="#">Statistique</a></li>
                    </ul>
                </li>

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="folder" width="20"></i>
                        <span>Gestion des projets</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="#">Tous les projets</a></li>
                        <li><a href="#">Projets en cours</a></li>
                        <li><a href="#">Projets termines</a></li>
                        <li><a href="#">Livrables</a></li>
                    </ul>
                </li>

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="star" width="20"></i>
                        <span>Packs</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="#">Tous les packs</a></li>
                    </ul>
                </li>

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="message-circle" width="20"></i>
                        <span>Forums</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="#">Tous les sujets</a></li>
                        <li><a href="#">Messages signales</a></li>
                        <li><a href="#">Categories</a></li>
                    </ul>
                </li>

                <li class="sidebar-title">Actions</li>

                <li class="sidebar-item">
                    <a href="?logout=1" class="sidebar-link">
                        <i data-feather="log-out" width="20"></i>
                        <span>Deconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>
