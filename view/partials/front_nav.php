<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Front Office navbar.
 *
 * Expected variables:
 * - $active (string): "home" | "packs" | "abonnement" | "profile"
 */
$active = isset($active) ? (string)$active : '';
$userName = $_SESSION['user_name'] ?? ($_SESSION['user_email'] ?? 'Utilisateur');
?>

<div class="front-navbar">
    <div class="logo-container">
        <img src="../frontoffice/assets/img/logo/logo.png" alt="DigiWork HUB" onerror="this.style.display='none'">
        <span>DigiWork HUB</span>
    </div>

    <div class="nav-links">
        <a href="../frontoffice/index.php" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
        <a href="../front/packs.php" class="<?= $active === 'packs' ? 'active' : '' ?>">Packs</a>
        <a href="../front/abonnement.php" class="<?= $active === 'abonnement' ? 'active' : '' ?>">Abonnement</a>
        <a href="../front/abonnement.php" class="<?= $active === 'profile' ? 'active' : '' ?>">Profile</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="color: rgba(255,255,255,0.92); margin-left: 8px; font-weight: 800;">
                <?= htmlspecialchars($userName) ?>
            </span>
            <a href="../../controller/AuthController.php?action=logout" style="margin-left: 6px;">Déconnexion</a>
        <?php else: ?>
            <a href="../front/login.php" style="margin-left: 6px;">Connexion</a>
        <?php endif; ?>
    </div>
</div>

