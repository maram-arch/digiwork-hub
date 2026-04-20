<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiWork HUB | Front Office</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php
    $active = 'home';
    require_once(__DIR__ . '/../partials/front_nav.php');
    ?>

    <div class="hero">
        <div class="container">
            <h1>Votre espace DigiWork HUB</h1>
            <p>
                Une expérience Front Office claire et moderne: découvrez nos packs, gérez votre abonnement
                et suivez votre profil, avec une navigation cohérente sur toutes les pages.
            </p>
            <div class="hero-buttons">
                <a href="../front/packs.php" class="btn-accent">Voir les Packs</a>
                <a href="../front/abonnement.php" class="btn-white">Mon Abonnement</a>
            </div>
        </div>
    </div>

    <h2 class="section-title">Raccourcis</h2>
    <div class="cards-container" style="padding-top: 10px;">
        <div class="pack-card" style="max-width: 360px;">
            <div class="pack-image">Packs</div>
            <div class="pack-content">
                <div>
                    <span class="pack-tag">Offres</span>
                    <h3 class="pack-title">Choisir un pack</h3>
                    <p style="margin:0;color:var(--text-muted);font-size:13px;">
                        Comparez les plans, les limites de projets et le support.
                    </p>
                </div>
                <div class="pack-footer" style="padding: 0; border: none; background: transparent; justify-content:flex-end;">
                    <a href="../front/packs.php" class="pack-cta" style="display:inline-block;">Aller →</a>
                </div>
            </div>
        </div>

        <div class="pack-card" style="max-width: 360px;">
            <div class="pack-image">Abonnement</div>
            <div class="pack-content">
                <div>
                    <span class="pack-tag">Compte</span>
                    <h3 class="pack-title">Gérer mon abonnement</h3>
                    <p style="margin:0;color:var(--text-muted);font-size:13px;">
                        Consultez vos dates, statut et détails en un coup d’œil.
                    </p>
                </div>
                <div class="pack-footer" style="padding: 0; border: none; background: transparent; justify-content:flex-end;">
                    <a href="../front/abonnement.php" class="pack-cta" style="display:inline-block;">Ouvrir →</a>
                </div>
            </div>
        </div>

        <div class="pack-card" style="max-width: 360px;">
            <div class="pack-image">Administration</div>
            <div class="pack-content">
                <div>
                    <span class="pack-tag">Back Office</span>
                    <h3 class="pack-title">Espace administrateur</h3>
                    <p style="margin:0;color:var(--text-muted);font-size:13px;">
                        Gestion des packs, abonnements et supervision.
                    </p>
                </div>
                <div class="pack-footer" style="padding: 0; border: none; background: transparent; justify-content:flex-end;">
                    <a href="../back/dashboard_packs.php" class="pack-cta" style="display:inline-block;">Dashboard →</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
