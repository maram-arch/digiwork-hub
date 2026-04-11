<?php
require_once("../../model/Pack.php");

if (isset($_GET['id'])) {
    $packModel = new Pack();
    $pack = $packModel->getById($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Pack</title>
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
            <h1>Modifier le Pack</h1>
            
            <div class="admin-form">
                <form id="updatePackForm" action="../../controller/PackController.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="id-pack" name="id-pack" value="<?= htmlspecialchars($pack['id-pack']) ?>">
                    
                    <div class="form-group">
                        <label>Nom :</label>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($pack['nom-pack']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Prix :</label>
                        <input type="number" step="0.01" id="prix" name="prix" value="<?= htmlspecialchars($pack['prix']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Durée :</label>
                        <input type="date" id="duree" name="duree" value="<?= htmlspecialchars($pack['duree']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description :</label>
                        <textarea id="description" name="description" required><?= htmlspecialchars($pack['description']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de projets max :</label>
                        <input type="number" id="nb" name="nb" value="<?= htmlspecialchars($pack['nb-proj-max']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Support Prioritaire :</label>
                        <select id="support" name="support">
                            <option value="oui" <?= $pack['support-prioritaire'] == 'oui' ? 'selected' : '' ?>>Oui</option>
                            <option value="non" <?= $pack['support-prioritaire'] == 'non' ? 'selected' : '' ?>>Non</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-accent" name="update">Mettre à jour</button>
                    <a href="dashboard_packs.php" style="margin-left: 15px;">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
