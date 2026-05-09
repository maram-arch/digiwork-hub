<?php
$activePage  = $activePage  ?? '';
$loggedInUser = $loggedInUser ?? [];
$userRole    = $userRole    ?? ($loggedInUser['role'] ?? 'condidat');
?>
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <img src="assets/images/logo.png" alt="DigiWork Hub Logo" style="height: 90px; width: auto; margin-bottom: 10px;" srcset="">
            <h4 class="mt-2" style="color:#1b4379;">DigiWork Hub</h4>
            <div class="user-info">
                <span><?= htmlspecialchars($loggedInUser['email'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="admin-badge" style="background:<?= $userRole === 'admin' ? '#1b4379' : '#69b83b' ?>;">
                    <?= htmlspecialchars(strtoupper($userRole), ENT_QUOTES, 'UTF-8') ?>
                </span>
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

                <?php if ($userRole === 'admin'): ?>
                <!-- ── Admin menu ── -->
                <li class="sidebar-title">Gestion</li>

                <li class="sidebar-item <?= $activePage === 'offres' ? 'active' : '' ?> has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="briefcase" width="20"></i>
                        <span>Gestion des offres</span>
                    </a>
                    <ul class="submenu <?= $activePage === 'offres' ? 'active' : '' ?>">
                        <li><a href="listOffres.php">Offres</a></li>
                        <li><a href="listCandidatures.php">Candidatures</a></li>
                    </ul>
                </li>

                <li class="sidebar-item <?= $activePage === 'users' ? 'active' : '' ?> has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="users" width="20"></i>
                        <span>Gestion des utilisateurs</span>
                    </a>
                    <ul class="submenu <?= $activePage === 'users' ? 'active' : '' ?>">
                        <li><a href="/projectttttttt/view/backoffice/users.php">Utilisateurs (CRUD)</a></li>
                    </ul>
                </li>

                <li class="sidebar-item <?= $activePage === 'projects' ? 'active' : '' ?> has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="folder" width="20"></i>
                        <span>Gestion des projets</span>
                    </a>
                    <ul class="submenu <?= $activePage === 'projects' ? 'active' : '' ?>">
                        <li><a href="index.php?page=projects">Tous les projets</a></li>
                        <li><a href="/projectttttttt/view/backoffice/modules/projectCRUD.php">Ajouter un projet</a></li>
                    </ul>
                </li>

                <li class="sidebar-item <?= in_array($activePage, ['packs','abonnements']) ? 'active' : '' ?> has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="star" width="20"></i>
                        <span>Packs &amp; Abonnements</span>
                    </a>
                    <ul class="submenu <?= in_array($activePage, ['packs','abonnements']) ? 'active' : '' ?>">
                        <li><a href="index.php?page=packs">Gérer les packs</a></li>
                        <li><a href="index.php?page=abonnements">Abonnements</a></li>
                        <li><a href="index.php?page=pack_events">Relations Pack-Événement</a></li>
                    </ul>
                </li>

                <li class="sidebar-item <?= in_array($activePage, ['events','inscriptions']) ? 'active' : '' ?> has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="calendar" width="20"></i>
                        <span>Événements</span>
                    </a>
                    <ul class="submenu <?= in_array($activePage, ['events','inscriptions']) ? 'active' : '' ?>">
                        <li><a href="index.php?page=events">Gérer les événements</a></li>
                        <li><a href="index.php?page=inscriptions">Inscriptions</a></li>
                    </ul>
                </li>

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i data-feather="mail" width="20"></i>
                        <span>Mailing</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="/projectttttttt/view/backoffice/modules/viewMails.php">Voir les mails</a></li>
                        <li><a href="index.php?page=mailing">Dashboard mailing</a></li>
                    </ul>
                </li>

                <li class="sidebar-title">Actions</li>

                <li class="sidebar-item">
                    <a href="?logout=1" class="sidebar-link">
                        <i data-feather="log-out" width="20"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>

                <?php elseif ($userRole === 'condidat'): ?>
                <!-- ── Candidat menu ── -->
                <li class="sidebar-title">Mon Espace</li>

                <li class="sidebar-item <?= $activePage === 'offres' ? 'active' : '' ?>">
                    <a href="../frontoffice/offres.php" class="sidebar-link">
                        <i data-feather="briefcase" width="20"></i>
                        <span>Offres</span>
                    </a>
                </li>

                <li class="sidebar-item <?= $activePage === 'candidatures' ? 'active' : '' ?>">
                    <a href="../frontoffice/mes_candidatures.php" class="sidebar-link">
                        <i data-feather="file-text" width="20"></i>
                        <span>Mes Candidatures</span>
                    </a>
                </li>

                <li class="sidebar-item <?= $activePage === 'mes_inscriptions' ? 'active' : '' ?>">
                    <a href="index.php?page=mes_inscriptions" class="sidebar-link">
                        <i data-feather="calendar" width="20"></i>
                        <span>Mes Inscriptions</span>
                    </a>
                </li>

                <li class="sidebar-item <?= $activePage === 'mes_projets' ? 'active' : '' ?>">
                    <a href="index.php?page=mes_projets" class="sidebar-link">
                        <i data-feather="folder" width="20"></i>
                        <span>Mes Projets</span>
                    </a>
                </li>

                <li class="sidebar-item <?= $activePage === 'mon_abonnement' ? 'active' : '' ?>">
                    <a href="index.php?page=mon_abonnement" class="sidebar-link">
                        <i data-feather="star" width="20"></i>
                        <span>Mon Abonnement</span>
                    </a>
                </li>

                <li class="sidebar-title">Actions</li>

                <li class="sidebar-item">
                    <a href="?logout=1" class="sidebar-link">
                        <i data-feather="log-out" width="20"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>

                <?php elseif ($userRole === 'entreprise' || $userRole === 'sponsor'): ?>
                <!-- ── Entreprise / Sponsor menu ── -->
                <li class="sidebar-title">Mon Espace</li>

                <li class="sidebar-item <?= $activePage === 'offres' ? 'active' : '' ?>">
                    <a href="listOffres.php" class="sidebar-link">
                        <i data-feather="briefcase" width="20"></i>
                        <span>Offres</span>
                    </a>
                </li>

                <li class="sidebar-item <?= $activePage === 'mes_projets' ? 'active' : '' ?>">
                    <a href="index.php?page=mes_projets" class="sidebar-link">
                        <i data-feather="folder" width="20"></i>
                        <span>Mes Projets</span>
                    </a>
                </li>

                <li class="sidebar-item <?= $activePage === 'mon_abonnement' ? 'active' : '' ?>">
                    <a href="index.php?page=mon_abonnement" class="sidebar-link">
                        <i data-feather="star" width="20"></i>
                        <span>Mon Abonnement</span>
                    </a>
                </li>

                <li class="sidebar-title">Actions</li>

                <li class="sidebar-item">
                    <a href="?logout=1" class="sidebar-link">
                        <i data-feather="log-out" width="20"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>

                <?php endif; ?>
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>
