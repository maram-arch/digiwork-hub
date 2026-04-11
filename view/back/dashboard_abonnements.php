<?php
require_once("../../model/Abonnement.php");
$abo = new Abonnement();
$abonnements = $abo->getAllAbonnements();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - Abonnements</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="back-layout">
        <div class="sidebar">
            <h2>DigiWork Hub BO</h2>
            <a href="dashboard_packs.php">Gestion des Packs</a>
            <a href="dashboard_abonnements.php">Gestion des Abonnements</a>
        </div>
        
        <div class="content">
            <h1>Gestion des Abonnements</h1>
            
            <table class="admin-table">
                <tr>
                    <th>ID Abonnement</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Statut</th>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Pack</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($abonnements as $a) : ?>
                    <tr>
                        <td><?= htmlspecialchars($a['id-abonnement']) ?></td>
                        <td><?= htmlspecialchars($a['date-deb']) ?></td>
                        <td><?= htmlspecialchars($a['date-fin']) ?></td>
                        <td><?= htmlspecialchars($a['status']) ?></td>
                        <td><?= htmlspecialchars($a['nom']) ?></td>
                        <td><?= htmlspecialchars($a['tel']) ?></td>
                        <td><?= htmlspecialchars($a['nom-pack']) ?></td>
                        <td>
                            <a href="../../controller/AbonnementController.php?action=delete&id=<?= $a['id-abonnement'] ?>" class="btn-delete" onclick="return confirm('Sûr de supprimer ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
