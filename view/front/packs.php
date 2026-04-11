<?php
require_once("../../model/Pack.php");
$packModel = new Pack();
$packs = $packModel->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Packs - DigiWork Hub</title>
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

    <div style="text-align: center; padding: 40px 20px 0 20px;">
        <h1>Abonnements Gérés</h1>
        <p>Découvrez les offres spécialement conçues pour vos projets à distance.</p>
    </div>

    <div class="cards-container">
        <?php while ($p = $packs->fetch(PDO::FETCH_ASSOC)) : ?>
        <div class="pack-card">
            <h3><?= htmlspecialchars($p['nom-pack']) ?></h3>
            <h2><?= htmlspecialchars($p['prix']) ?> dt</h2>
            <br>
            <p><strong>Max Projets :</strong> <?= htmlspecialchars($p['nb-proj-max']) ?></p>
            <p><strong>Durée :</strong> <?= htmlspecialchars($p['duree']) ?></p>
            <p><strong>Support Prioritaire :</strong> <?= htmlspecialchars($p['support-prioritaire']) ?></p>
            <br>
            <p><?= htmlspecialchars($p['description']) ?></p>
            <br>
            <form action="../../controller/AbonnementController.php" method="POST">
                <input type="hidden" name="action" value="subscribe">
                <input type="hidden" name="pack_id" value="<?= htmlspecialchars($p['id-pack']) ?>">
                <button type="submit" class="btn-accent" style="width: 100%;">S'abonner</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
