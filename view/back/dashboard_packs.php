<?php
require_once("../../model/Pack.php");
$packModel = new Pack();
$packs = $packModel->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Back Office - Dashboard Packs</title>
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
            <h1>Gestion des Packs</h1>
            
            <div class="admin-form">
                <h3>Ajouter un Pack</h3>
                <form id="addPackForm" action="../../controller/PackController.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Nom :</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label>Prix :</label>
                        <input type="number" step="0.01" id="prix" name="prix" required>
                    </div>
                    <div class="form-group">
                        <label>Durée :</label>
                        <input type="date" id="duree" name="duree" required>
                    </div>
                    <div class="form-group">
                        <label>Description :</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nombre de projets max :</label>
                        <input type="number" id="nb" name="nb" required>
                    </div>
                    <div class="form-group">
                        <label>Support Prioritaire :</label>
                        <select id="support" name="support">
                            <option value="oui">Oui</option>
                            <option value="non">Non</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-accent" name="add">Ajouter</button>
                </form>
            </div>

            <h3>Liste des Packs</h3>
            <table class="admin-table">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Durée</th>
                    <th>Nb Projets</th>
                    <th>Support</th>
                    <th>Actions</th>
                </tr>
                <?php while ($pack = $packs->fetch(PDO::FETCH_ASSOC)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($pack['id-pack']) ?></td>
                        <td><?= htmlspecialchars($pack['nom-pack']) ?></td>
                        <td><?= htmlspecialchars($pack['prix']) ?> dt</td>
                        <td><?= htmlspecialchars($pack['duree']) ?></td>
                        <td><?= htmlspecialchars($pack['nb-proj-max']) ?></td>
                        <td><?= htmlspecialchars($pack['support-prioritaire']) ?></td>
                        <td>
                            <a href="update_pack.php?id=<?= $pack['id-pack'] ?>" class="btn-edit">Modifier</a>
                            <a href="../../controller/PackController.php?action=delete&id=<?= $pack['id-pack'] ?>" class="btn-delete" onclick="return confirm('Sûr de supprimer ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
