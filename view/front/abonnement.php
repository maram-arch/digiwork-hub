<?php
require_once("../../model/Abonnement.php");
// Mocking logged in user for demonstration purposes, since no Auth module is strictly defined.
$abo = new Abonnement();
$abonnements = $abo->getAllAbonnements(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace - DigiWork Hub</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="front-navbar">
        <h2>DigiWork Hub</h2>
        <div>
            <a href="packs.php">Nos Packs</a>
            <a href="abonnement.php">Mon Tableau de Bord</a>
        </div>
    </div>

    <div style="padding: 40px; max-width: 800px; margin: 0 auto;">
        <h2>Mes Abonnements Réccurents</h2>
        <?php if (isset($_GET['success'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                Félicitations ! Vous êtes maintenant abonné au pack.
            </div>
        <?php endif; ?>

        <?php if(count($abonnements) == 0): ?>
            <p>Vous n'avez aucun abonnement actif.</p>
        <?php else: ?>
            <?php foreach ($abonnements as $a) : ?>
                <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                    <h3>Pack : <?= htmlspecialchars($a['nom-pack']) ?></h3>
                    <p><strong>Date de début :</strong> <?= htmlspecialchars($a['date-deb']) ?></p>
                    <p><strong>Date de fin :</strong> <?= htmlspecialchars($a['date-fin']) ?></p>
                    <p>
                        <strong style="color: var(--accent-color);">Status : <?= htmlspecialchars($a['status']) ?></strong>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
